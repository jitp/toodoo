<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TodoListItemResource
 *
 * Represents a resource from a TodoListItem.
 *
 * @package App\Http\Resources
 */
class TodoListItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'todo_list_id' => $this->todo_list_id,
            'todo_list' => (new TodoListResource($this->whenLoaded('todoList'))),
            'creator' => (new UserResource($this->whenLoaded('creator'))),
            'order' => $this->order,
            'status' => $this->status,
            'deadline' => (string) $this->deadline,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'deleted_at' => (string) $this->deleted_at,
        ];
    }
}
