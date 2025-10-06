<?php

namespace App\Http\Requests;

class StoreUploadRequest extends BaseRequest
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
            'file' => ['required', 'file', 'max:10240'], // 10MB
            'files' => ['sometimes', 'array'],
            'files.*' => ['file', 'max:10240'],
            'uploadable_type' => ['required', 'string'],
            'uploadable_id' => ['required', 'integer'],
            'type' => ['sometimes', 'string', 'in:image,video,document'],
            'is_main' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'O arquivo é obrigatório.',
            'file.file' => 'O arquivo enviado é inválido.',
            'file.max' => 'O arquivo não pode ser maior que 10MB.',
            'files.*.max' => 'Cada arquivo não pode ser maior que 10MB.',
            'uploadable_type.required' => 'O tipo do relacionamento é obrigatório.',
            'uploadable_id.required' => 'O ID do relacionamento é obrigatório.',
        ];
    }
}
