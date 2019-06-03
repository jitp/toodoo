<?php

namespace App\Http\Requests\TodoList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateTodoListFormRequest
 *
 * Handle validation when creating a new TodoList.
 *
 * @package App\Http\Requests\TodoList
 */
class CreateTodoListFormRequest extends FormRequest
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
                'required',
                'regex:/[a-z 0-9!?\'-_.]/',
                'max: 150'
            ],
            'creator.email' => [
                'required',
                'email'
            ],
            'participants.*.email' => [
                'nullable',
                'email'
            ]
        ];
    }
}
