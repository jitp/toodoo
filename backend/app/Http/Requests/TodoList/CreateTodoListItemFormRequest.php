<?php

namespace App\Http\Requests\TodoList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateTodoListItemFormRequest
 *
 * Validates user input when creating a TodoListItem.
 *
 * @package App\Http\Requests\TodoList
 */
class CreateTodoListItemFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'regex:/[a-z 0-9!?\'-_.]/',
                'max: 150'
            ]
        ];
    }
}
