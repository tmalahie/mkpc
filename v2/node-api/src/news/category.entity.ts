import { Entity, Column, PrimaryGeneratedColumn } from 'typeorm';

@Entity({name: "mkcats"})
export class Category {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  private name0: string;
  @Column()
  private name1: string;

  @Column()
  color: string;

  getName(lang: string) {
    return (lang === "fr") ? this.name0:this.name1;
  }
}