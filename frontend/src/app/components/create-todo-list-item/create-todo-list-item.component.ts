import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {NotifierService} from 'angular-notifier';
import {RxwebValidators} from '@rxweb/reactive-form-validators';
import {TodoListService} from '../../services/todo-list.service';
import {finalize} from 'rxjs/internal/operators';
import {TodoListItem} from '../../models/todo-list-item';

const strings = {
    messages: {
        successSubmit: 'New task created!',
        failureSubmit: 'Task has not been created',
    }
};

@Component({
    selector: 'app-create-todo-list-item',
    templateUrl: './create-todo-list-item.component.html',
    styleUrls: ['./create-todo-list-item.component.css']
})
/**
 * Form component to add a new TodoListItem
 */
export class CreateTodoListItemComponent implements OnInit {

    /**
     * Flag to know when form is submitting.
     *
     * @type {boolean}
     */
    isSubmitting = false;

    /**
     * Current user personal TodoList hash
     */
    @Input() protected hash: string;

    /**
     * Provide the new TodoListItem created.
     *
     * @type {EventEmitter<TodoListItem>}
     */
    @Output() public created = new EventEmitter<TodoListItem>();

    /**
     * Form to create TodoListItem
     */
    public form: FormGroup;

    constructor(
        protected fb: FormBuilder,
        protected notifierService: NotifierService,
        protected todoListService: TodoListService
    ) {
        this.createForm();
    }

    ngOnInit() {
    }

    /**
     * Create TodoListItem form
     */
    createForm() {

        this.form = this.fb.group({
            name: ['', Validators.compose([
                RxwebValidators.required(),
                RxwebValidators.ascii(),
                RxwebValidators.maxLength({value: 150})
            ])]
        });
    }

    /**
     * Submit the form
     */
    onSubmit() {
        const item = this.form.value;

        this.isSubmitting = true;

        this.todoListService.createTodoListItem(this.hash, item)
            .pipe(
                finalize(() => {
                    this.isSubmitting = false;
                    this.rebuildForm();
                })
            )
            .subscribe(
                (todoListItem: TodoListItem) => {
                    this.notifierService.notify('success', strings.messages.successSubmit);

                    //Notify TodoListItem
                    this.created.emit(todoListItem);
                },
                () => this.notifierService.notify('error', strings.messages.failureSubmit)
            );
    }

    /**
     * Set form to initial state.
     *
     */
    protected rebuildForm() {
        this.form.reset();
    }

}
