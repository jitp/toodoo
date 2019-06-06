import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {HomeComponent} from './home/home.component';
import {TodoListComponent} from './todo-list/todo-list.component';
import {PageNotFoundComponent} from './components/page-not-found/page-not-found.component';

// App routes to different components
const routes: Routes = [
    {
        path: '',
        redirectTo: '/home',
        pathMatch: 'full'
    },
    {
        path: 'home',
        component: HomeComponent
    },
    {
        path: 'todo-list/:hash',
        component: TodoListComponent
    },
    {
        path: '**',
        component: PageNotFoundComponent
    }
];


@NgModule({
    imports: [
        RouterModule.forRoot(routes)
    ],
    exports: [
        RouterModule
    ]
})
/**
 * Class AppRoutingModule
 *
 * Its purpose is to bring routing to the app.
 */
export class AppRoutingModule {
}
