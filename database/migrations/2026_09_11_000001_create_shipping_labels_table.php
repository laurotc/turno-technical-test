<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_labels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('easypost_shipment_id')->unique();
            $table->string('easypost_postage_label_id')->nullable()->index();
            $table->string('easypost_rate_id')->nullable();
            $table->string('carrier')->nullable()->index();
            $table->string('service')->nullable();
            $table->string('tracking_code')->nullable()->index();
            $table->text('label_url')->nullable();
            $table->text('label_pdf_url')->nullable();
            $table->json('from_address');
            $table->json('to_address');
            $table->json('parcel');
            $table->json('rate')->nullable();
            $table->json('raw_response')->nullable();
            $table->string('status')->default('purchased')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_labels');
    }
};
