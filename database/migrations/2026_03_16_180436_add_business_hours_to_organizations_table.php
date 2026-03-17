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
            if (! Schema::hasColumn('organizations', 'business_hours_start')) {
                $table->string('business_hours_start', 5)->default('08:00')->after('appointment_slot_minutes');
            }
            if (! Schema::hasColumn('organizations', 'business_hours_end')) {
                $table->string('business_hours_end', 5)->default('17:00')->after('business_hours_start');
            }
            if (! Schema::hasColumn('organizations', 'business_days')) {
                $table->json('business_days')->nullable()->after('business_hours_end');
            }
        });

        // Set default business days for existing rows (MySQL JSON can't have default)
        \DB::table('organizations')->whereNull('business_days')->update([
            'business_days' => json_encode([1, 2, 3, 4, 5]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['business_hours_start', 'business_hours_end', 'business_days']);
        });
    }
};
