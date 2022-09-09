import { User } from 'src/user/user.entity';
import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn } from 'typeorm';
import { CircuitType } from "../track-builder/circuit.service";

@Entity({name: "mkrecords"})
export class Record {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  date: Date;

  @Column()
  name: string;

  @Column()
  identifiant: number;

  @ManyToOne(_type => User)
  @JoinColumn({name: "player"})
  player: User;

  @Column({name: "perso"})
  character: string;

  @Column()
  class: number;

  @Column()
  type: CircuitType | "";

  @Column()
  circuit: number;

  @Column()
  time: number;

  @Column()
  best: boolean;
}