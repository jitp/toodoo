<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Class TodoListItemStatusEnum
 *
 * Enumerates the possible statuses a TodoList item may have.
 *
 * @package App\Enums
 */
final class TodoListItemStatusEnum extends Enum
{
    const DONE = 'done';
    const PENDING = 'pending';
    const EXPIRED = 'expired';
}
