<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->string('type')->default('String');
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->timestamps();
            $table->foreign('group_id')
                ->references('id')->on('config_groups')
                ->onUpdate('cascade')
                ->onDelete('cascade')
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_entries');
    }
}
;
