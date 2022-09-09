import { User } from '../user/user.entity';
import { Entity, Column, ManyToOne, JoinColumn, PrimaryColumn } from 'typeorm';

@Entity({name: "mkttranking"})
export class Ranking {
  @PrimaryColumn()
  @ManyToOne(_type => User)
  @JoinColumn({name: "player"})
  player: User;

  @PrimaryColumn()
  @Column()
  class: number;

  @Column()
  score: number;
}