<?php

namespace App\Models;

use App\Enums\ParticipantRolesEnum;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

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
                'hash' => hash_hmac('sha256', $this->name . $participant->email . time(), $this->name),
                'role' => $role
            ];
        }

        return $this->participants()->syncWithoutDetaching($prepareData);
    }

    /**
     * Add new Items to the list.
     *
     * @param array $items a single array or an array of arrays of valid TodoListItems attributes => values
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function addItems($items)
    {
        $items = Arr::wrap($items);

        return $this->items()->createMany($items);
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
     * Determine if given participant belongs to this todolist.
     *
     * @param User $participant
     * @return bool
     */
    public function isParticipant($participant)
    {
        return $this->participants
                ->firstWhere('id', data_get($participant, 'id')) != null;
    }

    /**
     * Determine if given set of ids is the whole TodoList items ids.
     *
     * @param array $items ids
     * @return bool
     */
    public function isWholeSetOfItemIds($items)
    {
        $givenItemsCount = $this->items->whereIn('id', $items)->count();
        $realItemsCount = $this->items->count();

        return ($givenItemsCount === $realItemsCount) && (count($items) === $realItemsCount);
    }

    /**
     * Get participant by hash.
     *
     * @param string $hash
     * @return mixed
     */
    public function getParticipantByHash($hash)
    {
        return $this->participants()
            ->where('hash', $hash)
            ->first();
    }

    /**
     * Determine if list has given item.
     *
     * @param TodoListItem|integer $item
     * @return bool
     */
    public function hasItem($item)
    {
        $id = data_get($item, 'id', $item);

        return $this->items->firstWhere('id', $id) !== null;
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

    /**
     * Relation to TodoListItems.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(
            TodoListItem::class,
            'todo_list_id',
            'id'
        )
            ->ordered();
    }

    /**
     * =================================================================================================================
     *
     * SCOPES
     *
     * =================================================================================================================
     */

    /**
     * Scope to limit models by hash.
     *
     * @param Builder $query
     * @param string  $hash
     */
    public function scopeHasHash($query, $hash)
    {
        $query->whereHas('participants', function (Builder $builder) use ($hash) {
            $builder->where('hash', $hash);
        });
    }
}
