<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class TodoListException
 *
 * Custom exception for todolists.
 *
 * This type of exceptions are intended to be thrown in  business logic whenever
 * something goes wrong related to a todolist.
 *
 * @package App\Exceptions
 */
class TodoListException extends HttpException
{
    //
}
