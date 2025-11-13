<?php

namespace App\Services;

use App\Models\Proposta;
use Illuminate\Validation\ValidationException;

/**
 * Service: PropostaService
 * 
 * Centraliza toda a logica relacionada as propostas de credito
 * 
 * Aqui ficam:
 *  - Validações especificas de dominio (CPF, margem, regras de status)
 *  - Calculos financeiros (juros compostos, parcela e valor total)
 *  - Regras de transição de status
 *  - Restrições sobre exclusão ou atualização
 * 
 * O controlador apenas chama este service e retorna as propostas ao cliente.
 */

class PropostaService
{
    /**
     * Lista propostas com filtros opcionais (busca, status e paginação)
     * 
     * @param array $filtros
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listar($filtros)
    {
        $query = Proposta::query();

        if (!empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $query->where(function ($q) use ($busca) {
                $q->where('nome_cliente', 'like', "%{$busca}%")
                    ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        $query->orderByDesc('created_at');

        return $query->paginate($filtros['per_page'] ?? 5);
    }

    /**
     * busca uma proposta especifica pelo ID
     * 
     * @param int $id
     * @return Proposta
     */

    public function buscar($id)
    {
        return Proposta::findOrFail($id);
    }

    /**
     * Cria uma nova proposta aplicando:
     *  - Validação de CPF
     *  - Verificação de proposta já em analise
     *  - Cálculo de margem
     *  - Calculo de parcela via juros compostos (2,5% a.m)
     *  - Validação se parcela cabe na margem (<= 30% da renda)
     * 
     * @param array $dados
     * @return Proposta
     * 
     * @throws ValidationException
     */

    public function criar(array $dados)
    {
        if (!$this->validarCPF($dados['cpf'])) {
            throw ValidationException::withMessages([
                'cpf' => 'O CPF informado é inválido.',
            ]);
        }
        

        $margem = $dados['salario'] * 0.30;

        $taxa = 0.025;
        $parcelas = $dados['quantidade_parcelas'];
        $valorSolicitado = $dados['valor_solicitado'];

        $parcela = $valorSolicitado * ($taxa * pow(1 + $taxa, $parcelas)) 
                    / (pow(1 + $taxa, $parcelas) - 1);

        $valorTotal = $parcela * $parcelas;

        if ($parcela > $margem) {
            throw ValidationException::withMessages([
                'valor_parcela' => 'O valor da parcela ultrapassa 30% da renda.',
            ]);
        }

        $dados['taxa_juros'] = $taxa;
        $dados['valor_parcela'] = round($parcela, 2);
        $dados['valor_total'] = round($valorTotal, 2);
        $dados['margem_disponivel'] = round($margem, 2);
        $dados['status'] = 'rascunho';
        $dados['observacoes'] = $dados['observacoes'] ?? null;




        return Proposta::create($dados);
    }

    /**
     * Atualiza o status de uma proposta seguindo regras de negocio
     * 
     * Fluxo permitido:
     *  - rascunho -> em analise
     *  - em analise -> aprovada/reprovada/cancelada 
     *  - reporvada -> somente cancelada
     * 
     * Não permitido:
     * - Proposta aprovada não pode mudar MAIS NADA
     * - Proposta cancelada nunca pode mudar
     * 
     * @param int $id
     * @param string $novoStatus
     * @return Proposta
     * 
     * @throws ValidationException
     */

    public function atualizarStatus($id, $novoStatus)
    {
        $proposta = Proposta::findOrFail($id);

        $validos = ['rascunho', 'em_analise', 'aprovada', 'reprovada', 'cancelada'];
        

        // Bloqueia mais de uma proposta em análise para o mesmo CPF
        if ($novoStatus === 'em_analise') {

            $cpf = $proposta->cpf;

            $jaExiste = Proposta::where('cpf', $cpf)
                ->where('status', 'em_analise')
                ->where('id', '!=', $proposta->id) // ignora a própria proposta
                ->exists();

            if ($jaExiste) {
                throw ValidationException::withMessages([
                    'status' => 'Já existe outra proposta em análise para este CPF.'
                ]);
            }
        }

        if (!in_array($novoStatus, $validos)) {
            throw ValidationException::withMessages([
                'status' => 'Status inválido.'
            ]);
        }

        // Cancelada NÃO pode mudar nunca
        if ($proposta->status === 'cancelada') {
            throw ValidationException::withMessages([
                'status' => 'Propostas canceladas não podem ser modificadas.'
            ]);
        }

        // Aprovada NÃO pode mudar para NENHUM outro status
        if ($proposta->status === 'aprovada') {
            throw ValidationException::withMessages([
                'status' => 'Propostas aprovadas não podem ter o status alterado.'
            ]);
        }

        // Reprovada só pode virar cancelada (mas isso é opcional; você decide)
        if ($proposta->status === 'reprovada' && $novoStatus !== 'cancelada') {
            throw ValidationException::withMessages([
                'status' => 'Propostas reprovadas só podem ser canceladas.'
            ]);
        }

        $proposta->status = $novoStatus;
        $proposta->save();

        return $proposta;
    }

    /**
     * Exclui uma proposta, desde que permitido
     * 
     * regras:
     * - Propostas aprovadas não podem ser excluidas.
     * - Cancelada e reprovadas podem ser excluidas normalmente.
     * 
     * @param int $id
     * @return string Mensagem de sucesso
     * 
     * @throws ValidationException
     */
    public function deletar($id)
    {
        $proposta = Proposta::find($id);

        if (!$proposta) {
            throw ValidationException::withMessages([
                'id' => 'Proposta não encontrada.'
            ]);
        }

        if ($proposta->status === 'aprovada') {
            throw ValidationException::withMessages([
                'status' => 'Propostas aprovadas não podem ser excluídas.'
            ]);
        }

        $proposta->delete();

        return 'Proposta excluída com sucesso.';
    }

    /**
     * Valida o CPF brasileiro utilizando o algoritmo oficial
     * 
     * @param string $cpf
     * @return boll
     */

    private function validarCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);


        // Deve ter extamente 11 digitos e nao pode ser repetido
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calculo dos digitos verificadores
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
