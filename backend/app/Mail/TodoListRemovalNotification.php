<?php

namespace App\Mail;

use App\Models\TodoList;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class TodoListRemovalNotification
 *
 * Mail model for a todolist removal notification.
 *
 * @package App\Mail
 */
class TodoListRemovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var TodoList
     */
    public $todoList;

    /**
     * @var User
     */
    public $participant;

    /**
     * @var User
     */
    public $deleter;

    /**
     * TodoListRemovalNotification constructor.
     *
     * @param TodoList $todoList
     * @param User     $participant
     * @param User     $deleter
     */
    public function __construct($todoList, $participant, $deleter)
    {
        $this->todoList = $todoList;
        $this->participant = $participant;
        $this->deleter = $deleter;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.todolist.removal');
    }
}
