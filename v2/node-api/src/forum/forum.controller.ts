import { Body, Controller, Get, NotFoundException, Param, Post } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { I18n, I18nContext } from 'nestjs-i18n';
import { AuthUser, GetUser } from 'src/user/user.decorator';
import { EntityManager, In, MoreThan } from 'typeorm';
import { Message } from './message.entity';
import { Topic } from './topic.entity';
import { Category } from './category.entity';
import { Profile } from 'src/user/profile.entity';
import { DateTime } from 'luxon';
import { keyBy, pick } from 'lodash'
import { SearchType, EQUALITY_SEARCH, SearchService } from 'src/search/search.service';
@Controller("/forum")
export class ForumController {
  constructor(private em: EntityManager, private searchService: SearchService) { }

  @Auth({ loadRoles: true })
  @Post("/topics/find")
  async getTopics(@GetUser() user: AuthUser, @I18n() i18n: I18nContext, @Body() params) {
    const { data: topics, count } = await this.searchService.find({
      entity: Topic,
      params,
      rules: {
        allowedFilters: {
          id: EQUALITY_SEARCH,
          category: EQUALITY_SEARCH,
          title: [SearchType.LIKE]
        },
        allowedOrders: ["id", "lastMessageDate"],
        canReturnCount: true,
        maxResults: 50
      },
      where: user.roles.moderator ? null : {
        private: 0
      },
      relations: ["category"]
    });
    const firstMessages = await this.em.find(Message, {
      where: {
        id: 1,
        topic: In(topics.map(topic => topic.id))
      },
      relations: ["author"]
    });
    const firstMessagesByTopic = keyBy(firstMessages, "topic");
    const lastMessages = await Promise.all(topics.map(topic => this.em.findOne(Message, {
      where: {
        topic: topic.id,
      },
      relations: ["author"],
      order: {
        id: "DESC"
      }
    })));
    const data = topics.map((topic, i) => ({
      id: topic.id,
      title: topic.title,
      nbMessages: topic.nbMessages,
      language: topic.getLanguage(),
      category: {
        id: topic.category.id,
        name: topic.category.getName(i18n.detectedLanguage)
      },
      firstMessage: {
        author: firstMessagesByTopic[topic.id]?.author && pick(firstMessagesByTopic[topic.id].author, "id", "name"),
        date: firstMessagesByTopic[topic.id]?.date
      },
      lastMessage: {
        author: lastMessages[i]?.author && pick(lastMessages[i].author, "id", "name"),
        date: lastMessages[i]?.date
      }
    }))
    return {
      data,
      count
    }
  }

  @Get("/categories")
  async getCategories(@I18n() i18n: I18nContext) {
    const lang = i18n.detectedLanguage;
    const categories = await this.em.find(Category);
    const sortedCategories = categories.sort((c1, c2) => c1.getOrder(lang) - c2.getOrder(lang))
    const categoriesWithData = await Promise.all(sortedCategories.map(async category => {
      const nbTopics = await this.em.count(Topic, {
        where: {
          category: category.id
        }
      });
      const lastTopic = await this.em.findOne(Topic, {
        where: {
          category: category.id
        },
        order: {
          lastMessageDate: "DESC"
        }
      });
      return {
        category,
        nbTopics,
        lastTopic
      };
    }))
    const data = categoriesWithData.map(({ category, nbTopics, lastTopic }) => ({
      id: category.id,
      name: category.getName(lang),
      description: category.getDescription(lang),
      nbTopics,
      lastTopic: {
        id: lastTopic.id,
        title: lastTopic.title,
        nbMessages: lastTopic.nbMessages,
        lastMessage: {
          date: lastTopic.lastMessageDate
        }
      }
    }))
    return {
      data
    }
  }

  @Get("/categories/:id")
  async getCategory(@I18n() i18n: I18nContext, @Param("id") id: number) {
    const lang = i18n.detectedLanguage;
    const category = await this.em.findOne(Category, id);
    if (!category)
      throw new NotFoundException(`Category with id ${id} does not exist`);
    return {
      id: category.id,
      name: category.getName(lang),
      description: category.getDescription(lang),
      adminOnly: category.adminOnly
    };
  }

  @Get("/stats")
  async getStats() {
    const messagesStats = await this.em.getRepository(Topic).createQueryBuilder("t")
      .select(`SUM(t.nbmsgs)`, "nb")
      .getRawOne();
    const nbTopics = await this.em.count(Topic);
    const nbMembers = await this.em.count(Profile, {
      where: {
        nbMessages: MoreThan(0)
      }
    });
    const mostActivePlayer = await this.em.findOne(Profile, {
      order: {
        nbMessages: "DESC"
      },
      relations: ["user"]
    });
    const beginMonth = DateTime.now().startOf("month").toFormat("yyyy-MM-dd");
    const [monthActivePlayer] = await this.em.query(
      `SELECT j.id,j.nom AS name,m.nbMessages FROM
      (SELECT auteur,COUNT(*) AS nbMessages FROM mkmessages WHERE date>="${beginMonth}" GROUP BY auteur) m
      INNER JOIN mkjoueurs j ON m.auteur=j.id
      ORDER BY nbMessages DESC, j.id ASC LIMIT 1`
    );
    return {
      nbTopics,
      nbMembers,
      nbMessages: messagesStats.nb,
      mostActivePlayer: mostActivePlayer && {
        id: mostActivePlayer.id,
        name: mostActivePlayer.user.name,
        nbMessages: mostActivePlayer.nbMessages
      },
      monthActivePlayer: monthActivePlayer && {
        ...monthActivePlayer,
        beginMonth
      }
    }
  }
}
