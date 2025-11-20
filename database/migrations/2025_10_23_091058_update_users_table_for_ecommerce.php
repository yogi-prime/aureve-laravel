<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('avatar');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('pincode')->nullable()->after('country');
            $table->boolean('is_active')->default(true)->after('pincode');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->json('preferences')->nullable()->after('last_login_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'date_of_birth', 'gender', 'address',
                'city', 'state', 'country', 'pincode', 'is_active',
                'last_login_at', 'preferences'
            ]);
        });
    }
};