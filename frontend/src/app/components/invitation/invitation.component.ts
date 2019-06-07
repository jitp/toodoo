import {Component, Input, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {RxwebValidators} from '@rxweb/reactive-form-validators';
import {TodoListService} from '../../services/todo-list.service';
import {finalize} from 'rxjs/internal/operators';
import {NotifierService} from 'angular-notifier';

const strings = {
    messages: {
        successSubmit: 'New participant has been invited!',
        failureSubmit: 'Invitation has not been done',
    }
};

@Component({
    selector: 'app-invitation',
    templateUrl: './invitation.component.html',
    styleUrls: ['./invitation.component.css']
})
/**
 * Form component to invite new users to participate in a TodoList
 */
export class InvitationComponent implements OnInit {

    /**
     * Flag to know when form is submitting.
     *
     * @type {boolean}
     */
    public isSubmitting = false;

    /**
     * Form to invite users to participate
     */
    public invitationForm: FormGroup;

    /**
     * Hash of the user whose inviting a new participant
     */
    @Input() public userInvitingHash: string;

    constructor(
        protected fb: FormBuilder,
        protected todoListService: TodoListService,
        protected notifierService: NotifierService
    ) {
        this.createForm();
    }

    ngOnInit() {
    }

    /**
     * Create invitation form
     */
    protected createForm(): void {
        this.invitationForm = this.fb.group({
            participant: ['', Validators.compose([
                RxwebValidators.required(),
                RxwebValidators.email()
            ])]
        });
    }

    /**
     * Submit form
     */
    onSubmit() {
        const formValue = this.invitationForm.value;

        this.isSubmitting = true;

        this.todoListService.invite(this.userInvitingHash, formValue)
            .pipe(
                finalize(() => {
                    this.isSubmitting = false;
                    this.rebuildForm();
                })
            )
            .subscribe(
                () => this.notifierService.notify('success', strings.messages.successSubmit),
                () => this.notifierService.notify('error', strings.messages.failureSubmit)
            );
    }

    /**
     * Set form to initial state.
     *
     */
    protected rebuildForm() {
        this.invitationForm.reset();
    }

}
