<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PropostaResource extends JsonResource
{
    public function toArray($request):array
    {
        return [
            'id' => $this->id,
            'nome_cliente' => $this->nome_cliente,
            'cpf' => $this->cpf,
            'valor_solicitado' => $this->valor_solicitado,
            'quantidade_parcelas' => $this->quantidade_parcelas,
            'valor_parcela' => $this->valor_parcela,
            'valor_total' => $this->valor_total,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}