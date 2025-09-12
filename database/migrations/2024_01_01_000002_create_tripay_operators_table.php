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
            $table->string('operator_id')->unique()->index();
            $table->string('operator_name');
            $table->string('operator_code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->string('logo_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('sort_order');
            $table->index('operator_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_operators');
    }
};