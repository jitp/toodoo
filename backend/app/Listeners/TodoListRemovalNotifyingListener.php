<?php

namespace App\Listeners;

use App\Models\TodoList;
use App\Services\TodoList\TodoListService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class TodoListRemovalNotifyingListener
{
    /**
     * @var TodoListService
     */
    protected $todoListService;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected $authUser;

    /**
     * TodoListRemovalNotifyingListener constructor.
     *
     * @param TodoListService $todoListService
     */
    public function __construct(TodoListService $todoListService)
    {
        $this->todoListService = $todoListService;
        $this->authUser = Auth::user();
    }

    /**
     * Handle the event.
     *
     * @param  TodoList $todoList
     * @return void
     */
    public function handle($todoList)
    {
        $this->todoListService->notifyTodoListRemoval($todoList, $this->authUser);
    }
}
