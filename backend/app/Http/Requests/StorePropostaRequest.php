<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

/**
 * Classe responsavel pela validacao dos dados enviados
 * ao criar uma nova proposta.
 * 
 * Essa classe é automaticamente utilizada pelo laravel
 * antes que os dados hegem ao controlador, garantindo
 * que apenas as informações validas sejam processadas.
 * 
 * @package App\Http\Requests
 */

class StorePropostaRequest extends FormRequest
{   
    /**
     * Define se o usuatio esta autorizado a enviar essa requisição.
     * 
     * @return bool Retorna true para permitir o uso da requisicao
     */

    public function authorize(): bool 
    {
        return true;
    }
    /**
     * Define as regras de validacao para os campos da proposta.
     * 
     * @return array<string,string|array> As regras de validacao aplicadas a cada campo.
     */


    public function rules(): array
    {
        return [
            'nome_cliente' => 'required|string|max:255',
            'cpf' => [
                'required',
                'string',
                'size:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                
            ],
            'valor_solicitado' => 'required|numeric|min:1000|max:50000',
            'quantidade_parcelas' => 'required|integer|min:6|max:60',
            'salario' => 'required|numeric|min:1500.00',
        ];
    }

    /**
     * Define mensagens personalizadas de erro para as regras de validação
     */

    public function messages():array
    {
        return [
            'nome_cliente.required' => 'O nome do cliente é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 14 caracteres no formato XXX.XXX.XXX-XX.',
            'cpf.regex' => 'O CPF deve estar no formato XXX.XXX.XXX-XX.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'valor_solicitado.required' => 'O valor solicitado é obrigatório.',
            'valor_solicitado.numeric' => 'O valor solicitado deve ser um número.',
            'valor_solicitado.min' => 'O valor solicitado deve ser no mínimo R$ 1.000,00.',
            'valor_solicitado.max' => 'O valor solicitado deve ser no máximo R$ 50.000,00.',
            'quantidade_parcelas.required' => 'A quantidade de parcelas é obrigatória.',
            'quantidade_parcelas.integer' => 'A quantidade de parcelas deve ser um número inteiro.',
            'quantidade_parcelas.min' => 'A quantidade de parcelas deve ser no mínimo 6.',
            'quantidade_parcelas.max' => 'A quantidade de parcelas deve ser no máximo 60.',
            'salario.required' => 'O salário é obrigatório.',
            'salario.numeric' => 'O salário deve ser um número.',
            'salario.min' => 'O salário deve ser no mínimo R$ 1.500,00.',
        ];
    }

}