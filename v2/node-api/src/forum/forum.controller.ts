import { Controller, Get } from '@nestjs/common';
import { User } from 'src/user/user.entity';
import { EntityManager } from 'typeorm';
import { Message } from './message.entity';
import { Topic } from './topic.entity';

@Controller("/forum")
export class ForumController {
  constructor(private em: EntityManager) {}

  @Get("/topics")
  async getTopics() {
    const topics = await this.em.find(Topic, {
      order: {
        lastMessageDate: "DESC"
      },
      relations: ["category"],
      take: 10
    });
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
      category: topic.category,
      lastMessage: {
        author: lastMessages[i].author,
        date: lastMessages[i].date
      }
    }))
    return {
      data
    }
  }
}
