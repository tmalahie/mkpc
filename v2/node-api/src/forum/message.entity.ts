import { User } from 'src/user/user.entity';
import { Entity, Column, PrimaryColumn, ManyToOne, JoinColumn } from 'typeorm';

@Entity({name: "mkmessages"})
export class Message {
  @PrimaryColumn()
  id: number;

  @PrimaryColumn()
  topic: number;

  @ManyToOne(_type => User)
  @JoinColumn({name: "auteur"})
  author: User;

  @Column()
  date: Date;

  @Column()
  message: string;
}