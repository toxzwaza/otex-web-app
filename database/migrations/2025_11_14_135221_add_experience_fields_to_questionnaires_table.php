<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            // 注湯体験関連
            $table->boolean('casting_experience')->default(false)->comment('注湯体験フラグ');
            $table->string('casting_staff')->nullable()->comment('注湯体験対応者');
            
            // 砂込め体験関連
            $table->boolean('sand_experience')->default(false)->comment('砂込め体験フラグ');
            $table->string('sand_staff')->nullable()->comment('砂込め体験対応者');
            
            // メモ
            $table->text('memo')->nullable()->comment('メモ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn([
                'casting_experience',
                'casting_staff',
                'sand_experience', 
                'sand_staff',
                'memo'
            ]);
        });
    }
};
