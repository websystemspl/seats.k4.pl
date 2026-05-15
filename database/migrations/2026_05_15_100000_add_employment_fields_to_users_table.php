<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('employment_start_date')->nullable()->after('working_time');
            $table->unsignedTinyInteger('education_years')->default(0)->after('employment_start_date');
            $table->date('education_completed_date')->nullable()->after('education_years');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employment_start_date', 'education_years', 'education_completed_date']);
        });
    }
};
