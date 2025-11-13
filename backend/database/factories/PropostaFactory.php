<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PropostaFactory extends Factory
{
    public function definition()
    {
        $salario = fake()->numberBetween(1500, 8000);
        $valorSolicitado = fake()->numberBetween(1000, 50000);
        $parcelas = fake()->numberBetween(6, 60);
        $taxa = 0.025;

        // Cálculo da parcela
        $parcela = $valorSolicitado * ($taxa * pow(1 + $taxa, $parcelas))
                   / (pow(1 + $taxa, $parcelas) - 1);

        $valorTotal = $parcela * $parcelas;
        $margem = $salario * 0.30;

        return [
            'nome_cliente'       => fake()->name(),
            'cpf'                => fake()->unique()->cpf(),
            'salario'            => $salario,
            'valor_solicitado'   => $valorSolicitado,
            'quantidade_parcelas'=> $parcelas,
            'valor_parcela'      => round($parcela, 2),
            'valor_total'        => round($valorTotal, 2),
            'margem_disponivel'  => round($margem, 2),
            'taxa_juros'         => $taxa,
            'status'             => fake()->randomElement([
                'rascunho',
                'em_analise',
                'aprovada',
                'reprovada',
                'cancelada'
            ]),
            'observacoes'        => fake()->sentence(),
        ];
    }
}
