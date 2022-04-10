import { Module } from '@nestjs/common';
import { SearchService } from 'src/search/search.service';
import { UserController } from './user.controller';

@Module({
  controllers: [UserController],
  providers: [SearchService],
})
export class UserModule { }
