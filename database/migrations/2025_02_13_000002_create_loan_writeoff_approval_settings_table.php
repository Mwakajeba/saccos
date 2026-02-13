<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_writeoff_approval_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->integer('approval_levels')->default(2);
            $table->decimal('auto_approval_limit', 15, 2)->default(0);
            $table->decimal('approval_threshold_1', 15, 2)->nullable();
            $table->decimal('approval_threshold_2', 15, 2)->nullable();
            $table->decimal('approval_threshold_3', 15, 2)->nullable();
            $table->decimal('approval_threshold_4', 15, 2)->nullable();
            $table->decimal('approval_threshold_5', 15, 2)->nullable();
            $table->boolean('require_approval_for_all')->default(false);

            $table->string('level1_approval_type')->default('role');
            $table->json('level1_approvers')->nullable();

            $table->string('level2_approval_type')->nullable();
            $table->json('level2_approvers')->nullable();

            $table->string('level3_approval_type')->nullable();
            $table->json('level3_approvers')->nullable();

            $table->string('level4_approval_type')->nullable();
            $table->json('level4_approvers')->nullable();

            $table->string('level5_approval_type')->nullable();
            $table->json('level5_approvers')->nullable();

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_writeoff_approval_settings');
    }
};
