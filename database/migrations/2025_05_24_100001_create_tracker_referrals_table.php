<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("tracker_referrals", function (Blueprint $table) {
            $table->id();
            $table->string("title", 128);
            $table->string("code", 32)->unique();
            $table->text("description")->nullable();
            $table->boolean("status")->default(true);
            $table->integer("position")->default(0);
            $table->timestamp("expires_at")->nullable();
            $table->unsignedBigInteger("created_by")->nullable();
            $table
                ->foreign("created_by")
                ->references("id")
                ->on("users")
                ->onDelete("set null");
            $table->unsignedBigInteger("updated_by")->nullable();
            $table
                ->foreign("updated_by")
                ->references("id")
                ->on("users")
                ->onDelete("set null");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("tracker_referrals");
    }
};
