<?php


namespace App\Http\Controllers;


use App\Http\Requests\StorePropostaRequest;
use App\Http\Resources\PropostaResource;
use App\Services\PropostaService;
use App\Models\Proposta;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


/**
 * Controller: PropostaController
 * 
 * Responsavel por gerenciar as operacoes relacionada as propostas de credito
 * contem metodos para listagme, criacao, visualização, autorização de status e exclusao.
 */

class PropostaController extends Controller
{
    protected $propostaService;


    /**
     * Constructor do controlador
     * 
     * Injeta o serviço responsável pela lógica de negocios das propostas.
     * 
     */

    public function __construct(PropostaService $propostaService)
    {
        $this->propostaService = $propostaService;

    }
    

    /**
     * Exibe a lista de propostas com filtros opcionais.
     * 
     * @param Request $request - Requisicao contendo filtros (busca, status, paginação)
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['busca','status','page','per_page']);
        $propostas = $this->propostaService->listar($filtros);
        return PropostaResource::collection($propostas);
    }


    /**
     * Cria uma nova proposta.
     * 
     * @param StorePropostaRequest $request - Requisição validada
     * @return PropostaResource
     */
    public function store(StorePropostaRequest $request)
    {
        $proposta = $this->propostaService->criar($request->validated());
        return new PropostaResource($proposta);

    }


    /**
     * Exibe os detalhes de uma proposta especifica.
     * 
     * @param int $id - ID da proposta
     * @return PropostaResource
     */
    public function show($id)
    {
        $proposta = $this->propostaService->buscar($id);
        return new PropostaResource($proposta);
    }


    /**
     * Atualiza o status de uma proposta existente.
     * 
     * @param Request $request - Requsição com o novo status
     * @param int $id - ID da proposta
     * @throws ValidationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizarStatus(Request $request, $id)
    {
        $proposta = Proposta::findOrFail($id);
        $novoStatus = $request->input('status');

        $statusPermitidos = ['rascunho','em_analise','aprovada','reprovada'];

        // abaixo a verificação de validação do status
        if (!in_array($novoStatus, $statusPermitidos))
        {
            throw ValidationException::withMessages([
                'status'=>'Status inválido. Permitidos: ' . implode(', ', $statusPermitidos),

            ]);
        }    

        // não permite retorno para "em_analise" caso seja aprovada ou não
        if (in_array($proposta->status, ['aprovada','reprovada']))//&& $novoStatus === 'em_analise' uma possiblidade porém pode alterar status aprovado
        {
            throw ValidationException::withMessages([
                'status'=> 'Não é permitido retornar para "em_analise" após aprovação ou reprovação.'

            ]);
        }

        // atualização do status da proposta
        $proposta->status = $novoStatus;
        $proposta->save();

        return response()->json([
            'mensagem' => 'Status atualizado com sucesso!',
            'proposta'=> $proposta
        ], 200);
    }


    /**
     * Exclui uma proposta, se permitido
     * 
     * @param int $id - ID da proposta
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $proposta = Proposta::find($id);

        // Caso a proposta não exista
        if(!$proposta)
        {
            return response()->json([
                'mensagem'=>"proposta não encontrada.0"
            ],404);
        }

        // impede a exclusao de uma proposta aprovada
        if($proposta->status === "aprovada")
        {
            return response()->json([
                'mensagem'=>'não é possivel excluir uma proposta aprovada'
            ], 422);

        }

        // essa aqui exclui a proposta
        $proposta->delete();

        return response()->json([
            'mensagem'=>'Proposta excluída com sucesso'
        ],200);
    }

}