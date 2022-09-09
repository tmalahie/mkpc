import { Module } from '@nestjs/common';
import { ForumModule } from './forum/forum.module';
import { TypeOrmModule } from '@nestjs/typeorm';
import { join } from 'path';
import { ConfigModule } from '@nestjs/config';
import { TranslationModule } from './translation/translation.module';
import { AuthModule } from './auth/auth.module';
import { NewsModule } from './news/news.module';
import { TrackBuilderModule } from './track-builder/track-builder.module';
import { TimeTrialModule } from './time-trial/time-trial.module';
import { OnlineGameModule } from './online-game/online-game.module';
import { SearchModule } from './search/search.module';
import { UserModule } from './user/user.module';

@Module({
  imports: [
    ConfigModule.forRoot(),
    TypeOrmModule.forRoot({
      type: 'mysql',
      host: process.env.DB_HOST,
      port: +process.env.DB_PORT,
      username: process.env.DB_USER,
      password: process.env.DB_PASSWORD,
      database: process.env.DB_NAME,
      //logging: true,
      entities: [join(__dirname, '**', '*.entity.{ts,js}')],
    }), ForumModule, TranslationModule, AuthModule, NewsModule, TrackBuilderModule, TimeTrialModule, OnlineGameModule, SearchModule, UserModule],
})
export class AppModule { }
