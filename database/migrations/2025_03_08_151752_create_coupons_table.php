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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained()->onDelete('cascade');
            $table->string('offer_title');
            $table->integer('regular_price');
            $table->integer('offer_price');
            $table->date('start_date');
            $table->date('finish_date');
            $table->date('deadline');
            $table->integer('quantity_limit');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
