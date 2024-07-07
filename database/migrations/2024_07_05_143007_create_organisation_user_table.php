<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->uuid('orgId');
            $table->uuid('userId');
            
            $table->foreign('orgId')->references('orgId')->on('organisations')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->primary(['orgId', 'userId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_user');
    }
};
