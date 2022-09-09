import { Entity, Column, PrimaryColumn } from 'typeorm';

@Entity({ name: "mknewsread" })
export class NewsRead {
  @PrimaryColumn()
  user: number;

  @Column({ name: "date" })
  date: Date;
}