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
        Schema::create('eway_bill_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eway_bill_id')->constrained('eway_bills')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained()->cascadeOnDelete();
            $table->double('price', 15, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->double('discount', 15, 2)->default(0);
            $table->string('hsn_code')->nullable();
            $table->double('tax_percent', 5, 2)->default(0);
            $table->double('tax_amount', 15, 2)->default(0);
            $table->double('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eway_bill_details');
    }
};
