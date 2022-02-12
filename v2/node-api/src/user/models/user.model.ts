import { Field, Int, ObjectType } from '@nestjs/graphql';

@ObjectType()
export class User {
  @Field(_type => Int)
  id: number;

  @Field()
  name: string;
}