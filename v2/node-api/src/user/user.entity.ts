import { Entity, Column, PrimaryGeneratedColumn } from 'typeorm';

@Entity({name: "mkjoueurs"})
export class User {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({name: "nom"})
  name: string;

  @Column()
  pts_vs: string;

  @Column()
  pts_battle: string;

  @Column()
  pts_challenge: string;

  @Column()
  deleted: boolean;
}