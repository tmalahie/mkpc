import { Entity, PrimaryColumn, Column } from 'typeorm';

@Entity({ name: "mkbans" })
export class Ban {
  @PrimaryColumn()
  player: number;

  @Column({ name: "msg" })
  message: string;

  @Column({ name: "end_date" })
  endDate: Date;
}