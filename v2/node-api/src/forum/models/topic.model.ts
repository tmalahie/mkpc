import { Field, Int, ObjectType } from '@nestjs/graphql';
import { Category } from './category.model';
import { Message } from './message.model';

@ObjectType()
export class Topic {
  @Field(_type => Int)
  id: number;

  @Field()
  title: string;

  @Field()
  category: Category;

  @Field()
  locked: boolean;

  @Field()
  language: string;

  @Field()
  nbMessages: number;

  @Field()
  firstMessage: Message;

  @Field()
  lastMessage: Message;
}