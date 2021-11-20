import { Body, Controller, Get, Post, Query } from '@nestjs/common';
import { EntityManager, FindOneOptions, In, LessThan, Raw } from 'typeorm';
import { Record } from './record.entity';
import { Ranking } from './ranking.entity';
import { CircuitService } from '../track-builder/circuit.service';

@Controller("/time-trial")
export class TimeTrialController {
  constructor(private em: EntityManager, private circuitService: CircuitService) {}

  @Post("/records/find")
  async getRecords(@Body() params) {
    const where: FindOneOptions<Record>["where"] = {
      best: 1
    };
    if (params) {
      if (params.filters) {
        if (params.filters.id !== undefined)
          where.id = params.filters.id;
        if (params.filters.circuit !== undefined)
          where.circuit = params.filters.circuit;
        if (params.filters.player !== undefined)
          where.player = params.filters.player;
        if (params.filters.class !== undefined)
          where.class = params.filters.class;
        if (params.filters.type !== undefined) {
          if (params.filters.type?.type === "in") {
            where.type = In(params.filters.type.value);
          }
          else
            where.type = params.filters.type;
        }
      }
    }
    const records = await this.em.find(Record, {
      where,
      order: {
        id: "DESC"
      },
      relations: ["player"],
      take: 30
    });
    const data = await Promise.all(records.map(async (record) => {
      const circuit = record.type ? await this.circuitService.getCircuit(record.type, record.circuit) : undefined;
      const [circuitRecords,circuitRanking] = await Promise.all([this.em.count(Record, {
        where: {
          type: record.type,
          circuit: record.circuit,
          class: record.class,
          best: 1
        }
      }), this.em.count(Record, {
        where: {
          type: record.type,
          circuit: record.circuit,
          class: record.class,
          best: 1,
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
          rank: circuitRanking+1,
          count: circuitRecords
        }
      }
    }));
    return {
      data
    }
  }

  @Get("/leaderboard")
  async getLeaderboard(@Query() params) {
    const ccFilter = +params.cc || 150;
    const leaderboard = await this.em.find(Ranking, {
      where: {
        class: ccFilter,
        player: {
          deleted: false
        }
      },
      order: {
        score: "DESC"
      },
      relations: ["player"],
      take: 20
    });
    const data = leaderboard.map((ranking) => ({
      id: ranking.player.id,
      name: ranking.player.name,
      score: ranking.score,
    }));
    return { data };
  }
}
