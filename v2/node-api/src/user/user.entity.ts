import { Entity, Column, PrimaryGeneratedColumn } from 'typeorm';

@Entity({name: "mkjoueurs"})
export class User {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({name: "nom"})
  name: string;
}