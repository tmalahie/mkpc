import { Body, Controller, Get, NotFoundException, Param, Post } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { I18n, I18nContext } from 'nestjs-i18n';
import { EntityManager } from 'typeorm';
import { News, NewsStatus } from './news.entity';
import { NewsRead } from './newsRead.entity';
import { EQUALITY_SEARCH, SearchService, SearchType } from 'src/search/search.service';
import { AuthUser, GetUser } from 'src/user/user.decorator';

@Controller("/news")
export class NewsController {
  constructor(private em: EntityManager, private searchService: SearchService) { }

  accessRestricted(news: News, user: AuthUser) {
    if (news.status === NewsStatus.ACCEPTED)
      return false;
    if (news.author?.id === user.id)
      return false;
    if (user.roles.publisher)
      return false;
    return true;
  }

  @Auth()
  @Get("/lastRead")
  async getLastRead(@GetUser() user: AuthUser) {
    const newsRead = await this.em.findOne(NewsRead, user.id);
    if (!newsRead)
      return null;
    return {
      date: newsRead.date
    }
  }

  @Auth({ loadRoles: true })
  @Get("/:id")
  async getNews(@GetUser() user: AuthUser, @I18n() i18n: I18nContext, @Param("id") id: number) {
    const news = await this.em.findOne(News, id, {
      relations: ["category", "author"]
    });
    if (!news || this.accessRestricted(news, user))
      throw new NotFoundException(`News with id ${id} does not exist`);
    return {
      id: news.id,
      title: news.title,
      category: {
        id: news.category.id,
        name: news.category.getName(i18n.detectedLanguage),
      },
      status: news.status,
      creationDate: news.creationDate,
      publicationDate: news.publicationDate,
      nbComments: news.nbComments,
      author: news.author && {
        id: news.author.id,
        name: news.author.name
      },
      content: news.content
    }
  }

  @Auth({ loadRoles: true })
  @Post("/find")
  async findNews(@GetUser() user: AuthUser, @I18n() i18n: I18nContext, @Body() params) {
    const { data: listNews, count } = await this.searchService.find({
      entity: News,
      params,
      rules: {
        allowedFilters: {
          id: EQUALITY_SEARCH,
          category: EQUALITY_SEARCH,
          author: EQUALITY_SEARCH,
          status: EQUALITY_SEARCH,
          title: [SearchType.LIKE]
        },
        allowedOrders: ["id", "creationDate", "publicationDate"],
        canReturnCount: true,
        maxResults: 50
      },
      where: params.me ? {
        author: user.id
      } : (user.roles.publisher ? null : {
        status: NewsStatus.ACCEPTED
      }),
      relations: ["category", "author"],
    });
    const data = listNews.map((news) => ({
      id: news.id,
      title: news.title,
      category: {
        id: news.category.id,
        name: news.category.getName(i18n.detectedLanguage),
      },
      status: news.status,
      creationDate: news.creationDate,
      publicationDate: news.publicationDate,
      nbComments: news.nbComments,
      author: news.author && {
        id: news.author.id,
        name: news.author.name
      }
    }))
    return {
      data,
      count
    }
  }
}
