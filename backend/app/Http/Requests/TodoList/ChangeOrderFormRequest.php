<?php

namespace App\Http\Requests\TodoList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ChangeOrderFormRequest
 *
 * Validates user input when changing TodoListItems order.
 *
 * @package App\Http\Requests\TodoList
 */
class ChangeOrderFormRequest extends FormRequest
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
            'order' => [
                'required',
                'array'
            ],
            'order.*' => [
                'integer'
            ]
        ];
    }
}
