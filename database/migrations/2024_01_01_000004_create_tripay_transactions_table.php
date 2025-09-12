<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tripay_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tripay_trx_id')->nullable()->index();
            $table->string('api_trx_id')->unique()->index();
            $table->string('product_id')->index();
            $table->string('customer_number');
            $table->string('customer_name')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled'])->default('pending');
            $table->enum('type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->text('message')->nullable();
            $table->string('sn')->nullable(); // serial number for successful transactions
            $table->json('response_data')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('product_id')->on('tripay_products')->onDelete('cascade');
            
            $table->index(['status', 'type']);
            $table->index(['customer_number', 'type']);
            $table->index('created_at');
            $table->index('completed_at');
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_transactions');
    }
};