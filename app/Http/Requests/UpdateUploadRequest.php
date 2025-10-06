<?php

namespace App\Http\Requests;

class UpdateUploadRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:image,video,document'],
            'is_main' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'original_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'O tipo deve ser image, video ou document.',
            'order.min' => 'A ordem deve ser maior ou igual a 0.',
        ];
    }
}
