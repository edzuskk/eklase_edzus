<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('grades');
        
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject')->constrained('subjects')->onDelete('cascade');
            $table->integer('grade');
            $table->timestamps();

            $table->unique(['student_id', 'subject']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('grades');
    }
};