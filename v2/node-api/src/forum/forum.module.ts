import { Module } from '@nestjs/common';
import { ForumController } from './forum.controller';

@Module({
    controllers: [ForumController],
})
export class ForumModule {}
