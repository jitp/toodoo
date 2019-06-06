import {Participant} from './participant';
import {TodoListItem} from './todo-list-item';

/**
 * Represent TodoList model
 */
export class TodoList {
    id = 0;
    name = '';
    creator: Participant;
    participants: Participant[];
    items: TodoListItem[];
}
