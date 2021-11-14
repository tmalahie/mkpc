import { createParamDecorator, ExecutionContext } from '@nestjs/common';
import { Role } from 'src/auth/auth.guard';

export type AuthUser = {
  id: number;
  roles?: Partial<Record<Role, true>>;
}

export const GetUser = createParamDecorator(
  (_data: string, ctx: ExecutionContext): AuthUser => {
    const request = ctx.switchToHttp().getRequest();
    return request.user;
  },
);