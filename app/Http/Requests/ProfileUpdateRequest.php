<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'ktp_number' => [
                'nullable',
                'string',
                'regex:/^[0-9]{16}$/',
                Rule::unique('tenants', 'ktp_number')->ignore($this->user()->tenant?->id),
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ktp_number.regex'  => 'Nomor KTP / NIK harus terdiri dari tepat 16 digit angka.',
            'ktp_number.unique' => 'Nomor KTP / NIK ini sudah terdaftar pada akun lain.',
            'phone.unique'      => 'Nomor telepon ini sudah digunakan oleh akun lain.',
        ];
    }
}
