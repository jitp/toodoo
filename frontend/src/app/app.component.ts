import {Component, OnInit} from '@angular/core';
import {LoadingService} from './services/loading.service';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
    title = 'toodoo';
    loading = false;

    constructor(
        protected loadingService: LoadingService
    ) {}

    ngOnInit() {
        /**
         * Subscribe to loading service. This way component know when to show or hide
         * the loading indicator.
         */
        this.loadingService.loading$.subscribe(value => this.loading = value);
    }
}
