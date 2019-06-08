<?php

namespace App\Http\Controllers\API\TodoList;

use App\Http\Controllers\ApiController;
use App\Http\Requests\TodoList\ChangeDeadlineFormRequest;
use App\Models\TodoListItem;
use App\Http\Requests\TodoList\CreateTodoListItemFormRequest;
use App\Http\Resources\TodoListItemResource;
use App\Models\TodoList;
use App\Services\TodoList\TodoListService;

class TodoListItemController extends ApiController
{
    /**
     * @var TodoListService
     */
    protected $todoListService;

    /**
     * TodoListItemController constructor.
     *
     * @param TodoListService $todoListService
     */
    public function __construct(TodoListService $todoListService)
    {
        $this->todoListService = $todoListService;

        $this->middleware('auth:api');

        $this->middleware('todolist.item')
            ->only('destroy', 'toggleStatus', 'changeDeadline');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTodoListItemFormRequest $request
     * @param TodoList                      $todoList
     * @return TodoListItemResource
     */
    public function store(CreateTodoListItemFormRequest $request, TodoList $todoList)
    {
        $input = $request->validated();

        $todoListItem = $this->todoListService->addItemToList($todoList, $input)->first();

        return (new TodoListItemResource($todoListItem->load(
            'todoList.items', 'todoList.participants')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TodoList     $todoList
     * @param TodoListItem $todoListItem
     * @return TodoListItemResource
     * @throws \Exception
     */
    public function destroy(TodoList $todoList, TodoListItem $todoListItem)
    {
        $this->todoListService->deleteTodoListItem($todoListItem);

        return (new TodoListItemResource($todoListItem->load(
            'todoList.items', 'todoList.participants')));
    }

    /**
     * Toggle status (pending/done) of a TodoListItem
     *
     * @param TodoList     $todoList
     * @param TodoListItem $todoListItem
     * @return TodoListItemResource
     */
    public function toggleStatus(TodoList $todoList, TodoListItem $todoListItem)
    {
        $todoListItem = $this->todoListService->toggleTodoItemListStatus($todoListItem);
        //Load relation with TodoList
        $todoListItem->load('todoList.items', 'todoList.participants');

        return (new TodoListItemResource($todoListItem));
    }

    /**
     * Change deadline of a TodoListItem
     *
     * @param ChangeDeadlineFormRequest $request
     * @param TodoList                  $todoList
     * @param TodoListItem              $todoListItem
     * @return TodoListItemResource
     */
    public function changeDeadline(ChangeDeadlineFormRequest $request, TodoList $todoList, TodoListItem $todoListItem)
    {
        $input = $request->validated();

        $todoListItem = $this->todoListService->changeTodoListItemDeadline($todoListItem, $input['deadline'] ?? null);
        //Load relation with TodoList
        $todoListItem->load('todoList.items', 'todoList.participants');

        return (new TodoListItemResource($todoListItem));
    }
}
