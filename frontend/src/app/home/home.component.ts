import {Component, OnInit} from '@angular/core';
import {FormArray, FormBuilder, FormGroup, Validators} from '@angular/forms';
import {RxwebValidators} from '@rxweb/reactive-form-validators';
import {Participant} from '../models/participant';
import {TodoList} from '../models/todo-list';
import {TodoListService} from '../services/todo-list.service';
import {LoadingService} from '../services/loading.service';
import {finalize} from 'rxjs/internal/operators';

@Component({
    selector: 'app-home',
    templateUrl: './home.component.html',
    styleUrls: ['./home.component.css']
})
/**
 * Home page component
 */
export class HomeComponent implements OnInit {

    /**
     * TodoList creation form
     */
    todoListForm: FormGroup;

    constructor(
        protected fb: FormBuilder,
        protected todoListService: TodoListService,
        protected loadingService: LoadingService
    ) {
        this.createTodoListForm();
    }

    ngOnInit() {
    }

    /**
     * Create the TodoList form group
     */
    createTodoListForm(): void {
        this.todoListForm = this.fb.group({
            name: ['', Validators.compose([
                RxwebValidators.required(),
                RxwebValidators.ascii(),
                RxwebValidators.maxLength({value: 150})
            ])],
            creator: this.fb.group({
                email: ['', Validators.compose([
                    RxwebValidators.required(),
                    RxwebValidators.email(),
                ])]
            }),
            participants: this.fb.array([])
        });
    }

    public get participants(): FormArray {
        return this.todoListForm.get('participants') as FormArray;
    }

    /**
     * Add new participant form control
     */
    public addParticipant() {
        this.participants.push(this.createParticipantFormGroup());
    }

    /**
     * Remove participant at given index
     *
     * @param {number} i
     */
    public removeParticipant(i: number) {
        this.participants.removeAt(i);
    }

    /**
     * Save TodoList form
     */
    public onSubmit() {
        const data = this.prepareSaveTodoList();

        this.loadingService.start();

        return this.todoListService
            .addTodoList(data)
            .pipe(
                finalize(
                    () => this.loadingService.stop()
                )
            )
            .subscribe(
                () => this.rebuildForm()
            )
    }

    /**
     * Create a participant form group.
     *
     * @param {Participant} participant
     * @return {FormGroup}
     */
    protected createParticipantFormGroup(participant: Participant = null): FormGroup {
        return this.fb.group({
            email: [participant ? participant.email : '', Validators.compose([
                RxwebValidators.email()
            ])]
        });
    }

    /**
     * Set new participants form array.
     *
     * @param {Participant[]} participants
     */
    protected setParticipants(participants: Participant[]): void {

        const participantFGs = participants.map(participant => this.createParticipantFormGroup(participant));

        const participantFormArray = this.fb.array(participantFGs);

        this.todoListForm.setControl('participants', participantFormArray);
    }

    /**
     * Prepare TodoList data to be saved.
     *
     * @return {Partial<TodoList>}
     */
    protected prepareSaveTodoList(): Partial<TodoList> {
        const formModel = this.todoListForm.value;

        // deep copy of form model participants
        const participantsDeepCopy: Participant[] = formModel.participants.map(
            (participant: Participant) => Object.assign({}, participant)
        );

        const saveTodoList: Partial<TodoList> = {
            name: formModel.name as string,
            creator: {...formModel.creator} as Participant,
            participants: participantsDeepCopy
        };

        return saveTodoList;
    }

    /**
     * Rebuild the form
     */
    protected rebuildForm() {
        this.todoListForm.reset();
        this.setParticipants([]);
    }
}
