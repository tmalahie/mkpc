import { Injectable } from '@nestjs/common';
import { EntityManager, EntityTarget, FindOneOptions, In, LessThan, MoreThan, Like } from 'typeorm';
import { EntityFieldsNames } from 'typeorm/common/EntityFieldsNames';

export enum SearchType {
  EQUALS = "=",
  LIKE = "%",
  MORE_THAN = ">",
  LESS_THAN = "<",
  IN = "in"
}

type SearchFilter = {
  key: string;
  operator: SearchType;
  value: any;
}
type SearchOrder = {
  key: string;
  order: "desc" | "asc";
}
type SearchPaging = {
  limit: number;
  offset: number;
  count: boolean;
}
type SearchParams = {
  filters?: SearchFilter[],
  sort?: SearchOrder,
  paging?: SearchPaging
}
type SearchRules<T> = {
  allowedFilters: Partial<Record<EntityFieldsNames<T>, SearchType[]>>,
  allowedOrders: EntityFieldsNames<T>[],
  maxResults?: number;
  canReturnCount?: boolean;
}
export const SEARCH_ALL_ALLOWED = [SearchType.EQUALS, SearchType.LIKE, SearchType.LESS_THAN, SearchType.MORE_THAN, SearchType.IN];
export const EQUALITY_SEARCH = [SearchType.EQUALS, SearchType.IN];
type SearchOptions<T> = {
  entity: EntityTarget<T>,
  params: SearchParams;
  where?: FindOneOptions<T>["where"],
  relations?: FindOneOptions<T>["relations"],
  rules: SearchRules<T>;
}

type SearchResult<T> = {
  data: T[],
  count?: number;
}

function escapeSqlLike(search) {
  return search.replace(/([%_\\])/g, "\\$1");
}

@Injectable()
export class SearchService {
  constructor(private em: EntityManager) { }

  private buildFilterFromParams<T>(options: SearchOptions<T>) {
    let where: FindOneOptions<T>["where"] = options.where || {};
    if (options.params?.filters) {
      for (const filter of options.params.filters) {
        const allowedFilters = options.rules.allowedFilters[filter.key];
        if (allowedFilters?.includes(filter.operator)) {
          if (where[filter.key]) continue;
          let newFilter;
          switch (filter.operator) {
            case SearchType.EQUALS:
              newFilter = filter.value;
              break;
            case SearchType.IN:
              newFilter = In(filter.value);;
              break;
            case SearchType.LESS_THAN:
              newFilter = LessThan(filter.value);;
              break;
            case SearchType.MORE_THAN:
              newFilter = MoreThan(filter.value);;
              break;
            case SearchType.LIKE:
              newFilter = Like("%" + escapeSqlLike(filter.value) + "%");
              break;
          }
          where[filter.key] = newFilter;
        }
      }
    }
    return where;
  }
  private buildOrderFromParams<T>(options: SearchOptions<T>) {
    let order: FindOneOptions<T>["order"];
    if (options.params?.sort) {
      const key = options.params.sort.key as EntityFieldsNames<T>;
      if (options.rules.allowedOrders.includes(key)) {
        order = {
          [key]: options.params.sort.order === "desc" ? "DESC" : "ASC"
        } as any
      }
    }
    return order;
  }

  async find<T>(options: SearchOptions<T>): Promise<SearchResult<T>> {
    const where = this.buildFilterFromParams<T>(options);
    const order = this.buildOrderFromParams<T>(options);
    const maxResults = options.rules.maxResults || 20;
    let take = options.params?.paging?.limit ?? maxResults;
    if (take > maxResults) take = maxResults;
    const skip = options.params?.paging?.offset ?? 0;
    let relations = options.relations;
    const data = (take > 0) ? await this.em.find(options.entity, {
      where,
      order,
      relations,
      take,
      skip
    }) : [];
    let count = data.length;
    if (options.params?.paging?.count && options.rules.canReturnCount) {
      count = await this.em.count(options.entity, {
        where
      });
    }
    return { data, count };
  }
}
