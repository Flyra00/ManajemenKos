<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Room;

class RoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //

            'room_number' =>[
                'required',
                'unique:room,room_number'
            ],

            'floor' => [
                'nullable',
                'integer'
            ],

            'price'=> [
                'required',
                'numeric'
            ],

            'status' =>[
                'required',
                'in:available,occupied,maintenance'
            ],

            'is_active' =>[
                'boolean'
            ],

            'description'=> [
                'nullable'
            ]
        ];
    }
}
