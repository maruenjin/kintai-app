<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('attendance_applications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('attendance_id');
            $t->unsignedBigInteger('user_id'); 
            $t->tinyInteger('type')->default(1); 
            $t->date('work_date')->nullable();
            $t->dateTime('clock_in')->nullable();
            $t->dateTime('clock_out')->nullable();
            $t->text('note')->nullable();

            $t->tinyInteger('status')->default(0); 
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->text('manager_comment')->nullable();
            $t->timestamps();

            $t->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_applications'); }
} 