<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

trait SeedsMseaCatalogs
{
    protected function seedMseaCatalogs(): void
    {
        foreach (['estudiante', 'profesor', 'director'] as $rol) {
            DB::table('roles')->updateOrInsert(['nombre' => $rol], ['nombre' => $rol]);
        }

        DB::table('secciones')->updateOrInsert(['nombre' => 'General'], ['nombre' => 'General']);
    }
}
