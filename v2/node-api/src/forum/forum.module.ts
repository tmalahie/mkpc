import { Module } from '@nestjs/common';
import { ForumController } from './forum.controller';

@Module({
    imports: [],
    controllers: [ForumController],
    providers: [],
  })
export class ForumModule {}
