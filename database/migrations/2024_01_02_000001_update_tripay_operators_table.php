<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists and needs updating
        if (Schema::hasTable('tripay_operators')) {
            Schema::table('tripay_operators', function (Blueprint $table) {
                // Add type field if it doesn't exist
                if (!Schema::hasColumn('tripay_operators', 'type')) {
                    $table->string('type')->nullable()->after('status');
                }
                
                // Modify category_id to be nullable and proper type
                if (Schema::hasColumn('tripay_operators', 'category_id')) {
                    // Drop foreign key constraint first if exists
                    try {
                        $table->dropForeign(['category_id']);
                    } catch (Exception $e) {
                        // Ignore if constraint doesn't exist
                    }
                    
                    // Change column type
                    $table->unsignedBigInteger('category_id')->nullable()->change();
                    
                    // Re-add foreign key constraint
                    $table->foreign('category_id')->references('id')->on('tripay_categories')->onDelete('cascade');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tripay_operators')) {
            Schema::table('tripay_operators', function (Blueprint $table) {
                if (Schema::hasColumn('tripay_operators', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
    }
};