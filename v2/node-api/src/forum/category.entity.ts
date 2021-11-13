import { Entity, Column, PrimaryGeneratedColumn } from 'typeorm';

@Entity({name: "mkcategories"})
export class Category {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  private nom: string;
  @Column()
  private name: string;

  getName(lang: string) {
    return (lang === "fr") ? this.nom:this.name;
  }

  @Column()
  private description: string;
  @Column()
  private summary: string;

  getDescription(lang: string) {
    return (lang === "fr") ? this.description:this.summary;
  }

  @Column()
  ordre: number;
  @Column()
  ordering: number;
  getOrder(lang: string) {
    return (lang === "fr") ? this.ordre:this.ordering;
  }

  @Column()
  adminonly: boolean;
}