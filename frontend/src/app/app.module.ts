import {BrowserModule} from '@angular/platform-browser';
import {NgModule} from '@angular/core';

import {AppComponent} from './app.component';
import {HomeComponent} from './home/home.component';
import {AppRoutingModule} from './app-routing.module';
import {ReactiveFormsModule} from '@angular/forms';
import {RxReactiveFormsModule} from '@rxweb/reactive-form-validators';
import {NgBootstrapFormValidationModule} from 'ng-bootstrap-form-validation';
import {HttpClientModule} from '@angular/common/http';
import {NgxLoadingModule} from 'ngx-loading';
import {NotifierModule, NotifierOptions} from 'angular-notifier';
import {ErrorInterceptorProvider} from './interceptors/error.interceptor';
import {MY_CUSTOM_ERRORS_PROVIDER} from './utils/custom-errors';
import { PageNotFoundComponent } from './components/page-not-found/page-not-found.component';
import { TodoListComponent } from './todo-list/todo-list.component';
import { SweetAlert2Module } from '@sweetalert2/ngx-sweetalert2';
import {AuthorizationInterceptorProvider} from './interceptors/authorization.interceptor';

const notifierDefaultOptions: NotifierOptions = {
    position: {
        horizontal: {
            position: 'left',
            distance: 12
        },
        vertical: {
            position: 'bottom',
            distance: 12,
            gap: 10
        }
    },
    theme: 'material',
    behaviour: {
        autoHide: 5000,
        onClick: false,
        onMouseover: 'pauseAutoHide',
        showDismissButton: true,
        stacking: 4
    },
    animations: {
        enabled: true,
        show: {
            preset: 'slide',
            speed: 300,
            easing: 'ease'
        },
        hide: {
            preset: 'fade',
            speed: 300,
            easing: 'ease',
            offset: 50
        },
        shift: {
            speed: 300,
            easing: 'ease'
        },
        overlap: 150
    }
};


@NgModule({
    declarations: [
        AppComponent,
        PageNotFoundComponent,
        HomeComponent,
        TodoListComponent
    ],
    imports: [
        BrowserModule,
        AppRoutingModule,
        ReactiveFormsModule,
        RxReactiveFormsModule,
        NgBootstrapFormValidationModule.forRoot(),
        NgBootstrapFormValidationModule,
        HttpClientModule,
        NgxLoadingModule.forRoot({}),
        NotifierModule.withConfig( notifierDefaultOptions),
        SweetAlert2Module.forRoot({
            buttonsStyling: false,
            customClass: 'modal-content',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn'
        })
    ],
    providers: [
        ErrorInterceptorProvider,
        AuthorizationInterceptorProvider,
        MY_CUSTOM_ERRORS_PROVIDER
    ],
    bootstrap: [AppComponent]
})
export class AppModule {
}
