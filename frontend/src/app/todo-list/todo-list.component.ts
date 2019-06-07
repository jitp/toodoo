import {Component, OnDestroy, OnInit} from '@angular/core';
import {TodoList} from '../models/todo-list';
import {TodoListService} from '../services/todo-list.service';
import {ActivatedRoute} from '@angular/router';
import {
    catchError,
    delay,
    delay,
    delayWhen,
    map,
    mergeMap,
    repeat,
    skipUntil,
    skipWhile,
    switchMap,
    takeUntil
} from 'rxjs/internal/operators';
import {from, iif, interval, Observable, of, Subject, Subscription, throwError, timer} from 'rxjs';
import {isNullOrUndefined} from 'util';

@Component({
    selector: 'app-todo-list',
    templateUrl: './todo-list.component.html',
    styleUrls: ['./todo-list.component.css']
})
export class TodoListComponent implements OnInit, OnDestroy {

    /**
     * Current TodoList
     */
    todoList: TodoList;

    /**
     * Amount of time to wait for requesting TodoList info.
     *
     * @type {number}
     */
    protected delayTimeBetweenRequests = 30000;

    /**
     * User hash that is used for requests
     */
    protected hash: string;

    /**
     * Observable to stop requesting TodoList updates.
     */
    protected timerUnsubscribe: Subject<void> = new Subject();

    constructor(
        protected todoListService: TodoListService,
        protected route: ActivatedRoute
    ) {
        this.hash = this.route.snapshot.paramMap.get('hash');
    }

    ngOnInit() {

        this.startRequestingTodoListUpdates();
    }

    ngOnDestroy() {

        //Stopping TodoList update request
        this.timerUnsubscribe.next();
        this.timerUnsubscribe.complete();
    }

    /**
     * Start requesting TodoList updates periodically
     */
    startRequestingTodoListUpdates(): void {

        timer(0, this.delayTimeBetweenRequests)
            .pipe(
                takeUntil(this.timerUnsubscribe),
                switchMap(
                    () => this.todoListService.getTodoList(this.hash)
                        .pipe(
                            catchError(
                                (error) => {
                                    return of(null)
                                }
                            )
                        )
                ),
                skipWhile((value => isNullOrUndefined(value)))
            )
            .subscribe(todoList => {
                this.todoList = todoList
            })
        ;
    }

}
