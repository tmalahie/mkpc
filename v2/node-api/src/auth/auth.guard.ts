import { Injectable, CanActivate, ExecutionContext } from '@nestjs/common';
import { AuthUser } from 'src/user/user.decorator';
import axios from 'axios';
import { UserRole } from './userRole.entity';
import { EntityManager } from 'typeorm';
export enum Role {
  ADMIN = 'admin',
  MODERATOR = 'moderator',
  ORGANIZER = 'organizer',
  PUBLISHER = 'publisher',
  CLVALIDATOR = 'clvalidator',
  MANAGER = 'manager'
}
@Injectable()
export class EntityManagerProvider {
  public static em: EntityManager;
  constructor(em: EntityManager) {
    EntityManagerProvider.em = em;
  }
}
export class AuthGuard implements CanActivate {
  constructor(private loginRequired: boolean, private loadRoles, private requiredRoles: Role[]) {}
  
  async canActivate(
    context: ExecutionContext,
  ): Promise<boolean> {
    const em = EntityManagerProvider.em;
    const request = context.switchToHttp().getRequest();
    const id = await this.getUserIdFromRequest(request);
    const user: AuthUser = {
      id
    }
    if (this.loadRoles) {
      user.roles = {};
      const userRoles = await em.find(UserRole, {
        where: {
          player: id
        }
      });
      for (const role of userRoles) {
        user.roles[role.privilege] = true;
      }
      /*
		if (isset($res['admin'])) {
			$res['moderator'] = true;
			$res['organizer'] = true;
		}
		if (isset($res['moderator']) || isset($res['organizer']))
			$res['manager'] = true;*/
      if (user.roles.admin) {
        user.roles.moderator = true;
        user.roles.organizer = true;
      }
      if (user.roles.moderator || user.roles.organizer)
        user.roles.manager = true;
    }
    request.user = user;
    if (this.loginRequired && !user.id)
      return false;
    if (this.requiredRoles) {
      if (!user.roles)
        return false;
      if (!this.requiredRoles.some(role => user.roles[role]))
        return false;
    }
    return true;
  }

  private userIdCache: Record<string,{
    data: number,
    expiryHandler: NodeJS.Timeout
  }> = {}
  private async getUserIdFromRequest(request): Promise<number> {
    const sessId = request.cookies.PHPSESSID;
    if (this.userIdCache[sessId])
      return this.userIdCache[sessId].data;
    const host = request.headers["x-forwarded-host"] || request.headers.host;
    const protocol = request.secure ? "https":"http";
    const authUser = await axios.get(`${protocol}://${host}/api/authenticateUser.php`, {
      headers: {
        cookie: request.headers.cookie ?? "",
      }
    });
    const id = +authUser.data;
    if (this.userIdCache[sessId])
      clearTimeout(this.userIdCache[sessId].expiryHandler);
    const userCacheTtl = 10000000; // Cache of about 3h
    this.userIdCache[sessId] = {
      data: id,
      expiryHandler: setTimeout(() => {
        delete this.userIdCache[sessId];
      }, userCacheTtl)
    }
    return id;
  }
}