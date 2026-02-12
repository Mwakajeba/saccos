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
        Schema::create('arrears_classifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->integer('days_from')->default(0);
            $table->integer('days_to')->nullable(); // null means unlimited (e.g., 181+)
            $table->string('bucket_label'); // e.g., "0", "1-30", "31-90", "91-180", "181+"
            $table->string('status'); // e.g., "Current", "Past Due", "Substandard", "Doubtful", "Loss/NPL"
            $table->decimal('provision_percentage', 5, 2)->default(0); // e.g., 0, 1, 5, 25, 50, 100
            $table->text('comments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arrears_classifications');
    }
};
