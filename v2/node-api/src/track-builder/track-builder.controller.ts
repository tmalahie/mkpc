import { Controller, Get, Req } from '@nestjs/common';
import { EntityManager, Not } from 'typeorm';
import { Comment } from './comment.entity';
import { CircuitService } from './circuit.service';

@Controller("/track-builder")
export class TrackBuilderController {
  constructor(private em: EntityManager, private circuitService: CircuitService) {}

  @Get("/comments")
  async getComments() {
    const comments = await this.em.find(Comment, {
      order: {
        id: "DESC"
      },
      relations: ["author"],
      take: 30
    });
    const data = await Promise.all(comments.map(async (comment) => {
      const circuit = await this.circuitService.getCircuit(comment.type, comment.circuit);
      return {
        id: comment.id,
        message: comment.message,
        date: comment.date,
        circuit: circuit && {
          id: circuit.id,
          name: circuit.name,
          url: this.circuitService.getUrl(circuit)
        },
        author: comment.author && {
          id: comment.author.id,
          name: comment.author.name,
        }
      }
    }));
    return {
      data
    }
  }
}
