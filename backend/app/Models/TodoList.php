<?php

namespace App\Models;

use App\Enums\ParticipantRolesEnum;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

/**
 * Class TodoList
 *
 * Represent a TodoList on the system.
 *
 * @package App\Models
 */
class TodoList extends Model
{
    use SoftDeletes;

    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string',
    ];

    protected $table = 'todo_lists';

    /**
     * Add participants to the todolist.
     *
     * @param array|User $participants array of Users or a single User instance
     * @param string     $role
     * @return array
     */
    public function addParticipants($participants, $role = ParticipantRolesEnum::PARTICIPANT)
    {
        $participants = collect(Arr::wrap($participants));

        $prepareData = [];

        foreach ($participants as $participant) {
            $prepareData[$participant['id']] = [
                'hash' => Hash::make($this->name . $participant->email . time()),
                'role' => $role
            ];
        }

        return $this->participants()->syncWithoutDetaching($prepareData);
    }

    /**
     * Determine if given participant is the creator of the todolist.
     *
     * @param int|User $participant
     * @return bool
     */
    public function isCreator($participant)
    {
        $id = data_get($participant, 'id', $participant);

        return data_get($this, 'creator.id') == $id;
    }

    /**
     * =================================================================================================================
     *
     * ACCESSORS & MUTATORS
     *
     * =================================================================================================================
     */

    /**
     * Get creator of this TodoList.
     *
     * @return User|null
     */
    public function getCreatorAttribute()
    {
        return $this->creators()->first();
    }

    /**
     * =================================================================================================================
     *
     * RELATIONS
     *
     * =================================================================================================================
     */

    /**
     * Relation to Users as Creators.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function creators()
    {
        return $this->participants()
            ->wherePivot('role', '=', ParticipantRolesEnum::CREATOR)
            ;
    }

    /**
     * Relation to Users as Participants.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants()
    {
        return $this->belongsToMany(
            User::class,
            'participants',
            'todo_list_id',
            'user_id'
        )
            ->withPivot([
                'hash',
                'role'
            ])
            ->as('participant')
            ->withTimestamps()
            ;
    }
}
