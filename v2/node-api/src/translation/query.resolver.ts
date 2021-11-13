import { ExecutionContext, Injectable } from "@nestjs/common";
import { I18nResolver } from "nestjs-i18n";

@Injectable()
export class QueryResolver implements I18nResolver {
  constructor() {}

  resolve(context: ExecutionContext) {
    let req = context.switchToHttp().getRequest();
    return req.cookies.language === "0" ? "fr" : "en";
  }
}