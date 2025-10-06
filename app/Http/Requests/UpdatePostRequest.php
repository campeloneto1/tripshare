<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:1000'],
            'trip_id' => ['nullable', 'exists:trips,id'],
            'shared_post_id' => ['nullable', 'exists:posts,id'],

            // uploads: novos arquivos opcionais
            'uploads' => ['nullable', 'array'],
            'uploads.*.file' => ['required', 'file', 'mimes:jpg,jpeg,png,mp4', 'max:5120'], // até 5MB
            'uploads.*.type' => ['required', 'in:image,video'],
            'uploads.*.order' => ['nullable', 'integer', 'min:0'],

            // uploads_removidos: IDs dos uploads que devem ser apagados
            'uploads_removed' => ['nullable', 'array'],
            'uploads_removed.*' => ['integer', 'exists:uploads,id'],
        ];
    }

    public function messages(): array
    {
        return [
            // content
            'content.string' => 'O conteúdo do post deve ser um texto válido.',
            'content.max' => 'O conteúdo do post pode ter no máximo :max caracteres.',

            // trip_id
            'trip_id.exists' => 'A viagem selecionada não existe.',

            // shared_post_id
            'shared_post_id.exists' => 'O post que você está tentando compartilhar não existe.',

            // uploads
            'uploads.array' => 'Os uploads devem ser enviados em formato de lista.',
            'uploads.*.file.required' => 'Cada upload deve conter um arquivo.',
            'uploads.*.file.file' => 'O upload deve ser um arquivo válido.',
            'uploads.*.file.mimes' => 'O arquivo deve ser uma imagem (JPG/PNG) ou vídeo (MP4).',
            'uploads.*.file.max' => 'Cada arquivo pode ter no máximo :max kilobytes (5MB).',
            'uploads.*.type.required' => 'Informe o tipo do arquivo (image ou video).',
            'uploads.*.type.in' => 'O tipo do arquivo deve ser "image" ou "video".',
            'uploads.*.order.integer' => 'A ordem deve ser um número inteiro.',
            'uploads.*.order.min' => 'A ordem não pode ser negativa.',

            // uploads removidos
            'uploads_removed.array' => 'A lista de uploads removidos deve ser um array.',
            'uploads_removed.*.integer' => 'Os IDs dos uploads devem ser números inteiros.',
            'uploads_removed.*.exists' => 'Um dos uploads informados para remoção não existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'content' => 'conteúdo do post',
            'trip_id' => 'viagem',
            'shared_post_id' => 'post compartilhado',
            'uploads' => 'uploads',
            'uploads.*.file' => 'arquivo',
            'uploads.*.type' => 'tipo do arquivo',
            'uploads.*.order' => 'ordem',
            'uploads_removed' => 'uploads removidos',
        ];
    }
}
