<?php

namespace App\Services;

use App\Models\Proposta;
use Illuminate\Validation\ValidationException;

/**
     * Classe PropostaService
     * 
     * responsável por centralizar as regras de negócio da entidade Proposta.
     * Aqui ficam todos os calculos, validações e operações que envolvem 
     * a logica alem do simples CRUD.
     * 
     * @package App\Sevices
     */

class PropostaService
{
    /**
     * Lista as propostas cadastradas com suporte a filtros e paginação
     * 
     * @param array $filtros Filtros de pesquisa, incluindo "busca", "status" e 'per_page".
     * @return \Illuminate\Contracts\Pagination\LenghtAwarePaginator 
     */
    
    public function listar($filtros)
    {
        $query = Proposta::query();

        // Filtro de busca (por nome ou CPF)
        if (!empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $query->where(function ($q) use ($busca) {
                $q->where('nome_cliente', 'like', "%{$busca}%")
                ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        // Filtro por status
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        // Ordenação: mais recentes primeiro
        $query->orderByDesc('created_at');

        // Paginação
        $porPagina = $filtros['per_page'] ?? 5;
        return $query->paginate($porPagina);
    }

    /**
     * Busca uma proposta especifica pelo ID.
     * 
     * @param int $id ID da proposta
     * @return \APP\Models\Proposta
     * 
     * throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */


    public function buscar($id)
    {
        // Conceito basico do modelo da propoosta (bd)
        return Proposta::findOrFail($id);
    }


    /**
     * cria umva nova proposta aplicando validações e calculos de negocio.
     * 
     * @param array $dados Dados validados da requisição
     * @return \App\Models\Proposta
     * 
     * 
     * @throws \Illuminate\Validation\ValidationException se ocorrer erro de validação
     * 
     */
    public function criar(array $dados)
    {
        //  Validar CPF real
        if (!$this->validarCPF($dados['cpf'])) {
            throw ValidationException::withMessages([
                'cpf' => 'O CPF informado é inválido.',
            ]);
        }

        // Verificar se já existe proposta "em_analise" para o mesmo CPF
        $existeEmAnalise = Proposta::where('cpf', $dados['cpf'])
            ->where('status', 'em_analise')
            ->exists();

        if ($existeEmAnalise) {
            throw ValidationException::withMessages([
                'cpf' => 'Já existe uma proposta em análise para este CPF.',
            ]);
        }

        //  Calcular a margem (30% do salário)
        $margemDisponivel = $dados['salario'] * 0.30;

        //  Calcular o valor das parcelas usando juros compostos (2.5% a.m.)
        $taxa = 0.025;
        $parcelas = $dados['quantidade_parcelas'];
        $valorSolicitado = $dados['valor_solicitado'];

        // Fórmula de juros compostos: PMT = P * (i*(1+i)^n) / ((1+i)^n - 1)
        $valorParcela = $valorSolicitado * ($taxa * pow(1 + $taxa, $parcelas)) / (pow(1 + $taxa, $parcelas) - 1);
        $valorTotal = $valorParcela * $parcelas;

        // Verificar se a parcela ultrapassa 30% do salário
        if ($valorParcela > $margemDisponivel) {
            throw ValidationException::withMessages([
                'valor_parcela' => 'O valor da parcela ultrapassa 30% da renda. Proposta recusada.',
            ]);
        }

        //  Atribuir dados calculados
        $dados['taxa_juros'] = $taxa;
        $dados['valor_parcela'] = round($valorParcela, 2);
        $dados['valor_total'] = round($valorTotal, 2);
        $dados['margem_disponivel'] = round($margemDisponivel, 2);
        $dados['status'] = 'rascunho'; // Status inicial obrigatório
        $dados['observacoes'] = $dados['observacoes'] ?? null;

        // Criar proposta
        return Proposta::create($dados);
    }

    /**
     * Valida um CPF brasileiro de forma algoritmica
     * 
     * @param string $cpf CPF no formato "000.000.000-00"
     * @return bool True se o CPF for valido, False caso contrario
     * 
     * 
     * 
     */

    private function validarCPF(string $cpf): bool
    {
        // Remover caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verificar se tem 11 dígitos ou é repetido
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcular dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}

