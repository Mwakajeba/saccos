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
        Schema::table('share_products', function (Blueprint $table) {
            // Share Purchase Limits
            $table->decimal('minimum_purchase_amount', 15, 2)->nullable()->after('nominal_price');
            $table->decimal('maximum_purchase_amount', 15, 2)->nullable()->after('minimum_purchase_amount');
            $table->decimal('maximum_shares_per_member', 15, 2)->nullable()->after('maximum_purchase_amount');
            $table->decimal('minimum_shares_for_membership', 15, 2)->nullable()->after('maximum_shares_per_member');
            $table->decimal('share_purchase_increment', 15, 2)->nullable()->after('minimum_shares_for_membership');

            // Dividend Management
            $table->decimal('dividend_rate', 8, 4)->nullable()->after('allow_dividends_for_inactive_member'); // Percentage (e.g., 5.25%)
            $table->string('dividend_calculation_method')->nullable()->after('dividend_rate'); // on_share_capital, on_share_value, on_minimum_balance, on_average_balance
            $table->string('dividend_payment_frequency')->nullable()->after('dividend_calculation_method'); // Monthly, Quarterly, Semi_Annually, Annually
            $table->integer('dividend_payment_month')->nullable()->after('dividend_payment_frequency'); // 1-12 for month
            $table->integer('dividend_payment_day')->nullable()->after('dividend_payment_month'); // 1-31 for day of month
            $table->decimal('minimum_balance_for_dividend', 15, 2)->nullable()->after('dividend_payment_day');

            // Certificate Settings
            $table->string('certificate_number_prefix', 20)->nullable()->after('description');
            $table->string('certificate_number_format', 100)->nullable()->after('certificate_number_prefix');
            $table->boolean('auto_generate_certificate')->default(true)->after('certificate_number_format');

            // Subscription & Availability
            $table->date('opening_date')->nullable()->after('auto_generate_certificate');
            $table->date('closing_date')->nullable()->after('opening_date');
            $table->boolean('allow_new_subscriptions')->default(true)->after('closing_date');
            $table->boolean('allow_additional_purchases')->default(true)->after('allow_new_subscriptions');
            $table->decimal('maximum_total_shares', 15, 2)->nullable()->after('allow_additional_purchases');

            // Transfer Rules
            $table->boolean('allow_share_transfers')->default(false)->after('maximum_total_shares');
            $table->decimal('transfer_fee', 15, 2)->nullable()->after('allow_share_transfers');
            $table->string('transfer_fee_type', 20)->nullable()->after('transfer_fee'); // fixed, percentage

            // Withdrawal Rules
            $table->boolean('allow_share_withdrawals')->default(false)->after('transfer_fee_type');
            $table->decimal('withdrawal_fee', 15, 2)->nullable()->after('allow_share_withdrawals');
            $table->string('withdrawal_fee_type', 20)->nullable()->after('withdrawal_fee'); // fixed, percentage
            $table->integer('withdrawal_notice_period')->nullable()->after('withdrawal_fee_type');
            $table->string('withdrawal_notice_period_type', 20)->nullable()->after('withdrawal_notice_period'); // Days, Weeks, Months
            $table->decimal('minimum_withdrawal_amount', 15, 2)->nullable()->after('withdrawal_notice_period_type');
            $table->decimal('maximum_withdrawal_amount', 15, 2)->nullable()->after('minimum_withdrawal_amount');
            $table->boolean('allow_partial_withdrawal')->default(false)->after('maximum_withdrawal_amount');

            // Additional Chart Accounts
            $table->foreignId('share_capital_account_id')->nullable()->constrained('chart_accounts')->onDelete('restrict')->after('income_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_products', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['share_capital_account_id']);

            // Drop columns in reverse order
            $table->dropColumn([
                'minimum_purchase_amount',
                'maximum_purchase_amount',
                'maximum_shares_per_member',
                'minimum_shares_for_membership',
                'share_purchase_increment',
                'dividend_rate',
                'dividend_calculation_method',
                'dividend_payment_frequency',
                'dividend_payment_month',
                'dividend_payment_day',
                'minimum_balance_for_dividend',
                'certificate_number_prefix',
                'certificate_number_format',
                'auto_generate_certificate',
                'opening_date',
                'closing_date',
                'allow_new_subscriptions',
                'allow_additional_purchases',
                'maximum_total_shares',
                'allow_share_transfers',
                'transfer_fee',
                'transfer_fee_type',
                'allow_share_withdrawals',
                'withdrawal_fee',
                'withdrawal_fee_type',
                'withdrawal_notice_period',
                'withdrawal_notice_period_type',
                'minimum_withdrawal_amount',
                'maximum_withdrawal_amount',
                'allow_partial_withdrawal',
                'share_capital_account_id',
            ]);
        });
    }
};
