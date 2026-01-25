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
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_class_id')->nullable()->after('asset_category_id');
            $table->string('model')->nullable()->after('name');
            $table->string('manufacturer')->nullable()->after('model');
            $table->date('capitalization_date')->nullable()->after('purchase_date');
            $table->string('building_reference')->nullable()->after('location');
            $table->decimal('gps_lat', 10, 8)->nullable()->after('building_reference');
            $table->decimal('gps_lng', 11, 8)->nullable()->after('gps_lat');
            $table->decimal('current_nbv', 18, 2)->nullable()->after('salvage_value');
            $table->integer('warranty_months')->nullable()->after('tag');
            $table->date('warranty_expiry_date')->nullable()->after('warranty_months');
            $table->string('insurance_policy_no')->nullable()->after('warranty_expiry_date');
            $table->decimal('insured_value', 18, 2)->nullable()->after('insurance_policy_no');
            $table->date('insurance_expiry_date')->nullable()->after('insured_value');
            // Fleet Management fields
            $table->string('registration_number')->nullable()->after('description');
            $table->string('ownership_type')->nullable()->after('registration_number');
            $table->string('fuel_type')->nullable()->after('ownership_type');
            $table->decimal('capacity_tons', 10, 2)->nullable()->after('fuel_type');
            $table->decimal('capacity_volume', 10, 2)->nullable()->after('capacity_tons');
            $table->integer('capacity_passengers')->nullable()->after('capacity_volume');
            $table->date('license_expiry_date')->nullable()->after('capacity_passengers');
            $table->date('inspection_expiry_date')->nullable()->after('license_expiry_date');
            $table->string('operational_status')->nullable()->after('inspection_expiry_date');
            $table->string('gps_device_id')->nullable()->after('operational_status');
            $table->string('current_location')->nullable()->after('gps_device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'tax_class_id',
                'model',
                'manufacturer',
                'capitalization_date',
                'building_reference',
                'gps_lat',
                'gps_lng',
                'current_nbv',
                'warranty_months',
                'warranty_expiry_date',
                'insurance_policy_no',
                'insured_value',
                'insurance_expiry_date',
                'registration_number',
                'ownership_type',
                'fuel_type',
                'capacity_tons',
                'capacity_volume',
                'capacity_passengers',
                'license_expiry_date',
                'inspection_expiry_date',
                'operational_status',
                'gps_device_id',
                'current_location',
            ]);
        });
    }
};
