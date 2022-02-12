import { Field, Int, ObjectType, PickType } from '@nestjs/graphql';

@ObjectType()
export class Category {
  @Field(_type => Int)
  id: number;

  @Field()
  name: string;
}