<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_lba_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('campaign_name');
            $table->string('sender_name');
            $table->text('message');
            $table->string('content_name');
            $table->unsignedInteger('recipient_total');
            $table->string('delivery_start_date', 8);
            $table->string('delivery_end_date', 8);
            $table->string('delivery_start_time', 4);
            $table->string('delivery_end_time', 4);
            $table->string('control_number', 32);
            $table->string('location_name');
            $table->decimal('location_lat', 10, 7);
            $table->decimal('location_lng', 10, 7);
            $table->unsignedInteger('location_radius');
            $table->string('location_type', 20);
            $table->json('gw_payload')->nullable();
            $table->json('gw_response')->nullable();
            $table->string('gw_campaign_id')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('gw_campaign_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_lba_campaigns');
    }
};
