import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {TodoListItem} from '../../models/todo-list-item';
import {TodoListService} from '../../services/todo-list.service';
import {NotifierService} from 'angular-notifier';
import {finalize} from 'rxjs/internal/operators';
import {TodoListItemStatusEnum} from '../../enums/todo-list-item-status-enum';
import {IMyDpOptions} from 'mydatepicker';
import {AbstractControl, FormBuilder} from '@angular/forms';

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
     * Get deadline formatted as for mydatetimepicker.
     *
     * @return {any}
     */
    get deadline(): any {
        const date = this.item.deadline ? this.item.deadline : '';
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

    /**
     * Change deadline of the item.
     *
     * @param $event
     */
    onDateChanged($event: any) {

        this.isSubmitting = true;

        const date = new Date($event.date.year, $event.date.month - 1, $event.date.day);

        this.todoListService.changeDeadline(this.hash, this.item.id, date.toUTCString())
            .pipe(
                finalize(() => this.isSubmitting = false),
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
