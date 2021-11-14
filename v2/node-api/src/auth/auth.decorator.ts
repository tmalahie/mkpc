import { applyDecorators, UseGuards } from '@nestjs/common';
import { AuthGuard, Role } from './auth.guard';

export function Auth({
  login = false,
  loadRoles = false,
  roles = null as Role[]
} = {}) {
  return applyDecorators(
    UseGuards(AuthGuard.bind(null, login, loadRoles, roles)),
  );
}