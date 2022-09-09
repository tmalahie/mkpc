import { ExecutionContext, HttpStatus, Injectable, NestInterceptor } from '@nestjs/common';
import { Observable, map } from 'rxjs';

@Injectable()
export class StatusInterceptor implements NestInterceptor {
  intercept(context: ExecutionContext, next): Observable<any> {
    return next.handle().pipe(
      map((data: any) => {
        if (data === null) {
          const response = context.switchToHttp().getResponse();
          if (response.statusCode < HttpStatus.NO_CONTENT)
            response.status(HttpStatus.NO_CONTENT);
        }
        return data;
      }),
    );
  }
}