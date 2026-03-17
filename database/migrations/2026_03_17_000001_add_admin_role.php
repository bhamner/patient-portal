<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('roles')->where('name', 'admin')->exists()) {
            DB::table('roles')->insert([
                'name' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('role_user')
            ->whereIn('role_id', DB::table('roles')->where('name', 'admin')->pluck('id'))
            ->delete();
        DB::table('roles')->where('name', 'admin')->delete();
    }
};
