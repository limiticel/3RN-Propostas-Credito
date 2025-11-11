<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Criação da tabela 'propostas'
 * 
 * Esta migration define a estrutura da tabela 'propostas', responsáveis por armazenar
 * informações sobre solicitações de crédito realizadas por clientes.
 * 
 * cada registro representa um proposta de empréstimo, contendo dados do cliente,
 * valores envolvidos e status da analise.
 */

return new class extends Migration
{
    /**
    * Executa o migration (criação da tabela)
    * o metodo `up()`é responsavel por definir e criar a estrutura
    * da tabela 'proposta' no banco de dados.
    */

    public function up(): void
    {
        Schema::create('propostas', function(Blueprint $table)
        {   
            // Chave primária (ID auto incremental)
            $table->id();

            // Dados do cliente
            $table->string('nome_cliente');
            $table->string('cpf', 14)->unique();

            // Informações financeiras da proposta
            $table->decimal('valor_solicitado',10,2);
            $table->integer('quantidade_parcelas');

            // Calculos financeiros derivados
            $table->decimal('valor_parcela',10,2)->nullable();
            $table->decimal('valor_total',10,2)->nullable();
            
            // status da proposta
            $table->string('status')->default('Em analise');
            
            // data e hora da atualização/criação
            $table->timestamps();
            
            // informações adicionais
            $table->decimal('salario',10,2);
            $table->decimal('taxa_juros',5,3)->default(0.025);
            $table->decimal('margem_disponivel',10,2)->nullable();
            $table->text('observacoes')->nullable();
           
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('propostas');
    }
};