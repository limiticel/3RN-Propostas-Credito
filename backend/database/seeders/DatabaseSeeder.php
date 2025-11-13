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
        $this->call(PropostaSeeder::class);
    }

}
