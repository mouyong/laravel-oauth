<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->default(0)->after('id')->comment('用户 id，users.id');
            $table->string('realname')->nullable()->after('name')->comment('用户真实姓名');
            $table->string('mobile')->nullable()->after('realname')->comment('用户手机号');
            $table->string('avatar')->nullable()->after('mobile')->comment('用户头像');
            $table->string('id_card')->nullable()->after('avatar')->comment('用户身份证号');
            $table->string('ip')->nullable()->after('remember_token')->comment('用户访问 ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['parent_id']);
            $table->dropColumn(['realname']);
            $table->dropColumn(['mobile']);
            $table->dropColumn(['avatar']);
            $table->dropColumn(['id_card']);
            $table->dropColumn(['ip']);
        });
    }
}
