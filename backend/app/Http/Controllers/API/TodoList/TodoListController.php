<?php

namespace App\Http\Controllers\API\TodoList;

use App\Http\Controllers\ApiController;
use App\Http\Requests\TodoList\CreateTodoListFormRequest;
use App\Http\Resources\TodoListResource;
use App\Models\TodoList;
use App\Services\TodoList\TodoListService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class TodoListController
 *
 * Handle user actions on TodoLists
 *
 * @package App\Http\Controllers\API
 */
class TodoListController extends ApiController
{
    /**
     * @var TodoListService
     */
    protected $todoListService;

    /**
     * TodoListController constructor.
     *
     * @param TodoListService $todoListService
     */
    public function __construct(TodoListService $todoListService)
    {
        $this->todoListService = $todoListService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTodoListFormRequest $request
     * @return TodoListResource
     * @throws \Exception
     */
    public function store(CreateTodoListFormRequest $request)
    {
        $inputValidated = $request->validated();

        return (new TodoListResource($this->todoListService->create($inputValidated)));
    }

    /**
     * Display the specified resource.
     *
     * @param TodoList $todoList
     * @return TodoListResource
     */
    public function show(TodoList $todoList)
    {
        return (new TodoListResource($todoList));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TodoList $todoList
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(TodoList $todoList)
    {
        $this->todoListService->delete($todoList);

        return response()->json();
    }
}
