<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracker_logs', function (Blueprint $table) {
            // Composite index for daily trend chart (groupByRaw DATE(created_at))
            $table->index(['created_at', 'visitor_id'], 'idx_logs_created_visitor');

            // Composite index for referral filtering + date range
            $table->index(['referral_code', 'created_at'], 'idx_logs_referral_date');

            // Composite index for UTM analytics
            $table->index(['utm_source', 'created_at'], 'idx_logs_source_date');
            $table->index(['utm_medium', 'created_at'], 'idx_logs_medium_date');
            $table->index(['utm_campaign', 'created_at'], 'idx_logs_campaign_date');

            // Composite for IP + date filtering
            $table->index(['ip_address', 'created_at'], 'idx_logs_ip_date');

            // For visit URL grouping (most visited pages)
            $table->index(['visit_url', 'created_at'], 'idx_logs_url_date');
        });
    }

    public function down(): void
    {
        Schema::table('tracker_logs', function (Blueprint $table) {
            $table->dropIndex('idx_logs_created_visitor');
            $table->dropIndex('idx_logs_referral_date');
            $table->dropIndex('idx_logs_source_date');
            $table->dropIndex('idx_logs_medium_date');
            $table->dropIndex('idx_logs_campaign_date');
            $table->dropIndex('idx_logs_ip_date');
            $table->dropIndex('idx_logs_url_date');
        });
    }
};
