<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('emails_enabled')->default(true);
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('email_settings')->insert([
            'emails_enabled' => true,
            'updated_by_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mail_type', 80);
            $table->string('recipient_email');
            $table->string('subject');
            $table->string('status', 20);
            $table->string('skip_reason', 80)->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_settings');
    }
};
