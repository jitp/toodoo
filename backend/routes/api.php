<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * =====================================================================================================================
 *
 * TodoList's Routes
 *
 * =====================================================================================================================
 */

// Toggle TodoListItem status
Route::put('todolist/{todolist}/items/{item}/toggle-status', 'API\\TodoList\\TodoListItemController@toggleStatus')
    ->name('items.toggle-status')
;

// Change TodoListItem deadline
Route::put('todolist/{todolist}/items/{item}/change-deadline', 'API\\TodoList\\TodoListItemController@changeDeadline')
    ->name('items.change-deadline')
;

Route::apiResource('todolist/{todolist}/items', 'API\\TodoList\\TodoListItemController')
    ->except('index', 'update');

//Invite new users to participate
Route::post('todolist/{todolist}/invite', 'API\\TodoList\\TodoListController@invite')
    ->name('todolist.invite');

Route::apiResource('todolist', 'API\\TodoList\\TodoListController')
    ->except('index', 'update');