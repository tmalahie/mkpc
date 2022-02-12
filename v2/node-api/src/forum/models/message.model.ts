import { Field, Int, ObjectType } from '@nestjs/graphql';
import { User } from '../../user/models/user.model';

@ObjectType()
export class Message {
  @Field(_type => Int)
  id: number;

  @Field()
  author: User;

  @Field()
  date: Date;

  @Field()
  message: string;
}