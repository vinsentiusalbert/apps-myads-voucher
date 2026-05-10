<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'merchant_id')) {
                $table->string('merchant_id', 128)->nullable()->after('password')->index();
            }

            if (! Schema::hasColumn('users', 'partner_id')) {
                $table->string('partner_id', 128)->nullable()->after('merchant_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'partner_id')) {
                $table->dropColumn('partner_id');
            }

            if (Schema::hasColumn('users', 'merchant_id')) {
                $table->dropColumn('merchant_id');
            }
        });
    }
};
