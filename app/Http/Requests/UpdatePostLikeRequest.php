<?php

namespace App\Http\Requests;

class UpdatePostLikeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Likes não são editáveis, apenas criados e deletados.
     */
    public function rules(): array
    {
        return [];
    }
}
