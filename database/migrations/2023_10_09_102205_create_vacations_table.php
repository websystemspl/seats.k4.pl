<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('working_time', false, true);
            $table->date('request_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days_off')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacations');
    }
};
