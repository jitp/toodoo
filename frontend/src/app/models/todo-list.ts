import {Participant} from './participant';

/**
 * Represent TodoList model
 */
export class TodoList {
    id = 0;
    name = '';
    creator: Participant;
    participants: Participant[]
}
