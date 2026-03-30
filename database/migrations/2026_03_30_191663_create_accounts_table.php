<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->onDelete('cascade');
            $table->string('account_number', 50)->unique();
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('currency', 3)->default('UAH');
            $table->timestamps();

            // Indexes
            $table->index('client_id');
            $table->index('account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
