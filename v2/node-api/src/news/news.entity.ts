import { User } from 'src/user/user.entity';
import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Category } from './category.entity';

export enum NewsStatus {
  PENDING = "pending",
  ACCEPTED = "accepted",
  REJECTED = "rejected",
}

@Entity({name: "mknews"})
export class News {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  title: string;

  @ManyToOne(_type => Category)
  @JoinColumn({name: "category"})
  category: Category;

  @ManyToOne(_type => User)
  @JoinColumn({name: "author"})
  author: User;

  @Column({name: "creation_date"})
  creationDate: Date;

  @Column({name: "publication_date"})
  publicationDate: Date;

  @Column()
  content: string;

  @Column()
  status: NewsStatus;

  @Column({name: "reject_reason"})
  rejectReason: string;

  @Column({name: "nbcomments"})
  nbComments: number;

  @Column()
  locked: boolean;
}