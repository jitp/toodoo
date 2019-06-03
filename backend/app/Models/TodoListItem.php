<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Class TodoListItem
 *
 * Represents a TodoList item.
 *
 * @package App\Models
 */
class TodoListItem extends Model implements Sortable
{
    use SoftDeletes;
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $dates = [
        'deleted_at',
        'deadline'
    ];

    protected $fillable = [
        'name',
        'todo_list_id',
        'user_id',
        'order',
        'status',
        'deadline',
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'todo_list_id' => 'integer',
        'user_id' => 'integer',
        'order' => 'integer',
        'status' => 'string',
    ];

    protected $table = 'todo_list_items';


    /**
     * Restrict order to items of the same todolist.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildSortQuery()
    {
        return static::query()->where('todo_list_id', $this->todo_list_id);
    }

    /**
     * =================================================================================================================
     *
     * RELATIONS
     *
     * =================================================================================================================
     */

    /**
     * Relation to TodoList.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function todoList()
    {
        return $this->belongsTo(
            TodoList::class,
            'todo_list_id',
            'id'
        );
    }

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
