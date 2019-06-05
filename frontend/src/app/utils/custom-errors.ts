import {CUSTOM_ERROR_MESSAGES, ErrorMessage} from 'ng-bootstrap-form-validation';

export const CUSTOM_ERRORS: ErrorMessage[] = [
    {
        error: "remote",
        format: remoteFormat
    }
];

export const MY_CUSTOM_ERRORS_PROVIDER = {
    provide: CUSTOM_ERROR_MESSAGES,
    useValue: CUSTOM_ERRORS,
    multi: true
};

/**
 * Print messages for server response errors.
 *
 * @param {string} label
 * @param {string} error
 * @return {string}
 */
export function remoteFormat(label: string, error: string): string {
    return `${error}`;
}