import { Controller, Get, HttpCode } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { EntityManager } from 'typeorm';
import { User } from './user.entity';
import { Ban } from './ban.entity';
import { AuthUser, GetUser } from './user.decorator';

@Controller("/user")
export class UserController {
  constructor(private em: EntityManager) { }

  @Auth({ loadRoles: true })
  @Get("/me")
  async getMe(@GetUser() authUser: AuthUser) {
    if (!authUser.id)
      return null;
    const user = await this.em.findOne(User, authUser.id);
    return {
      id: user.id,
      name: user.name,
      roles: authUser.roles,
      banned: user.banned || undefined
    }
  }

  @Auth({ loadRoles: true })
  @Get("/me/banData")
  async getBanMessage(@GetUser() authUser: AuthUser) {
    if (!authUser.id)
      return null;
    const ban = await this.em.findOne(Ban, authUser.id);
    if (!ban)
      return null;
    return {
      message: ban.message,
      endDate: ban.endDate
    }
  }
}
