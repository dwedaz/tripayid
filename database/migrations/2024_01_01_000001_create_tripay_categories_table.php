<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tripay_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_id')->unique()->index();
            $table->string('category_name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->integer('sort_order')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('sort_order');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_categories');
    }
};