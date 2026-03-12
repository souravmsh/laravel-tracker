<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create the table without the foreign key constraint initially
        Schema::create("tracker_logs", function (Blueprint $table) {
            $table->id();
            $table->uuid("visitor_id")->index();
            $table->string("referral_code", 32)->nullable()->index();
            $table->string("referral_url", 255)->nullable();
            $table->string("visit_url", 255)->nullable();
            $table->string("utm_source", 100)->nullable()->index();
            $table->string("utm_medium", 100)->nullable();
            $table->string("utm_campaign", 100)->nullable();
            $table->string("ip_address", 45)->nullable()->index();
            $table->string("country_code", 5)->nullable()->index();
            $table->string("country_name", 32)->nullable()->index();
            $table->string("country_flag", 10)->nullable();
            $table->string("address")->nullable();
            $table->string("country_geo", 64)->nullable();
            $table->text("user_agent")->nullable();
            $table->unsignedBigInteger("user_id")->nullable();
            $table
                ->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("set null");
            $table->string("session_id", 100)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ["visitor_id", "referral_code", "utm_source", "created_at"],
                "referral_log_index"
            );
        });

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

        // Add the foreign key constraint after the referrals table is created
        if (Schema::hasTable("tracker_referrals")) {
            Schema::table("tracker_logs", function (Blueprint $table) {
                $table
                    ->foreign("referral_code")
                    ->references("code")
                    ->on("tracker_referrals")
                    ->onDelete("set null");
            });
        }
    }

    public function down(): void
    {
        // Drop the foreign key first
        Schema::table("tracker_logs", function (Blueprint $table) {
            $table->dropForeign(["referral_code"]);

            $table->dropIndex('idx_logs_created_visitor');
            $table->dropIndex('idx_logs_referral_date');
            $table->dropIndex('idx_logs_source_date');
            $table->dropIndex('idx_logs_medium_date');
            $table->dropIndex('idx_logs_campaign_date');
            $table->dropIndex('idx_logs_ip_date');
            $table->dropIndex('idx_logs_url_date');
        });
        
        Schema::dropIfExists("tracker_logs");
    }
};
