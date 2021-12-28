import { Module } from '@nestjs/common';
import { SearchService } from 'src/search/search.service';
import { NewsController } from './news.controller';

@Module({
  controllers: [NewsController],
  providers: [SearchService]
})
export class NewsModule { }
