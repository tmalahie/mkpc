import { Module } from '@nestjs/common';
import { SearchService } from 'src/search/search.service';
import { CircuitService } from 'src/track-builder/circuit.service';
import { TimeTrialController } from './time-trial.controller';

@Module({
  controllers: [TimeTrialController],
  providers: [CircuitService, SearchService]
})
export class TimeTrialModule { }
