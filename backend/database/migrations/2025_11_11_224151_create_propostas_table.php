<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


/**
 * Migration responsavel por criar a tabela 'propostas' no banco de dados
 * 
 * essa tabela armazena as informações relacionadas as propostas de credito
 * incluindo dados do cliente, valores, juros, parcelas e observações
 */

return new class extends Migration
{

    /**
     * Executa migrações (Criação da tabela 'propostas')
     * 
     * o metodo up é chamado quando executamos o comado 'php artisan migrate'
     * Ele define a estrutura e os tipos de dados da tabela
     *      
     */
    public function up(): void
    {
        Schema::create('propostas', function (Blueprint $table) {
        
        // Chave primaria auto incremental (principal forma de identificação no backend)
        $table->id();

        // Dados do cliente (Nome, CPF)
        $table->string('nome_cliente');
        $table->string('cpf', 14);
            
        //sobre a proposta
        $table->decimal('valor_solicitado', 10, 2);
        $table->integer('quantidade_parcelas');
        $table->decimal('valor_parcela', 10, 2)->nullable();
        $table->decimal('valor_total', 10, 2)->nullable();
        $table->string('status')->default('Em análise');
        
        //data e hora
        $table->timestamps();
        
        //dados financeiros
        $table->decimal('salario', 10, 2);
        $table->decimal('taxa_juros', 5, 3)->default(0.025);
        $table->decimal('margem_disponivel', 10, 2)->nullable();
        
        //observações (opcional)
        $table->text('observacoes')->nullable();
});
    }

    /**
     * reverte a migração 
     * 
     * o metodo 'down' é executado ao rodar 'php artisan migrate:rollback',
     */
    public function down(): void
    {
        Schema::dropIfExists('propostas');
    }
};
