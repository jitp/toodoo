import {HttpErrorResponse} from '@angular/common/http';
import {AbstractControl} from '@angular/forms';

/**
 * Render under each form field the server response field errors.
 *
 * @param {HttpErrorResponse} errorResponse
 * @param {AbstractControl} form
 */
export function render422FormFieldErrors(errorResponse: HttpErrorResponse, form: AbstractControl) {
    if (errorResponse.status == 422 && errorResponse.error.hasOwnProperty('errors')) {
        for (let field in errorResponse.error.errors) {
            form.get(field).setErrors({
                remote: errorResponse.error.errors[field][0]
            })
        }
    }
}