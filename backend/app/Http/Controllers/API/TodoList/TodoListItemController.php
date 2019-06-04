<?php

namespace App\Http\Controllers\API\TodoList;

use App\Http\Controllers\ApiController;
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

        $this->middleware('auth:api')->only('store');
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

        return (new TodoListItemResource($this->todoListService->addItemToList($todoList, $input)->first()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
