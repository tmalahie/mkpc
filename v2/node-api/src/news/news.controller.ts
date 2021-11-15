import { Controller, Get, Req } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { I18n, I18nContext } from 'nestjs-i18n';
import { EntityManager } from 'typeorm';
import { News, NewsStatus } from './news.entity';

@Controller("/news")
export class NewsController {
  constructor(private em: EntityManager) {}

  @Auth({loadRoles: true})
  @Get("")
  async getNews(@I18n() i18n: I18nContext) {
    const listNews = await this.em.find(News, {
      where: {
        status: NewsStatus.ACCEPTED
      },
      order: {
        publicationDate: "DESC"
      },
      relations: ["category", "author"],
      take: 8
    });
    const data = listNews.map((news) => ({
      id: news.id,
      title: news.title,
      category: {
        id: news.category.id,
        name: news.category.getName(i18n.detectedLanguage),
      },
      creationDate: news.creationDate,
      publicationDate: news.publicationDate,
      nbComments: news.nbComments,
      author: news.author && {
        id: news.author.id,
        name: news.author.name
      }
    }))
    return {
      data
    }
  }
}
