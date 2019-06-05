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

@NgModule({
    declarations: [
        AppComponent,
        HomeComponent
    ],
    imports: [
        BrowserModule,
        AppRoutingModule,
        ReactiveFormsModule,
        RxReactiveFormsModule,
        NgBootstrapFormValidationModule.forRoot(),
        NgBootstrapFormValidationModule,
        HttpClientModule,
        NgxLoadingModule.forRoot({})
    ],
    providers: [],
    bootstrap: [AppComponent]
})
export class AppModule {
}
