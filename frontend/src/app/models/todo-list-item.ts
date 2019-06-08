import {TodoList} from './todo-list';

/**
 * Represent a TodoListItem model
 */
export class TodoListItem {
    id = 0;
    name = '';
    order = 0;
    status = '';
    deadline = '';
    todo_list: TodoList;
}
