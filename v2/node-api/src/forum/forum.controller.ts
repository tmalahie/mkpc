import { Controller, Get, Req } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { I18n, I18nContext } from 'nestjs-i18n';
import { AuthUser, GetUser } from 'src/user/user.decorator';
import { EntityManager } from 'typeorm';
import { Message } from './message.entity';
import { Topic } from './topic.entity';
import { Category } from './category.entity';

@Controller("/forum")
export class ForumController {
  constructor(private em: EntityManager) {}

  @Auth({loadRoles: true})
  @Get("/topics")
  async getTopics(@GetUser() user: AuthUser, @I18n() i18n: I18nContext) {
    let topics = await this.em.find(Topic, {
      where: user.roles.moderator ? {} : {
        private: 0
      },
      order: {
        lastMessageDate: "DESC"
      },
      relations: ["category"],
      take: 10
    });
    if (i18n.detectedLanguage !== "fr") {
      topics = [
        ...topics.filter(t => t.getLanguage() !== "fr"),
        ...topics.filter(t => t.getLanguage() === "fr")
      ];
    }
    const lastMessages = await Promise.all(topics.map(topic => this.em.findOne(Message, {
        where: {
            topic: topic.id
        },
        relations: ["author"],
        order: {
          id: "DESC"
        }
      })));
    const data = topics.map((topic,i) => ({
      id: topic.id,
      title: topic.title,
      nbMessages: topic.nbMessages,
      category: {
        id: topic.category.id,
        name: topic.category.getName(i18n.detectedLanguage)
      },
      lastMessage: {
        author: lastMessages[i].author,
        date: lastMessages[i].date
      }
    }))
    return {
      data
    }
  }

  @Get("/categories")
  async getCategories(@I18n() i18n: I18nContext) {
    const lang = i18n.detectedLanguage;
    const categories = await this.em.find(Category);
    const sortedCategories = categories.sort((c1,c2) => c1.getOrder(lang) - c2.getOrder(lang))
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
    const data = categoriesWithData.map(({category,nbTopics,lastTopic}) => ({
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
}
