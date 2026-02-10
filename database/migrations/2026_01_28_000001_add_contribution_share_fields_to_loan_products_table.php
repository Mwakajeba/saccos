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
        Schema::table('loan_products', function (Blueprint $table) {
            // Add has_top_up boolean column
            if (!Schema::hasColumn('loan_products', 'has_top_up')) {
                $table->boolean('has_top_up')->default(false)->after('maximum_number_of_loans');
            }

            // Add contribution-related columns
            if (!Schema::hasColumn('loan_products', 'has_contribution')) {
                $table->boolean('has_contribution')->default(false)->after('allow_push_to_ess');
            }
            if (!Schema::hasColumn('loan_products', 'contribution_product_id')) {
                $table->unsignedBigInteger('contribution_product_id')->nullable()->after('has_contribution');
            }
            if (!Schema::hasColumn('loan_products', 'contribution_value_type')) {
                $table->string('contribution_value_type')->nullable()->after('contribution_product_id');
            }
            if (!Schema::hasColumn('loan_products', 'contribution_value')) {
                $table->decimal('contribution_value', 15, 2)->nullable()->after('contribution_value_type');
            }

            // Add share-related columns
            if (!Schema::hasColumn('loan_products', 'has_share')) {
                $table->boolean('has_share')->default(false)->after('contribution_value');
            }
            if (!Schema::hasColumn('loan_products', 'share_product_id')) {
                $table->unsignedBigInteger('share_product_id')->nullable()->after('has_share');
            }
            if (!Schema::hasColumn('loan_products', 'share_value_type')) {
                $table->string('share_value_type')->nullable()->after('share_product_id');
            }
            if (!Schema::hasColumn('loan_products', 'share_value')) {
                $table->decimal('share_value', 15, 2)->nullable()->after('share_value_type');
            }
        });

        // Add foreign key constraints separately to avoid issues with column order
        Schema::table('loan_products', function (Blueprint $table) {
            if (Schema::hasColumn('loan_products', 'contribution_product_id')) {
                $table->foreign('contribution_product_id')
                    ->references('id')
                    ->on('contribution_products')
                    ->onDelete('set null');
            }
            if (Schema::hasColumn('loan_products', 'share_product_id')) {
                $table->foreign('share_product_id')
                    ->references('id')
                    ->on('share_products')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('loan_products', 'contribution_product_id')) {
                $table->dropForeign(['contribution_product_id']);
            }
            if (Schema::hasColumn('loan_products', 'share_product_id')) {
                $table->dropForeign(['share_product_id']);
            }
        });

        Schema::table('loan_products', function (Blueprint $table) {
            $columns = [
                'has_top_up',
                'has_contribution',
                'contribution_product_id',
                'contribution_value_type',
                'contribution_value',
                'has_share',
                'share_product_id',
                'share_value_type',
                'share_value',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('loan_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
