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
        Schema::create('transactions', function (Blueprint $table) {

            $table->id();

            $table->bigInteger('user_id');

            $table->decimal('amount', 10, 2)->default(0);

            $table->string('description', 255)->nullable();

            $table->enum('status', ['CREATED', 'FAILED', 'CONFIRMED'])->default('CREATED');

            $table->timestamps();

            $table->foreign('user_id')->references('telegram_id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
