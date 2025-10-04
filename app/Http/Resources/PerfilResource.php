<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerfilResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'permissoes' => $this->permissoes->pluck('nome'), // lista só os nomes das permissões
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
