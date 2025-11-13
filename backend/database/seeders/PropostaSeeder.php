<?php

namespace Database\Seeders;

use App\Models\Proposta;
use Illuminate\Database\Seeder;

class PropostaSeeder extends Seeder
{
    public function run(): void
    {
        $cpfs = [
            "096.077.107-70",
            "390.533.447-05",
            "762.144.387-20",
            "121.317.447-30",
            "703.802.917-10",
            "144.267.227-21",
            "337.869.267-00",
            "540.972.817-02",
            "817.055.597-04",
            "221.993.157-80",
        ];

        $status = [
            "rascunho", "em_analise", "aprovada", "reprovada", "cancelada"
        ];

        for ($i = 0; $i < 10; $i++) {

            Proposta::create([
                'nome_cliente'        => "Cliente Teste " . ($i+1),
                'cpf'                 => $cpfs[$i],
                'valor_solicitado'    => rand(1000, 50000),
                'quantidade_parcelas' => rand(6, 60),
                'salario'             => rand(1500, 8000),
                'taxa_juros'          => 0.025,
                'valor_parcela'       => rand(150, 900),
                'valor_total'         => rand(1000, 80000),
                'margem_disponivel'   => rand(400, 3000),
                'status'              => $status[array_rand($status)],
                'observacoes'         => null,
            ]);

        }
    }
}
