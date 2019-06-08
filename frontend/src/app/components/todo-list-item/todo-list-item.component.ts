import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {TodoListItem} from '../../models/todo-list-item';
import {TodoListService} from '../../services/todo-list.service';
import {NotifierService} from 'angular-notifier';
import {finalize} from 'rxjs/internal/operators';
import {TodoListItemStatusEnum} from '../../enums/todo-list-item-status-enum';

const strings = {
    messages: {
        deleteSucess: 'The task has been removed!',
        statusUpdateSuccess: 'Status changed!'
    }
};

@Component({
    selector: 'app-todo-list-item',
    templateUrl: './todo-list-item.component.html',
    styleUrls: ['./todo-list-item.component.css']
})
/**
 * Represent an item in a TodoList component
 */
export class TodoListItemComponent implements OnInit {

    /**
     * Flag to determine when an item is performing a server request.
     *
     * @type {boolean}
     */
    public isSubmitting = false;

    /**
     * TodoListItem to represent
     */
    @Input() item: TodoListItem;

    /**
     * Current user personal list hash
     */
    @Input() hash: string;

    /**
     * Notify the item has been deleted.
     *
     * @type {EventEmitter<TodoListItem>}
     */
    @Output() deleted = new EventEmitter<TodoListItem>();

    constructor(
        protected todoListService: TodoListService,
        protected notifierService: NotifierService
    ) {
    }

    ngOnInit() {
    }

    /**
     * Delete the item.
     */
    delete(): void {

        this.isSubmitting = true;

        this.todoListService.deleteTodoListItem(this.hash, this.item.id)
            .pipe(
                finalize(() => this.isSubmitting = false),
            )
            .subscribe(
                (todoListItem: TodoListItem) => {
                    this.deleted.emit(todoListItem);
                    this.notifierService.notify('success', strings.messages.deleteSucess);
                }
            )
    }

    /**
     * Determine if item is already done.
     *
     * @return {boolean}
     */
    get isDone(): boolean {
        return this.item.status === TodoListItemStatusEnum.DONE;
    }

    /**
     * Toggle the status of the item
     */
    changeStatus(): void {

        this.isSubmitting = true;

        this.todoListService.toggleTodoListItemStatus(this.hash, this.item.id)
            .pipe(
                finalize(() => this.isSubmitting = false),
            )
            .subscribe(
                (todoListItem: TodoListItem) => {
                    this.item = todoListItem;
                    this.notifierService.notify('success', strings.messages.statusUpdateSuccess);
                }
            )
    }

}
