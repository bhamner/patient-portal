<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('subdomain')->nullable()->unique()->after('name');
            $table->string('primary_color', 9)->nullable()->after('subdomain');
            $table->string('secondary_color', 9)->nullable()->after('primary_color');
            $table->string('accent_color', 9)->nullable()->after('secondary_color');
            $table->string('logo_url')->nullable()->after('accent_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'subdomain',
                'primary_color',
                'secondary_color',
                'accent_color',
                'logo_url',
            ]);
        });
    }
};

