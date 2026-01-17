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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // Icon name like 'info_outline', 'credit_card', 'feedback'
            $table->string('color')->default('blue'); // Color name: blue, green, orange, etc.
            $table->string('image_path')->nullable(); // Path to uploaded image
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_date')->nullable(); // When to start showing
            $table->dateTime('end_date')->nullable(); // When to stop showing
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
