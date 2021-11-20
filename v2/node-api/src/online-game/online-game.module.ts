import { Module } from '@nestjs/common';
import { OnlineGameController } from './online-game.controller';

@Module({
  controllers: [OnlineGameController]
})
export class OnlineGameModule {}
