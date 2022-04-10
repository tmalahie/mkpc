import { Body, Controller, Get, HttpCode, Param, Post } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { EntityManager } from 'typeorm';
import { User } from './user.entity';
import { Ban } from './ban.entity';
import { AuthUser, GetUser } from './user.decorator';
import { EQUALITY_SEARCH, SearchService, SearchType } from 'src/search/search.service';
import { pick } from 'lodash';

@Controller("/user")
export class UserController {
  constructor(private em: EntityManager, private searchService: SearchService) { }

  @Auth({ loadRoles: true })
  @Get("/me")
  async getMe(@GetUser() authUser: AuthUser) {
    if (!authUser.id)
      return null;
    const user = await this.em.findOne(User, authUser.id);
    return {
      id: user.id,
      name: user.name,
      roles: authUser.roles,
      banned: user.banned || undefined
    }
  }

  @Auth({ loadRoles: true })
  @Get("/me/banData")
  async getBanMessage(@GetUser() authUser: AuthUser) {
    if (!authUser.id)
      return null;
    const ban = await this.em.findOne(Ban, authUser.id);
    if (!ban)
      return null;
    return {
      message: ban.message,
      endDate: ban.endDate
    }
  }

  @Get("/:id")
  async getUser(@Param("id") id: number) {
    const user = await this.em.findOne(User, id);
    return {
      id: user.id,
      name: user.name
    }
  }

  @Post("/find")
  async findUsers(@Body() params) {
    const { data: users, count } = await this.searchService.find({
      entity: User,
      params,
      rules: {
        allowedFilters: {
          id: EQUALITY_SEARCH,
          name: [SearchType.PREFIX, SearchType.EQUALS],
          banned: [SearchType.EQUALS],
          deleted: [SearchType.EQUALS]
        },
        allowedOrders: ["id", "name", "pts_vs", "pts_battle", "pts_challenge"],
        canReturnCount: true,
        maxResults: 20
      },
      relations: ["profile", "profile.country"]
    });
    const data = users.map((user) => ({
      id: user.id,
      name: user.name,
      pts_vs: user.pts_vs,
      pts_battle: user.pts_battle,
      pts_challenge: user.pts_challenge,
      country: user.profile.country && pick(user.profile.country, ["id", "code"])
    }))
    return {
      data,
      count
    }
  }
}
