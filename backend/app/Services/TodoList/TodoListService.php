<?php

namespace App\Services\TodoList;

use App\Enums\ParticipantRolesEnum;
use App\Enums\TodoListItemStatusEnum;
use App\Exceptions\TodoListException;
use App\Mail\TodoListInvitation;
use App\Mail\TodoListRemovalNotification;
use App\Models\TodoList;
use App\Models\TodoListItem;
use App\Services\Service;
use App\Services\UserService;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Mail;

/**
 * Class TodoListService
 *
 * Provide services for TodoLists
 *
 * @package App\Services\TodoList
 */
class TodoListService extends Service
{
    /**
     * @var UserService
     */
    protected $userService = null;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create a todolist.
     *
     * @param array $data can have index 'creator' with a list of emails or array of valid user attributes and another
     *                    index 'participants' with a list of emails or arrays of valid user attributes.
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws Exception
     */
    public function create($data)
    {
        $createdTodoList = null;
        $creator = Arr::pull($data, 'creator', []);
        $participants = Arr::pull($data, 'participants', []);

        DB::beginTransaction();

        try {

            $createdTodoList = $this->todoListQueryBuilder()->create($data);

            if ($creator) {
                if (is_array($creator) && count($creator) > 1) {
                    throw new TodoListException(422, 'Only one creator is admitted');
                }

                $this->addParticipantsToList($createdTodoList, $creator, ParticipantRolesEnum::CREATOR);
            }

            if ($participants) {
                $this->addParticipantsToList($createdTodoList, $participants, ParticipantRolesEnum::PARTICIPANT);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        //Notify users of the creation
        if ($creator = $createdTodoList->creator) {
            $participants = $createdTodoList->participants->all();

            $this->mailInvitationToCollaborate($createdTodoList, $participants, $creator);
        }

        return $createdTodoList;
    }

    /**
     * Invite new group of participants to collaborate on the todolist.
     *
     * @param TodoList           $todolist
     * @param array|integer|User $participants array or single value of: user ids, user attributes and values or emails
     * @param User               $inviting
     * @return mixed
     * @throws Exception
     */
    public function invite($todolist, $participants, $inviting)
    {
        $participants = $this->addParticipantsToList($todolist, $participants);

        $this->mailInvitationToCollaborate($todolist, $participants, $inviting);

        return $participants;
    }

    /**
     * Delete a todolist
     *
     * @param TodoList|int $todoList
     * @return mixed
     * @throws Exception
     */
    public function delete($todoList)
    {
        if (is_int($todoList)) {
            $todoList = $this->findOrFail($todoList);
        }

        return $todoList->delete();
    }

    /**
     * Notify about a todolist removal to participants.
     *
     * @param TodoList $todoList
     * @param User     $deleter
     */
    public function notifyTodoListRemoval($todoList, $deleter)
    {
        //Exclude deleter from notifying
        $participants = $todoList->participants
            ->whereNotIn('email', [data_get($deleter, 'email')]);

        $this->mailRemovalNotification($todoList, $participants->all(), $deleter);
    }

    /**
     * Add a new TodoListItem to the list.
     *
     * @param TodoList    $todoList
     * @param array       $data
     * @param User|null   $participant
     * @throws TodoListException
     * @return mixed
     */
    public function addItemToList($todoList, $data, $participant = null)
    {
        Arr::set($data, 'status', TodoListItemStatusEnum::PENDING);
        Arr::set($data, 'deadline', Carbon::today()->addMonth());

        if (is_null($participant)) {
            $participant = Auth::user();
        }

        if ($todoList->isParticipant($participant) === false) {
            throw new TodoListException(
                422, sprintf('User %s is not a participant of %s', $participant->name, $todoList->name));
        }

        Arr::set($data, 'user_id', $participant->id);

        return $todoList->addItems([
            $data
        ]);
    }

    /**
     * Toggle TodoListItem status as done/pending
     *
     * @param TodoListItem $todoItemList
     * @return TodoListItem
     */
    public function toggleTodoItemListStatus($todoItemList)
    {
        if ($todoItemList->isExpired()) {
            throw new TodoListException(422, sprintf('%s has expired', $todoItemList->name));
        }

        switch ($todoItemList->status) {
            case TodoListItemStatusEnum::PENDING:
                $todoItemList->markDone();
                break;

            default:
                $todoItemList->markPending();
        }

        return $todoItemList;
    }

    /**
     * Delete a TodoListItem
     *
     * @param TodoListItem $todoListItem
     * @return bool|null
     * @throws Exception
     */
    public function deleteTodoListItem($todoListItem)
    {
        return $todoListItem->delete();
    }

    /**
     * Change TodoList's items order.
     *
     * @param TodoList  $todoList
     * @param array     $items TodoListItems
     */
    public function changeTodoListItemsOrder($todoList, $items)
    {
        if (!$todoList->isWholeSetOfItemIds($items)) {
            throw new TodoListException(422, 'Wrong list\'s items.');
        }

        TodoListItem::setNewOrder($items);
    }

    /**
     * Send invitation email to collaborate on a todolist.
     *
     * @param TodoList   $todoList
     * @param array|User $participants
     * @param User       $inviting
     */
    protected function mailInvitationToCollaborate($todoList, $participants, $inviting)
    {
        $participants = collect(Arr::wrap($participants));

        foreach ($participants as $participant) {
            Mail::to($participant)->send(app(TodoListInvitation::class, compact(
                'todoList',
                'participant',
                'inviting')));
        }
    }

    /**
     * Send a removal notification.
     *
     * @param TodoList   $todoList
     * @param array|User $participants
     * @param User       $deleter
     */
    protected function mailRemovalNotification($todoList, $participants, $deleter)
    {
        $participants = collect(Arr::wrap($participants));

        foreach ($participants as $participant) {
            Mail::to($participant)->send(app(TodoListRemovalNotification::class, compact(
                'todoList',
                'participant',
                'deleter')));
        }
    }

    /**
     * Add participants to todolist.
     *
     * Participants already added are not taken into consideration.
     *
     * @param TodoList           $todoList
     * @param array|integer|User $participants array or single value of: user ids, user attributes and values or emails
     * @param string             $role
     * @return mixed
     * @throws Exception
     */
    protected function addParticipantsToList($todoList, $participants, $role = ParticipantRolesEnum::PARTICIPANT)
    {
        $participants = collect(Arr::wrap($participants))->transform(function($item) {
            //Possible user id
            if (is_int($item)) {
                return [
                    'id' => $item
                ];
            }

            //Possible user email
            if (is_string($item)) {
                return [
                    'email' => $item
                ];
            }

            if ($item instanceof User) {
                return $item->toArray();
            }

            return $item;
        });

        $participantInstances = [];

        DB::beginTransaction();

        try {
            //Create users if not existing in system
            foreach ($participants as $participant) {
                $participantInstance = $this->userService->firstOrCreate($participant);

                $participantInstances[] = $participantInstance;
            }

            $participantInstances = collect($participantInstances);

            //Avoid duplicates
            $participantInstances = $participantInstances->unique('email');

            //Exclude users already participating in todolist
            $participantInstances = $participantInstances->whereNotIn('id', $todoList->participants->pluck('id')->all());

            if ($participantInstances->isNotEmpty()) {
                $todoList->addParticipants($participantInstances->all(), $role);

                //Refresh list participants so new ones are loaded
                $todoList->load('participants');
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        //Return new added participants
        return $todoList->participants->whereIn('email', $participantInstances->pluck('email'))->all();
    }

    /**
     * Get the TodoList model query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function todoListQueryBuilder()
    {
        return TodoList::query();
    }

    /**
     * Find a todolist or fail with an exception.
     *
     * @param integer $todoList
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|static|static[]
     * @throws ModelNotFoundException
     */
    protected function findOrFail($todoList)
    {
        return $this->todoListQueryBuilder()->findOrFail($todoList);
    }
}