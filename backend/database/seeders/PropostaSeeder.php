<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proposta;

class PropostaSeeder extends Seeder
{
    public function run()
    {
        // 10 propostas com status variados
        $statusLista = [
            'rascunho',
            'em_analise',
            'em_analise',
            'aprovada',
            'reprovada',
            'cancelada',
            'rascunho',
            'aprovada',
            'reprovada',
            'cancelada',
        ];

        foreach ($statusLista as $status) {
            Proposta::factory()->create([
                'status' => $status
            ]);
        }
    }
}
