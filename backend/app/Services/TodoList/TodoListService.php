<?php

namespace App\Services\TodoList;

use App\Mail\TodoListInvitation;
use App\Mail\TodoListRemovalNotification;
use App\Models\TodoList;
use App\Services\Service;
use App\Services\UserService;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
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
     * Create a TodoList
     *
     * @param array $data
     * @return TodoList
     */
    public function create($data)
    {
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
     * @return mixed
     * @throws Exception
     */
    protected function addParticipantsToList($todoList, $participants)
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

        //Getting todolist existing participants
        $todoListParticipantsEmails = $todoList->participants->pluck('email');

        //Exclude users already participating in todolist
        $participants->whereNotIn('email', $todoListParticipantsEmails);

        $participantInstances = [];

        DB::beginTransaction();

        try {
            //Create users if not existing in system
            foreach ($participants as $participant) {
                $participantInstance = $this->userService->firstOrCreate($participant);

                $participantInstances[] = $participantInstance;
            }

            $todoList->addParticipants($participantInstances);

            //Refresh list participants so new ones are loaded
            $todoList->load('participants');

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        //Return new added participants
        return $todoList->participants->whereNotIn('email', $todoListParticipantsEmails)->all();
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