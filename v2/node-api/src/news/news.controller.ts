import { Body, Controller, Post } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { I18n, I18nContext } from 'nestjs-i18n';
import { EntityManager } from 'typeorm';
import { News, NewsStatus } from './news.entity';
import { EQUALITY_SEARCH, SearchService, SearchType } from 'src/search/search.service';
import { AuthUser, GetUser } from 'src/user/user.decorator';

@Controller("/news")
export class NewsController {
  constructor(private em: EntityManager, private searchService: SearchService) { }

  @Auth({ loadRoles: true })
  @Post("/find")
  async getNews(@GetUser() user: AuthUser, @I18n() i18n: I18nContext, @Body() params) {
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
