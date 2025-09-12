<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tripay_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique()->index();
            $table->string('product_name');
            $table->string('category_id')->index();
            $table->string('operator_id')->index();
            $table->decimal('product_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('profit_margin', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->string('denomination')->nullable();
            $table->json('additional_info')->nullable();
            $table->time('cut_off_start')->nullable();
            $table->time('cut_off_end')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('tripay_categories')->onDelete('cascade');
            $table->foreign('operator_id')->references('operator_id')->on('tripay_operators')->onDelete('cascade');
            
            $table->index(['category_id', 'status']);
            $table->index(['operator_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('is_featured');
            $table->index('sort_order');
            $table->index('product_price');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_products');
    }
};