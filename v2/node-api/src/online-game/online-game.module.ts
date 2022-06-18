import { Module } from '@nestjs/common';
import { SearchService } from 'src/search/search.service';
import { OnlineGameController } from './online-game.controller';

@Module({
  controllers: [OnlineGameController],
  providers: [SearchService],
})
export class OnlineGameModule {}
