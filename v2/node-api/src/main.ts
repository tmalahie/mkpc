import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { StatusInterceptor } from './app/status.interceptor';
import * as cookieParser from 'cookie-parser';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);
  app.use(cookieParser());
  app.useGlobalInterceptors(new StatusInterceptor());
  await app.listen(8000);
}
bootstrap();
