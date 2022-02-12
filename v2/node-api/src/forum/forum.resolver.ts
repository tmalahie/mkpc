import { Args, Parent, Query, ResolveField, Resolver,  } from "@nestjs/graphql";
import { Topic } from "./models/topic.model";
import { EntityManager } from 'typeorm';
import { SearchType, EQUALITY_SEARCH, SearchService } from '../search/search.service';
import { Message } from './message.entity';
import { Topic as TopicEntity } from './topic.entity';
import { NotFoundException } from "@nestjs/common";

@Resolver(_of => Topic)
export class ForumResolver {
  constructor(
    private em: EntityManager, private searchService: SearchService
  ) {}

  @Query(_returns => Topic)
  async topic(@Args('id') id: number) {
    const topic = await this.em.findOne(TopicEntity, id, {
      relations: ["category"]
    });
    if (!topic)
      throw new NotFoundException();
    return {
      id: topic.id,
      title: topic.title,
      nbMessages: topic.nbMessages,
      language: topic.getLanguage(),
      category: {
        id: topic.category.id,
        name: topic.category.getName(null)
      }
    };
  }

  @Query(_returns => [Topic])
  async topics() {
    const params = {
      sort: {
        key: "lastMessageDate",
        order: "desc" as const
      },
      paging: {
        limit: 10
      }
    };
    const { data: topics } = await this.searchService.find({
      entity: TopicEntity,
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
      where: {
        private: 0
      },
      relations: ["category"]
    });
    const data = topics.map((topic, i) => ({
      id: topic.id,
      title: topic.title,
      nbMessages: topic.nbMessages,
      language: topic.getLanguage(),
      category: {
        id: topic.category.id,
        name: topic.category.getName(null)
      }
    }))
    return data
  }

  @ResolveField('firstMessage', _returns => Message)
  async firstMessage(@Parent() topic: Topic) {
    const firstMessage = await this.em.findOne(Message, {
      where: {
        topic: topic.id,
        id: 1
      },
      relations: ["author"]
    });
    return firstMessage;
  }

  @ResolveField('lastMessage', _returns => Message)
  async lastMessage(@Parent() topic: Topic) {
    const lastMessage = await this.em.findOne(Message, {
      where: {
        topic: topic.id
      },
      relations: ["author"],
      order: {
        id: "DESC"
      }
    });
    return lastMessage;
  }
}