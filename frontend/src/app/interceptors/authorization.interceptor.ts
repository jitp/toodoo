import {
    HTTP_INTERCEPTORS,
    HttpEvent,
    HttpHandler,
    HttpInterceptor,
    HttpRequest,
    HttpResponse
} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {tap} from 'rxjs/internal/operators';
import {TodoListService} from '../services/todo-list.service';

@Injectable()
/**
 * Intercepts authorization response header from the app workflow.
 *
 */
export class AuthorizationInterceptor implements HttpInterceptor{

    constructor(
        protected todoListService: TodoListService
    ) {}

    /**
     * Intercept authorization response header and set it in TodoListService.
     *
     * @param {HttpRequest<any>} request
     * @param {HttpHandler} next
     * @return {Observable<HttpEvent<any>>}
     */
    intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        return next.handle(request)
            .pipe(
                tap((event) => {
                    if (event instanceof HttpResponse) {
                        if (event.headers.has('authorization')) {
                            this.todoListService.setAuthorizationToken(event.headers.get('authorization'));
                        }
                    }
                })
            );
    }
}

/**
 * Provider configuration
 * @type {{provide: InjectionToken<HttpInterceptor[]>, useClass: AuthorizationInterceptor, multi: boolean}}
 */
export const AuthorizationInterceptorProvider = {
    provide: HTTP_INTERCEPTORS,
    useClass: AuthorizationInterceptor,
    multi: true
};
