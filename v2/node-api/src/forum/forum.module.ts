import { Module } from '@nestjs/common';
import { SearchService } from 'src/search/search.service';
import { ForumController } from './forum.controller';

@Module({
    controllers: [ForumController],
    providers: [SearchService]
})
export class ForumModule { }
