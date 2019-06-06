import {Component, OnInit} from '@angular/core';
import {TodoList} from '../models/todo-list';
import {TodoListService} from '../services/todo-list.service';
import {ActivatedRoute} from '@angular/router';

@Component({
    selector: 'app-todo-list',
    templateUrl: './todo-list.component.html',
    styleUrls: ['./todo-list.component.css']
})
export class TodoListComponent implements OnInit {

    /**
     * Current TodoList
     */
    todoList: TodoList;

    constructor(
        protected todoListService: TodoListService,
        protected route: ActivatedRoute
    ) {
    }

    ngOnInit() {
        this.getTodoList();
    }

    getTodoList(): void {
        const hash = this.route.snapshot.paramMap.get('hash');

        this.todoListService.getTodoList(hash)
            .subscribe(todoList => {
                this.todoList = todoList
            });
    }

}
