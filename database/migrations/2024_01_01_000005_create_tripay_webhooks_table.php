<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tripay_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('tripay_trx_id')->nullable()->index();
            $table->string('api_trx_id')->nullable()->index();
            $table->string('event_type')->index(); // payment_updated, payment_success, etc.
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->string('signature')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->index(['processed', 'created_at']);
            $table->index('retry_count');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_webhooks');
    }
};