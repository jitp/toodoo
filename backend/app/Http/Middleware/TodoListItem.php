<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Class TodoListItem
 *
 * Check item and TodoList are related.
 *
 * @package App\Http\Middleware
 */
class TodoListItem
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $todoList = $request->route('todolist');
        $todoListItem = $request->route('item');

        if (!$todoList->hasItem($todoListItem)) {
            return abort(404);
        }

        return $next($request);
    }
}
