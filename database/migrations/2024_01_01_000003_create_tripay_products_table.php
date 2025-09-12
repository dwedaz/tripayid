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
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('category_id')->index();
            $table->string('operator_id')->index();
           
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->enum('type', ['prepaid', 'postpaid'])->default('prepaid'); 
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('tripay_categories')->onDelete('cascade');
            $table->foreign('operator_id')->references('operator_id')->on('tripay_operators')->onDelete('cascade');
            
            $table->index(['category_id', 'status']);
            $table->index(['operator_id', 'status']);
            $table->index(['type', 'status']);
           
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_products');
    }
};

