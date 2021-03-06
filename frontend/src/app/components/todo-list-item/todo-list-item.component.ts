import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {TodoListItem} from '../../models/todo-list-item';
import {TodoListService} from '../../services/todo-list.service';
import {NotifierService} from 'angular-notifier';
import {finalize} from 'rxjs/internal/operators';
import {TodoListItemStatusEnum} from '../../enums/todo-list-item-status-enum';
import {IMyDpOptions} from 'mydatepicker';
import {AbstractControl, FormBuilder} from '@angular/forms';
import {isNullOrUndefined} from 'util';

const strings = {
    messages: {
        deleteSucess: 'The task has been removed!',
        statusUpdateSuccess: 'Status changed!',
        deadlineUpdateSuccess: 'Deadline changed!',
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

    /**
     * Notify when a changing is being persisted.
     *
     * @type {EventEmitter<boolean>}
     */
    @Output() changing = new EventEmitter<boolean>();

    /**
     * Configuration for mydatepicker.
     *
     * @type {{IMyDpOptions}}
     */
    myDatePickerOptions: IMyDpOptions = {
        // other options...
        showInputField: false,
        showClearDateBtn: false,
        ariaLabelOpenCalendar: 'Change deadline',
    };

    /**
     * Deadline form control
     */
    deadlineFormControl: AbstractControl;

    constructor(
        protected todoListService: TodoListService,
        protected notifierService: NotifierService,
        protected fb: FormBuilder
    ) {
    }

    ngOnInit() {
        //Defining and setting default deadline on form control
        this.deadlineFormControl = this.fb.control(this.deadline);
    }

    /**
     * Delete the item.
     */
    delete(): void {

        this.isSubmitting = true;
        this.changing.emit(true);

        this.todoListService.deleteTodoListItem(this.hash, this.item.id)
            .pipe(
                finalize(() => {
                    this.isSubmitting = false;
                    this.changing.emit(false);
                }),
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
     * Get deadline formatted as for mydatetimepicker.
     *
     * @return {any}
     */
    get deadline(): any {
        const date = this.item.deadline ? this.item.deadline : null;

        if (isNullOrUndefined(date)) {
            return date;
        }

        const dateInstance = new Date(date);

        return {
            date: {
                year: dateInstance.getFullYear(),
                month: dateInstance.getMonth() + 1,
                day: dateInstance.getDate()
            }
        }
    }

    /**
     * Determine if TodoListItem is expired.
     *
     * @return {boolean}
     */
    get isExpired(): boolean {
        return (this.item.deadline && (new Date(this.item.deadline)) < (new Date()));
    }

    /**
     * Toggle the status of the item
     */
    changeStatus(): void {

        this.isSubmitting = true;
        this.changing.emit(true);

        this.todoListService.toggleTodoListItemStatus(this.hash, this.item.id)
            .pipe(
                finalize(() => {
                    this.isSubmitting = false;
                    this.changing.emit(false);
                }),
            )
            .subscribe(
                (todoListItem: TodoListItem) => {
                    this.item = todoListItem;
                    this.notifierService.notify('success', strings.messages.statusUpdateSuccess);
                }
            )
    }

    /**
     * Change deadline of the item.
     *
     * @param $event
     */
    onDateChanged($event: any) {

        this.isSubmitting = true;
        this.changing.emit(true);

        let date = '';

        if ($event) {
            date = (new Date(Date.UTC($event.date.year, $event.date.month - 1, $event.date.day))).toISOString();
        }

        this.todoListService.changeDeadline(this.hash, this.item.id, date)
            .pipe(
                finalize(() => {
                    this.isSubmitting = false;
                    this.changing.emit(false);
                }),
            )
            .subscribe(
                (todoListItem: TodoListItem) => {
                    this.item = todoListItem;
                    this.notifierService.notify('success', strings.messages.deadlineUpdateSuccess);
                }
            )
        ;
    }

}
