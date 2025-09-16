<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tripay_operators', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('type')->nullable();
            $table->enum('billing_type', ['prepaid', 'postpaid'])->default('prepaid');
          
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('tripay_categories')->onDelete('cascade');

            $table->index(['type', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_operators');
    }
};