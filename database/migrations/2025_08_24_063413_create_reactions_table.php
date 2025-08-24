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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->onDelete('cascade');
            $table->string('user_identifier'); // 今は認証なしなので、識別子を使用
            $table->string('type'); // like,favorite,amazing,want_to_read
            $table->timestamps();

            // 同じユーザーが同じ小説に同じ反応を複数回出来ないように
            $table->unique(['novel_id','user_identifier','type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
