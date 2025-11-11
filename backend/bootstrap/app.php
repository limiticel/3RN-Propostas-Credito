<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;



/**
 * Este arquivo é o ponto de inicialização (bootstrap) da aplicação Laravel.
 * 
 * 
 * Ele cria e configura a instancia principal da aplicação
 * definindo caminhos de rotas, middlewares globais e manipulação de exceções
 * 
 */
return Application::configure(basePath: dirname(__DIR__))
    /**
     * Configuração das rotas principais da aplicação
     * o metodo withRouting() mapeia os diferentes arquivos de rota
     * que serão carregados automaticamente pelo Laravel.
     */

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )

    //configuração dos middlewares
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })

    //configuração de tratamento de exceções
    ->withExceptions(function (Exceptions $exceptions): void {
        
        
        //criação da aplicação Laravel
    })->create();
