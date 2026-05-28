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
        Schema::create('eway_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('party_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number');
            $table->timestamp('invoice_date');
            
            // E-Way Bill specific fields
            $table->string('supply_type')->default('Outward');
            $table->string('sub_type')->default('Supply');
            $table->string('document_type')->default('Tax Invoice');
            
            // Dispatch/Consignor Details
            $table->string('from_name')->nullable();
            $table->string('from_gstin')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_pincode')->nullable();
            $table->string('from_state')->nullable();
            
            // Delivery/Consignee Details
            $table->string('to_name')->nullable();
            $table->string('to_gstin')->nullable();
            $table->string('to_address')->nullable();
            $table->string('to_pincode')->nullable();
            $table->string('to_state')->nullable();
            
            // Transporter/Vehicle Details
            $table->string('transporter_name')->nullable();
            $table->string('transporter_id')->nullable();
            $table->string('transport_mode')->default('Road'); // Road, Rail, Air, Ship
            $table->integer('distance')->default(0);
            $table->string('vehicle_number')->nullable();
            $table->string('vehicle_type')->default('Regular'); // Regular, Over Dimensional Cargo (ODC)
            
            // Financial details
            $table->foreignId('vat_id')->nullable()->constrained()->nullOnDelete();
            $table->double('sub_total', 15, 2)->default(0);
            $table->double('discount_amount', 15, 2)->default(0);
            $table->double('tax_amount', 15, 2)->default(0);
            $table->double('shipping_charge', 15, 2)->default(0);
            $table->double('total_amount', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eway_bills');
    }
};
