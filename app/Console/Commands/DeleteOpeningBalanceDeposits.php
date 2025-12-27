<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShareDeposit;
use App\Models\GlTransaction;
use App\Models\ShareAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteOpeningBalanceDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:delete-opening-balance-deposits {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all opening balance deposits and reverse their GL transactions and share account balances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No data will be deleted');
            $this->newLine();
        }

        // Find all opening balance deposits
        // Opening balance deposits have bank_account_id = null and notes/transaction_reference contains "Opening Balance"
        $openingBalanceDeposits = ShareDeposit::whereNull('bank_account_id')
            ->where(function($query) {
                $query->where('notes', 'like', '%Opening Balance%')
                      ->orWhere('transaction_reference', 'like', '%Opening Balance%')
                      ->orWhere('notes', 'like', '%opening balance%')
                      ->orWhere('transaction_reference', 'like', '%opening balance%');
            })
            ->with(['shareAccount', 'shareAccount.shareProduct'])
            ->get();

        if ($openingBalanceDeposits->isEmpty()) {
            $this->info('No opening balance deposits found.');
            return 0;
        }

        $this->info("Found {$openingBalanceDeposits->count()} opening balance deposit(s) to delete.");
        $this->newLine();

        // Show what will be deleted
        $this->table(
            ['ID', 'Account Number', 'Customer', 'Amount', 'Shares', 'Date', 'Status'],
            $openingBalanceDeposits->map(function($deposit) {
                return [
                    $deposit->id,
                    $deposit->shareAccount->account_number ?? 'N/A',
                    $deposit->shareAccount->customer->name ?? 'N/A',
                    number_format($deposit->deposit_amount, 2),
                    number_format($deposit->number_of_shares, 4),
                    $deposit->deposit_date->format('Y-m-d'),
                    $deposit->status,
                ];
            })->toArray()
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('This is a dry run. Run without --dry-run flag to actually delete.');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to delete these opening balance deposits? This action cannot be undone!', false)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->newLine();
        $this->info('Deleting opening balance deposits...');
        $this->newLine();

        $deletedCount = 0;
        $errorCount = 0;

        DB::beginTransaction();

        try {
            foreach ($openingBalanceDeposits as $deposit) {
                try {
                    $shareAccount = $deposit->shareAccount;

                    if ($shareAccount) {
                        // Reverse GL transactions
                        $glTransactions = GlTransaction::where('transaction_type', 'share_deposit')
                            ->where('transaction_id', $deposit->id)
                            ->get();

                        foreach ($glTransactions as $glTransaction) {
                            // Delete the GL transaction (it will be automatically reversed by deleting)
                            $glTransaction->delete();
                            $this->line("  - Deleted GL Transaction ID: {$glTransaction->id}");
                        }

                        // Reverse share account balance (subtract the shares)
                        $shareAccount->share_balance -= $deposit->number_of_shares;
                        
                        // Update last transaction date if this was the last transaction
                        $lastDeposit = ShareDeposit::where('share_account_id', $shareAccount->id)
                            ->where('id', '!=', $deposit->id)
                            ->whereNotNull('bank_account_id') // Not opening balance
                            ->orderBy('deposit_date', 'desc')
                            ->first();
                        
                        if ($lastDeposit) {
                            $shareAccount->last_transaction_date = $lastDeposit->deposit_date;
                        } else {
                            $shareAccount->last_transaction_date = null;
                        }
                        
                        $shareAccount->save();
                        $this->line("  - Updated share account balance for: {$shareAccount->account_number}");
                    }

                    // Delete the deposit
                    $depositId = $deposit->id;
                    $deposit->delete();
                    $deletedCount++;
                    $this->info("  ✓ Deleted deposit ID: {$depositId}");

                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("  ✗ Error deleting deposit ID {$deposit->id}: " . $e->getMessage());
                    Log::error("Error deleting opening balance deposit {$deposit->id}: " . $e->getMessage());
                }
            }

            DB::commit();

            $this->newLine();
            $this->info("Successfully deleted {$deletedCount} opening balance deposit(s).");
            
            if ($errorCount > 0) {
                $this->warn("Encountered {$errorCount} error(s) during deletion.");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during deletion: ' . $e->getMessage());
            Log::error('Error deleting opening balance deposits: ' . $e->getMessage());
            return 1;
        }
    }
}

