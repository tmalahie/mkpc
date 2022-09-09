import { Module } from '@nestjs/common';
import {TrackBuilderController} from './track-builder.controller';
import {CircuitService} from './circuit.service';

@Module({
  controllers: [TrackBuilderController],
  providers: [CircuitService],
})
export class TrackBuilderModule {}
