<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Aqui você pode limitar quem pode criar posts, se quiser
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:2000'],
            'trip_id' => ['nullable', 'exists:trips,id'],
            'shared_post_id' => ['nullable', 'exists:posts,id'],
            'uploads' => ['nullable', 'array'],
            'uploads.*' => ['integer', 'exists:uploads,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.max' => 'O conteúdo do post não pode ter mais de 2000 caracteres.',
            'trip_id.exists' => 'A viagem selecionada é inválida.',
            'shared_post_id.exists' => 'O post compartilhado é inválido.',
            'uploads.array' => 'Os uploads devem ser enviados em formato de lista.',
            'uploads.*.exists' => 'Um dos uploads enviados não existe.',
        ];
    }
}
