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
        });
        Schema::dropIfExists("tracker_logs");
    }
};
