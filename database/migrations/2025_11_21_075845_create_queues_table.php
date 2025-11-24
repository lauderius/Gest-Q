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
        if (!Schema::hasTable('queues')) {
            Schema::create('queues', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('group_name');
                $table->boolean('active')->default(true);
                $table->integer('avg_service_sec')->default(180);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
