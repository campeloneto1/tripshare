<?php

namespace App\Http\Requests;

class StorePostRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:2000'],
            'trip_id' => ['nullable', 'exists:trips,id'],
            'shared_post_id' => ['nullable', 'exists:posts,id'],

            // Uploads (imagens/vídeos)
            'uploads' => ['nullable', 'array', 'max:10'],
            'uploads.*' => ['file', 'mimes:jpg,jpeg,png,gif,mp4,mov', 'max:10240'], // 10MB
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    public function messages(): array
    {
        return [
            'content.max' => 'O conteúdo do post não pode ter mais de 2000 caracteres.',
            'trip_id.exists' => 'A viagem selecionada é inválida.',
            'shared_post_id.exists' => 'O post compartilhado é inválido.',
            'uploads.max' => 'Você pode enviar no máximo 10 arquivos por post.',
            'uploads.*.file' => 'Cada upload deve ser um arquivo válido.',
            'uploads.*.mimes' => 'Os arquivos devem ser imagens (JPG, PNG, GIF) ou vídeos (MP4, MOV).',
            'uploads.*.max' => 'Cada arquivo não pode ser maior que 10MB.',
        ];
    }
}
