import { Controller, Body, Post } from '@nestjs/common';
import { pick } from 'lodash';
import { SearchService, SearchType } from 'src/search/search.service';
import { EntityManager, FindOneOptions, Not, Raw } from 'typeorm';
import { EntityFieldsNames } from 'typeorm/common/EntityFieldsNames';
import { User } from '../user/user.entity';

@Controller("/online-game")
export class OnlineGameController {
  constructor(private em: EntityManager, private searchService: SearchService) { }

  @Post("/leaderboard")
  async getLeaderboard(@Body() params) {
    const where: FindOneOptions<User>["where"] = {
      deleted: false
    };
    let score: EntityFieldsNames<User> = "pts_vs";
    let defaultScore = 5000;
    if (params) {
      switch (params.mode) {
        case "battle":
          score = "pts_battle";
          break;
        case "challenge":
          score = "pts_challenge";
          defaultScore = 0;
      }
      where[score] = Not(defaultScore);
      if (params.name) {
        params.filters = [
          ...(params.filters || []), {
            "key": "name",
            "operator": "=",
            "value": params.name
          }
        ];
      }
      else {
        params.sort = params.sort || {
          "key": score,
          "order": "desc"
        }
      }
    }
    let skip = +params.paging?.offset || 0;
    const { data: leaderboard, count } = await this.searchService.find({
      entity: User,
      params,
      where,
      rules: {
        allowedFilters: {
          name: [SearchType.EQUALS],
        },
        allowedOrders: ["id", score],
        defaultOrder: {
          id: "ASC"
        },
        canReturnCount: true,
        maxResults: 20
      },
      relations: ["profile", "profile.country"]
    });
    if (params.name && leaderboard.length) {
      const usersBefore = await this.em.count(User, {
        where: {
          ...where,
          [score]: Raw((alias) => `(${alias} > :pts OR (${alias} = :pts AND id<:id)) AND (${alias} != :def)`, { id: leaderboard[0].id, pts: leaderboard[0][score], def: defaultScore })
        }
      });
      skip = usersBefore;
    }
    const data = leaderboard.map((user, i) => ({
      id: user.id,
      name: user.name,
      score: user[score],
      rank: i + skip + 1,
      country: user.profile.country && pick(user.profile.country, ["id", "code"])
    }))
    return {
      data,
      count
    }
  }
}
