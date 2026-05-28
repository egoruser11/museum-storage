<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('blocked_at')->nullable()->index()->after('role');
        });

        DB::table('artifact_submissions')
            ->where('desired_action', 'donate')
            ->update(['desired_price' => null]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('blocked_at');
        });
    }
};
