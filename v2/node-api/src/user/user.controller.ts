import { Controller, Get, HttpCode } from '@nestjs/common';
import { Auth } from '../auth/auth.decorator';
import { EntityManager } from 'typeorm';
import { User } from './user.entity';
import { AuthUser, GetUser } from './user.decorator';

@Controller("/user")
export class UserController {
  constructor(private em: EntityManager) { }

  @Auth({ loadRoles: true })
  @Get("/me")
  async getMe(@GetUser() authUser: AuthUser) {
    if (!authUser.id)
      return null;
    const user = await this.em.findOne(User, {
      where: {
        id: authUser.id
      }
    });
    return {
      id: user.id,
      name: user.name,
      roles: authUser.roles,
      banned: user.banned || undefined
    }
  }
}
