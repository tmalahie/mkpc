import { Controller, Get, Query } from '@nestjs/common';
import { EntityManager, FindOneOptions } from 'typeorm';
import { User } from '../user/user.entity';

@Controller("/online-game")
export class OnlineGameController {
  constructor(private em: EntityManager) {}

  @Get("/leaderboard")
  async getLeaderboard(@Query() params) {
    const where: FindOneOptions<User>["where"] = {
      deleted: false
    };
    let score = "pts_vs";
    if (params) {
      if (params.name)
        where.name = params.name;
      switch (params.mode) {
      case "battle":
        score = "pts_battle";
        break;
      case "challenge":
        score = "pts_challenge";
        break;
      }
    }
    const take = 20;
    const skip = params.page ? (params.page-1)*take : 0;
    const leaderboard = await this.em.find(User, {
      where,
      order: {
        [score]: "DESC"
      },
      take: 20,
      skip
    });
    const data = leaderboard.map((user) => ({
      id: user.id,
      name: user.name,
      score: user[score]
    }))
    return {
      data
    }
  }
}
