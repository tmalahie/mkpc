import { Module } from '@nestjs/common';
import { SearchService } from './search.service';

@Module({
  providers: [SearchService],
})
export class SearchModule { }
