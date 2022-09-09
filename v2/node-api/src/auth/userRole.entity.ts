import { Entity, PrimaryColumn } from 'typeorm';
import { Role } from "./auth.guard"

@Entity({name: "mkrights"})
export class UserRole {
  @PrimaryColumn()
  player: number;

  @PrimaryColumn()
  privilege: Role;
}