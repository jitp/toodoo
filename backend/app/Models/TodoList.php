<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'user_id'
    ];

    protected $casts = [
        'name' => 'string',
        'user_id' => 'integer'
    ];

    /**
     * =================================================================================================================
     *
     * RELATIONS
     *
     * =================================================================================================================
     */

    /**
     * Relation to User as Creator.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
}
