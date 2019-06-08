import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {TodoList} from '../models/todo-list';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';
import {map} from 'rxjs/internal/operators';
import {Router} from '@angular/router';

@Injectable({
    providedIn: 'root'
})
/**
 * Provide services for TodoList actions
 */
export class TodoListService {

    readonly todoListsUrl = environment.baseUrl + 'api/todolist';

    protected authorizationToken = '';

    constructor(
        protected http: HttpClient,
        protected route: Router
    ) {
    }

    /**
     * Add new TodoList
     *
     * @param {TodoList} todoList
     * @return {Observable<TodoList>}
     */
    addTodoList(todoList: Partial<TodoList>): Observable<TodoList> {
        return this.http.post<TodoList>(this.todoListsUrl, todoList, this.httpOptions);
    }

    /**
     * Get TodoList
     *
     * @param {string} hash
     * @return {Observable<TodoList>}
     */
    getTodoList(hash: string): Observable<TodoList> {
        return this.http.get<{data: TodoList}>(`${this.todoListsUrl}/${hash}`)
            .pipe(
                map(
                    (response) => response.data
                )
            );
    }

    /**
     * Delete a TodoList.
     *
     * @param {string} hash
     * @return {Observable<void>}
     */
    deleteTodoList(hash: string): Observable<void> {
        return this.http.delete<void>(`${this.todoListsUrl}/${hash}`, this.httpOptions);
    }

    /**
     * Invite new participant to TodoList.
     *
     * @param {string} hash
     * @param {{participant: string}} participant
     * @return {Observable<void>}
     */
    invite(hash: string, participant: {participant: string}): Observable<void> {
        return this.http.post<void>(`${this.todoListsUrl}/${hash}/invite`, participant, this.httpOptions);
    }

    /**
     * Navigate to home page.
     *
     */
    goHome(): void {
        this.route.navigateByUrl('/home');
    }

    /**
     * Set the authorization token.
     *
     * @param {string} token
     */
    setAuthorizationToken(token: string) {
        this.authorizationToken = token;
    }

    /**
     * Build HttpHeaders to be sent in requests.
     *
     * @return {HttpHeaders}
     */
    protected buildHttpRequestHeaders(): HttpHeaders {

        let headers = {
            'Content-Type': 'application/json'
        };

        if (this.authorizationToken) {
            headers['Authorization'] = this.authorizationToken;
        }

        return new HttpHeaders(headers);

    }

    /**
     * Getter for HttpOptions to be sent in requests.
     *
     * @return {any}
     */
    protected get httpOptions(): {headers: any} {
        return {
            headers: this.buildHttpRequestHeaders()
        }
    }
}
