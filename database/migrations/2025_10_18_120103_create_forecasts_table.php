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
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->decimal('temperature', 5, 2);
            $table->tinyInteger('humidity');
            $table->integer('pressure')->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->string('condition', 100);
            $table->string('icon', 50)->nullable();
            $table->dateTime('forecast_time');
            $table->dateTime('fetched_at');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['location_id', 'forecast_time']);
            $table->index('forecast_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
