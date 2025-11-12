<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropostaRequest;
use App\Http\Resources\PropostaResource;
use App\Services\PropostaService;
use Illuminate\Http\Request;

/**
 * Controller: PropostaController
 * 
 * Responsavel por receber as requisições HTTP relacionadas as propostas
 * e delegar toda a logica de negocio para o PropostaService
 * 
 * Este controller atua apenas como camada de orquestração
 * - Recebe requisições
 * - Envia dados ao service
 *  - Retorna respostas formatadas
 */

class PropostaController extends Controller
{
    protected $propostaService;

    /**
     * Injeta o service responsavel pela logica de negocio
     * 
     * @param PropostaService $propostaService
     * 
     */

    public function __construct(PropostaService $propostaService)
    {
        $this->propostaService = $propostaService;
    }

    /**
     * lista propostas com filtros opcionais
     * 
     * Filtros aceitos:
     * - busca (nome ou CPF)
     * - status
     * - page e per_page (paginação)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */

    public function index(Request $request)
    {
        $filtros = $request->only(['busca', 'status', 'page', 'per_page']);
        $propostas = $this->propostaService->listar($filtros);

        return PropostaResource::collection($propostas);
    }

    /**
     * Cria uma nova proposta.
     * 
     * Valida os dados via StorePropostaRequest e delega a criação ao service
     * 
     * @param StorePropostaRequest $request
     * @return PropostaResourcce
     */

    public function store(StorePropostaRequest $request)
    {
        $proposta = $this->propostaService->criar($request->validated());
        return new PropostaResource($proposta);
    }

    /**
     * Retorna os detalhes de uma proposta especifica
     * 
     * @param int $id
     * @return PropostaResource
     */
    public function show($id)
    {
        $proposta = $this->propostaService->buscar($id);
        return new PropostaResource($proposta);
    }

    /**
     * Atualiza o status de uma proposta
     * 
     * o controller apenas repassa o ID e o novo status para o service
     * que contem toda a regra de transição permitida
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonReesponse
     */
    public function atualizarStatus(Request $request, $id)
    {
        $novoStatus = $request->input('status');

        $proposta = $this->propostaService->atualizarStatus($id, $novoStatus);

        return response()->json([
            'mensagem' => 'Status atualizado com sucesso!',
            'proposta' => $proposta
        ], 200);
    }

    /**
     * Remove uma proposta
     * 
     * A remoção só é permitida caso a lógica do service permita.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $mensagem = $this->propostaService->deletar($id);

        return response()->json(['mensagem' => $mensagem]);
    }
}
