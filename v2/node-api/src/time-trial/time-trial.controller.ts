import { Body, Controller, Get, Post, Query } from '@nestjs/common';
import { EntityManager, FindOneOptions, In, LessThan, Raw } from 'typeorm';
import { Record } from './record.entity';
import { Ranking } from './ranking.entity';
import { CircuitService } from '../track-builder/circuit.service';
import { SearchService, EQUALITY_SEARCH, SearchType } from '../search/search.service';
import { pick } from 'lodash';
import { User } from 'src/user/user.entity';

@Controller("/time-trial")
export class TimeTrialController {
  constructor(private em: EntityManager, private circuitService: CircuitService, private searchService: SearchService) { }

  @Post("/records/find")
  async getRecords(@Body() params) {
    const recordsPayload = await this.searchService.find({
      entity: Record,
      params,
      rules: {
        allowedFilters: {
          id: EQUALITY_SEARCH,
          circuit: EQUALITY_SEARCH,
          player: EQUALITY_SEARCH,
          class: EQUALITY_SEARCH,
          type: EQUALITY_SEARCH
        },
        allowedOrders: ["id", "date"],
        maxResults: 30
      },
      where: {
        best: true
      },
      relations: ["player"]
    });
    const data = await Promise.all(recordsPayload.data.map(async (record) => {
      const circuit = record.type ? await this.circuitService.getCircuit(record.type, record.circuit) : undefined;
      const [circuitRecords, circuitRanking] = await Promise.all([this.em.count(Record, {
        where: {
          type: record.type,
          circuit: record.circuit,
          class: record.class,
          best: true
        }
      }), this.em.count(Record, {
        where: {
          type: record.type,
          circuit: record.circuit,
          class: record.class,
          best: true,
          time: LessThan(record.time)
        }
      })]);
      return {
        id: record.id,
        name: record.name,
        character: record.character,
        date: record.date,
        class: record.class,
        time: record.time,
        type: record.type,
        circuit: circuit && {
          id: circuit.id,
          name: circuit.name,
          url: this.circuitService.getUrl(circuit)
        },
        player: record.player && {
          id: record.player.id,
          name: record.player.name,
        },
        leaderboard: {
          rank: circuitRanking + 1,
          count: circuitRecords
        }
      }
    }));
    return {
      data
    }
  }

  @Post("/leaderboard")
  async getLeaderboard(@Body() params) {
    const ccFilter = +params.cc || 150;
    const baseWhere: FindOneOptions<Ranking>["where"] = {
      class: ccFilter,
      player: {
        deleted: false
      }
    };
    let where = baseWhere;
    if (params) {
      if (params.name) {
        const user = await this.em.findOne(User, {
          where: {
            name: params.name
          }
        });
        where = {
          ...where,
          player: undefined
        }
        params.filters = [
          ...(params.filters || []), {
            "key": "player",
            "operator": "=",
            "value": user?.id ?? 0
          }
        ];
      }
      else {
        params.sort = params.sort || {
          "key": "score",
          "order": "desc"
        }
      }
    }
    let skip = +params.paging?.offset || 0;
    const { data: leaderboard, count } = await this.searchService.find({
      entity: Ranking,
      params,
      where,
      rules: {
        allowedFilters: {
          player: [SearchType.EQUALS],
        },
        allowedOrders: ["player", "score"],
        defaultOrder: {
          player: "ASC"
        },
        canReturnCount: true,
        maxResults: 20
      },
      relations: ["player", "player.profile", "player.profile.country"],
    });
    if (params.name && leaderboard.length) {
      const usersBefore = await this.em.count(Ranking, {
        where: {
          score: Raw((alias) => `(${alias} > :pts OR (${alias} = :pts AND player<:id))`, { id: leaderboard[0].id, pts: leaderboard[0].score }),
          ...baseWhere
        },
        relations: ["player"]
      });
      skip = usersBefore;
    }
    const data = leaderboard.map((ranking, i) => ({
      id: ranking.player.id,
      name: ranking.player.name,
      score: ranking.score,
      rank: i + skip + 1,
      country: ranking.player.profile.country && pick(ranking.player.profile.country, ["id", "code"])
    }));
    return { data, count };
  }
}
