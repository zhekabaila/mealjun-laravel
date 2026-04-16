<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitor_analytics', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->date('visit_date')->notNull();
            $table->string('visitor_ip', 45)->nullable();
            $table->string('visitor_city', 255)->nullable();
            $table->string('visitor_province', 255)->nullable();
            $table->string('visitor_country', 100)->default('Indonesia');
            $table->string('page_viewed', 255)->nullable()->comment('/, /products, /about, dll');
            $table->char('product_id', 36)->nullable()->comment('Jika view product detail');
            $table->text('referrer_url')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index('visit_date');
            $table->index('visitor_city');
            $table->index('page_viewed');
            $table->index('product_id');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_analytics');
    }
};
