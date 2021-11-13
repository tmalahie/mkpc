import { Module } from '@nestjs/common';
import * as path from 'path';
import { I18nJsonParser, I18nModule } from 'nestjs-i18n';
import { QueryResolver } from './query.resolver';

@Module({
  imports: [I18nModule.forRoot({
    fallbackLanguage: 'en',
    parser: I18nJsonParser,
    parserOptions: {
      path: path.join(__dirname, '/i18n/'),
      watch: true
    },
    resolvers: [
      new QueryResolver()
    ]
  })]
})
export class TranslationModule {}
