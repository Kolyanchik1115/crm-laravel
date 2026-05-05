<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['client_id', 'created_at'], 'idx_invoices_client_created');
            $table->index(['status', 'issued_at'], 'idx_invoices_status_issued');
        });

        // Transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['account_id', 'created_at'], 'idx_transactions_account_created');
            $table->index(['type', 'created_at'], 'idx_transactions_type_created');
        });

        // Accounts
        Schema::table('accounts', function (Blueprint $table) {
            $table->index('balance', 'idx_accounts_balance');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_client_created');
            $table->dropIndex('idx_invoices_status_issued');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_account_created');
            $table->dropIndex('idx_transactions_type_created');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('idx_accounts_balance');
        });
    }
};
