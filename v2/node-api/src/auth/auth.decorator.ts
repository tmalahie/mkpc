import { applyDecorators, UseGuards } from '@nestjs/common';
import { EntityManagerProvider, AuthGuard, Role } from './auth.guard';

export function Auth({
  login = false,
  loadRoles = false,
  roles = null as Role[]
} = {}) {
  return applyDecorators(
    // TODO see if there is a better way to pass parameters in a decorator
    UseGuards(EntityManagerProvider, AuthGuard.bind(null, login, loadRoles, roles)),
  );
}