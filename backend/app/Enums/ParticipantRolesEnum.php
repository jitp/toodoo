<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Class ParticipantRolesEnum
 *
 * Enumerates roles a User can have when participating in a todolist.
 *
 * @package App\Enums
 */
final class ParticipantRolesEnum extends Enum
{
    const CREATOR = 'creator';
    const PARTICIPANT = 'participant';
}
