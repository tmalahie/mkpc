import { Module } from '@nestjs/common';
import { SearchService } from 'src/search/search.service';
import { ForumController } from './forum.controller';
import { ForumResolver } from './forum.resolver';

@Module({
    controllers: [ForumController],
    providers: [ForumResolver, SearchService],
})
export class ForumModule { }
