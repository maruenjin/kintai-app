<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBreaksJsonToAttendanceApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_applications', function (Blueprint $table) {
            $table->json('breaks')->nullable()->after('clock_out');
             $table->index(['user_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_applications', function (Blueprint $table) {
            $table->dropIndex(['user_id','status']);
            $table->dropColumn('breaks');
        });
    }
}
