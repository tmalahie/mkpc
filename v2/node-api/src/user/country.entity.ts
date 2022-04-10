import { Entity, Column, PrimaryGeneratedColumn } from 'typeorm';

@Entity({ name: "mkcountries" })
export class Country {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  code: string;

  @Column()
  private name_fr: string;
  @Column()
  private name_en: string;

  @Column()
  ordering: number;

  getName(lang: string) {
    return (lang === "fr") ? this.name_fr:this.name_en;
  }
}