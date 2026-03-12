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
            $table->string("session_id", 100)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key: User
            $table->foreign("user_id")->references("id")->on("users")->onDelete("set null");

            // Additional Indices
            $table->index(["visitor_id", "referral_code", "utm_source", "created_at"], "referral_log_index");
            
            // Performance Indices
            $table->index(['created_at', 'visitor_id'], 'idx_logs_created_visitor');
            $table->index(['referral_code', 'created_at'], 'idx_logs_referral_date');
            $table->index(['utm_source', 'created_at'], 'idx_logs_source_date');
            $table->index(['utm_medium', 'created_at'], 'idx_logs_medium_date');
            $table->index(['utm_campaign', 'created_at'], 'idx_logs_campaign_date');
            $table->index(['ip_address', 'created_at'], 'idx_logs_ip_date');
            $table->index(['visit_url', 'created_at'], 'idx_logs_url_date');
        });

        // Add foreign key for referral_code if referrals table exists
        if (Schema::hasTable("tracker_referrals")) {
            Schema::table("tracker_logs", function (Blueprint $table) {
                $table->foreign("referral_code")->references("code")->on("tracker_referrals")->onDelete("set null");
            });
        }
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
        
        Schema::dropIfExists("tracker_logs");
    }
};
