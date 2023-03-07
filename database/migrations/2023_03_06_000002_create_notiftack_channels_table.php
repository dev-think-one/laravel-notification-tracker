<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('notification-tracker.tables.channels'), function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->foreignId('notification_id')
                ->nullable()
                ->constrained(config('notification-tracker.tables.notifications'), 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('channel')->index();
            $table->longText('route')->nullable();
            // in 99% will be same as created_at, but we use different column to be sure this is correct data;
            $table->dateTime('sent_at');
            $table->dateTime('first_open_at')->nullable()->index();
            $table->dateTime('last_open_at')->nullable();
            $table->unsignedBigInteger('open_count')->default(0)->index();
            $table->dateTime('first_click_at')->nullable()->index();
            $table->dateTime('last_click_at')->nullable();
            $table->unsignedBigInteger('click_count')->default(0)->index();
            $table->json('stats')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('notification-tracker.tables.channels'));
    }
};
