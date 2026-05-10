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
        Schema::table('campaign_templates', function (Blueprint $table): void {
            $table->string('gw_template_id')->nullable()->after('id');
            $table->json('gw_payload')->nullable()->after('status');
            $table->json('gw_response')->nullable()->after('gw_payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_templates', function (Blueprint $table): void {
            $table->dropColumn(['gw_template_id', 'gw_payload', 'gw_response']);
        });
    }
};
