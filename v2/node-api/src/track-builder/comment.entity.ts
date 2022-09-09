import { User } from "src/user/user.entity";
import { Column, Entity, JoinColumn, ManyToOne, PrimaryGeneratedColumn } from "typeorm";
import { CircuitType } from "./circuit.service";

@Entity({name: "mkcomments"})
export class Comment {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  circuit: number;

  @Column()
  type: CircuitType;

  @ManyToOne(_type => User)
  @JoinColumn({name: "auteur"})
  author: User;

  @Column()
  message: string;

  @Column()
  date: Date;
}