import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {TodoList} from '../models/todo-list';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';

const httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
};

@Injectable({
    providedIn: 'root'
})
/**
 * Provide services for TodoList actions
 */
export class TodoListService {

    readonly todoListsUrl = environment.baseUrl + 'api/todolist';

    constructor(
        protected http: HttpClient
    ) {
    }

    /**
     * Add new TodoList
     *
     * @param {TodoList} todoList
     * @return {Observable<TodoList>}
     */
    addTodoList(todoList: Partial<TodoList>): Observable<TodoList> {
        return this.http.post<TodoList>(this.todoListsUrl, todoList, httpOptions);
    }
}
