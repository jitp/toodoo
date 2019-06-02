<?php

namespace App\Services\TodoList;

use App\Models\TodoList;
use App\Services\Service;
use App\Services\UserService;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Exception;

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
//        $creatorData = Arr::pull($data, 'creator', []);
//        $participantsData = Arr::pull($data, 'participants', []);
//
//        $creator = null;
//        $participants = [];
//
//        DB::beginTransaction();
//
//        try {
//            $todolist = $this->todoListQueryBuilder()->create($data);
//
//            if ($creatorData) {
//                $creator = $this->userService->firstOrCreate($creatorData);
//            }
//
//            if ($participantsData) {
//                collect($participantsData)
//                    ->unique('email')
//                    ->reject(function($item) {
//
//                    })
//                ;
//            }
//
//
//
//        } catch (Exception $exception) {
//            DB::rollBack();
//
//            throw $exception;
//        }
    }

    /**
     * Invite new group of participants to collaborate on the todolist.
     *
     * @param TodoList          $todolist
     * @param array|string|User $participants an array or single instance of: array of valid user model attributes
     * (email included), emails, Users instances
     * @throws Exception
     */
    public function invite($todolist, $participants)
    {
        //Pulling out email info
        $emails = collect(Arr::wrap($participants))->transform(function($item) {
            return data_get($item, 'email', $item);
        });

        $emailsInTodoList = $todolist->participants->pluck('email');

        //Excluding emails of users already invited
        $emails->diff($emailsInTodoList);

        $participantInstances = [];

        DB::beginTransaction();

        try {
            foreach ($emails as $email) {
                $participant = $this->userService->firstOrCreate([
                    'email' => $email
                ]);

                $participantInstances[] = $participant;
            }

            $todolist->addParticipants($participantInstances);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
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
}