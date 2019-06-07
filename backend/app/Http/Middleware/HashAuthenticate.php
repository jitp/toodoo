<?php

namespace App\Http\Middleware;

use App\Exceptions\TodoListException;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class HashAuthenticate
 *
 * Attempt to authenticate a user by TodoList Hash.
 *
 * Each TodoList participant is uniquely identified by a hash given when joined to the list.
 *
 * @package App\Http\Middleware
 */
class HashAuthenticate
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
        $route = $request->route();

        if (!Auth::check()) {
            $hash = $route->originalParameter('todolist');
            $todoList = $route->parameter('todolist');

            if ($participant = $todoList->getParticipantByHash($hash)) {
                $token = Auth::login($participant);
            } else {
                throw new TodoListException(404, 'Participant not found');
            }

        } else {
            $token = Auth::tokenById(Auth::user()->getAuthIdentifier());
        }

        $response = $next($request);

        //Set authorization token in response headers for later use in requests
        $response->headers->set('Authorization', 'Bearer ' . $token);

        return $response;
    }
}
