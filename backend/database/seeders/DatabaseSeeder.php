<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed da aplicação do database
     */
    public function run(): void
    {
        // cria usuário de teste (opcional)
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // executa o seeder das propostas
        $this->call([
            PropostaSeeder::class,
        ]);
    }

}
