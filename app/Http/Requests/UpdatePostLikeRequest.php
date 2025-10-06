<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostLikeRequest extends FormRequest
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
