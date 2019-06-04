<?php

namespace App\Http\Requests\TodoList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class InviteFormRequest
 *
 * Validate user input when inviting another user to collaborate.
 *
 * @package App\Http\Requests\TodoList
 */
class InviteFormRequest extends FormRequest
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
            'participant' => [
                'required',
                'email'
            ]
        ];
    }
}
