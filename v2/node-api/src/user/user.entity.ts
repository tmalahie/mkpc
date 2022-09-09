import { Entity, Column, PrimaryGeneratedColumn, OneToOne, JoinColumn } from 'typeorm';
import { Profile } from './profile.entity';

@Entity({ name: "mkjoueurs" })
export class User {
  @PrimaryGeneratedColumn()
  id: number;

  @OneToOne(_type => Profile)
  @JoinColumn({name: "id"})
  profile: Profile;

  @Column({ name: "nom" })
  name: string;

  @Column()
  pts_vs: string;

  @Column()
  pts_battle: string;

  @Column()
  pts_challenge: string;

  @Column()
  banned: boolean;

  @Column()
  deleted: boolean;
}