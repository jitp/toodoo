import {Injectable} from '@angular/core';
import {Subject} from 'rxjs';

@Injectable({
    providedIn: 'root'
})
/**
 * Service to show loading indicator
 */
export class LoadingService {

    /**
     * Source to tell when to load or not the loading indicator.
     *
     * @type {Subject<boolean>}
     */
    protected loadingSource = new Subject<boolean>();

    /**
     * Observable to know when to load or not the indicator.
     *
     * @type {Observable<boolean>}
     */
    readonly loading$ = this.loadingSource.asObservable();

    constructor() {
    }

    /**
     * Start showing loading indicator
     */
    public start(): void {
        this.loadingSource.next(true);
    }

    /**
     * Stop showing loading indicator
     */
    public stop(): void {
        this.loadingSource.next(false);
    }
}
