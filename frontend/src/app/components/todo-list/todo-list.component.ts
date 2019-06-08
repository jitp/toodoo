import {Component, ElementRef, OnDestroy, OnInit, ViewChild} from '@angular/core';
import {TodoList} from '../../models/todo-list';
import {TodoListService} from '../../services/todo-list.service';
import {ActivatedRoute} from '@angular/router';
import {
    catchError,
    finalize,
    skipWhile,
    switchMap,
    takeUntil
} from 'rxjs/internal/operators';
import {of, Subject, timer} from 'rxjs';
import {isNullOrUndefined} from 'util';
import {LoadingService} from '../../services/loading.service';
import {NotifierService} from 'angular-notifier';
import {TodoListItem} from '../../models/todo-list-item';

const strings = {
    messages: {
        deleteSucess: 'The list has been removed!',
    }
};

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
        protected route: ActivatedRoute,
        protected loadingService: LoadingService,
        protected notifierService: NotifierService
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

    /**
     * Delete TodoList.
     *
     * @param {string} hash
     */
    deleteTodoList(hash: string): void {

        this.loadingService.start();

        this.todoListService.deleteTodoList(hash)
            .pipe(
                finalize(
                    () => this.loadingService.stop()
                )
            )
            .subscribe(
                () => {
                    this.todoListService.goHome();
                    this.notifierService.notify('success', strings.messages.deleteSucess);
                }
            )
    }

    /**
     * Get TodoListItems of current list.
     *
     * @return {TodoListItem[]}
     */
    get todoListItems(): TodoListItem[] {
        return this.todoList ? this.todoList.items : [];
    }

    /**
     * Listen TodoListItem creation and update TodoList.
     *
     * @param {TodoList} $event
     */
    onTodoListItemCreated($event: TodoListItem): void {
        this.todoListItems.push($event);
    }

    /**
     * Listen TodoListItem deleted and update TodoList.
     *
     * @param {TodoListItem} $event
     */
    onTodoListItemDeleted($event: TodoListItem): void {
        this.todoList.items = this.todoList.items.filter((item => item.id !== $event.id));
    }
}
