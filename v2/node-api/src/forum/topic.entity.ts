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
  private language: Buffer;
  getLanguage() {
    return this.language.toString() === '\x00' ? "fr":"en";
  }
  setLanguage(language) {
    this.language = Buffer.from(language === "fr" ? "\x00" : "\x01");
  }

  @Column()
  private: boolean;

  @Column()
  locked: boolean;

  @Column({name: "nbmsgs"})
  nbMessages: number;

  @Column({name: "dernier"})
  lastMessageDate: number;
}