import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Category } from './category.entity';

@Entity({name: "mktopics"})
export class Topic {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({name: "titre"})
  title: string;

  @ManyToOne(_type => Category)
  @JoinColumn({name: "category"})
  category: Category;

  @Column()
  language: boolean;

  @Column()
  private: boolean;

  @Column()
  locked: boolean;

  @Column({name: "nbmsgs"})
  nbMessages: number;

  @Column({name: "dernier"})
  lastMessageDate: number;
}