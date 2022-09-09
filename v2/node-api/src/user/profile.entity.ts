import { Column, Entity, JoinColumn, OneToOne, PrimaryColumn, PrimaryGeneratedColumn } from 'typeorm';
import { User } from './user.entity';
import { Country } from './country.entity';

@Entity({name: "mkprofiles"})
export class Profile {
  @PrimaryColumn()
  id: number;

  @OneToOne(_type => User)
  @JoinColumn({name: "id"})
  user: User;

  @OneToOne(_type => Country)
  @JoinColumn({name: "country"})
  country: Country;

  @Column()
  email: string;

  @Column({name: "birthdate"})
  birthDate: Date;

  @Column({name: "sub_date"})
  subDate: Date;

  @Column({name: "last_connect"})
  lastConnectDate: Date;

  @Column()
  description: Date;

  @Column({name: "nbmessages"})
  nbMessages: number;
}