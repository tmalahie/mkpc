import { Entity, Column, PrimaryGeneratedColumn } from 'typeorm';

@Entity({name: "mkcategories"})
export class Category {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  name: string;

  @Column()
  description: string;

  @Column()
  ordering: number;

  @Column()
  adminonly: boolean;
}