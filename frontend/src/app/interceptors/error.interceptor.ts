import {HTTP_INTERCEPTORS, HttpErrorResponse, HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, throwError} from 'rxjs';
import {catchError} from 'rxjs/internal/operators';
import {NotifierService} from 'angular-notifier';

@Injectable()
/**
 * Intercepts all error responses from the app workflow.
 *
 */
export class ErrorInterceptor implements HttpInterceptor{

    constructor(
        protected notifierService: NotifierService
    ) {}

    /**
     * Intercept errors and show them.
     *
     * @param {HttpRequest<any>} request
     * @param {HttpHandler} next
     * @return {Observable<HttpEvent<any>>}
     */
    intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        return next.handle(request)
            .pipe(
                catchError(
                    err => {
                        this.notifyError(err);

                        return throwError(err);
                    })
            );
    }

    /**
     * Notify user about the error in a pop up message.
     *
     * @param {HttpErrorResponse} errorResponse
     */
    protected notifyError(errorResponse: HttpErrorResponse): void {
        let message = '';

        switch (errorResponse.status) {
            default:
                message = errorResponse.error.message || '';
        }

        if (message) {
            this.notifierService.notify('error', message);
        }
    }
}

/**
 * Provider configuration
 * @type {{provide: InjectionToken<HttpInterceptor[]>, useClass: ErrorInterceptor, multi: boolean}}
 */
export const ErrorInterceptorProvider = {
    provide: HTTP_INTERCEPTORS,
    useClass: ErrorInterceptor,
    multi: true
};
