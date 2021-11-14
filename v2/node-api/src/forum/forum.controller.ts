import { Controller, Get, Req } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { I18n, I18nContext } from 'nestjs-i18n';
import { AuthUser, GetUser } from 'src/user/user.decorator';
import { EntityManager } from 'typeorm';
import { Message } from './message.entity';
import { Topic } from './topic.entity';

@Controller("/forum")
export class ForumController {
  constructor(private em: EntityManager) {}

  @Auth({loadRoles: true})
  @Get("/topics")
  async getTopics(@GetUser() user: AuthUser, @I18n() i18n: I18nContext) {
    console.log(user);
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
}
