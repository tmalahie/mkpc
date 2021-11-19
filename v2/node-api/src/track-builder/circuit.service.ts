import { Injectable } from '@nestjs/common';
import { EntityManager } from 'typeorm';

export enum CircuitType {
  COMPLETE_CIRCUIT = "circuits",
  SIMPLE_CIRCUIT = "mkcircuits",
  COMPLETE_ARENA = "arenes",
  CUP = "mkcups",
  MULTICUP = "mkmcups"
}

type Circuit = {
  id: number;
  type: CircuitType;
  mode?: number;
  name?: string;
  author?: string;
}

@Injectable()
export class CircuitService {
  constructor(private em: EntityManager) {}

  getUrl({
    id, type, mode
  }: Circuit): string {
    switch (type) {
      case CircuitType.MULTICUP :
        return (mode ? 'map.php':'circuit.php') + '?mid='+ id;
      case CircuitType.CUP :
        return (mode ? 'map.php':'circuit.php') + '?cid='+ id;
      case CircuitType.SIMPLE_CIRCUIT :
        return (mode ? 'circuit.php':'arena.php') + '?id='+ id;
      case CircuitType.COMPLETE_ARENA :
        return 'battle.php?i='+ id;
      case CircuitType.COMPLETE_CIRCUIT :
        return 'map.php?i='+ id;
      }
  }

  async getCircuit(type: CircuitType, id: number): Promise<Circuit> {
    const [circuit] = await this.em.query(`SELECT id,
      nom AS name,
      auteur AS author
      ${(type===CircuitType.SIMPLE_CIRCUIT) ? ',!type as mode':''}
      ${(type===CircuitType.CUP) ? ',mode':''}
      ${(type===CircuitType.MULTICUP) ? ',mode':''}
      FROM \`${type}\`
      WHERE id=${id}`
    );
    return {
      ...circuit,
      type
    };
  }
}
