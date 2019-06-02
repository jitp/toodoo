<?php

namespace App\Mail;

use App\Models\TodoList;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TodoListInvitation extends Mailable
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
    public $inviting;

    /**
     * TodoListInvitation constructor.
     *
     * @param TodoList $todoList
     * @param User     $participant
     * @param User     $inviting
     */
    public function __construct($todoList, $participant, $inviting)
    {
        $this->todoList = $todoList;
        $this->participant = $participant;
        $this->inviting = $inviting;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //Creator has a different invitation template
        if ($this->todoList->isCreator($this->participant)) {
            return $this->markdown('emails.todolist.creator-invitation');
        }

        return $this->markdown('emails.todolist.invitation');
    }
}
