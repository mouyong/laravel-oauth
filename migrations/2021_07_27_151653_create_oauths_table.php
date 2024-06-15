<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthsTable extends Migration
{
    use \ZhenMu\LaravelInitTemplate\Traits\RemarkMapTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauths', function (Blueprint $table) {
            /** @var \ZhenMu\LaravelOauth\Models\Oauth $model */
            $model = config('laravel-oauth.oauth_model', \ZhenMu\LaravelOauth\Models\Oauth::class);

            $table->id();
            $table->unsignedBigInteger(config('laravel-oauth.user_foreign_key', 'user_id'))->nullable()->comment('用户 id：users.id');
            $table->string('app_id')->nullable()->comment('第三方平台的 app id');
            $table->string('platform')->comment($this->getRemarkByMap('登录平台', $model::PLATFORM_MAP));
            $table->string('open_id')->comment('用户微信 openid');
            $table->string('union_id')->nullable()->comment('unionid');
            $table->string('session_key')->nullable()->comment('session_key');
            $table->string('nickname')->comment('用户昵称');
            $table->string('gender')->default($model::GENDER_UNKNOWN)->comment($this->getRemarkByMap('性别', $model::GENDER_MAP));
            $table->string('avatar')->comment('用户头像');
            $table->string('country')->comment('用户所在国家');
            $table->string('province')->comment('用户所在省份');
            $table->string('city')->comment('用户所在城市');
            $table->json('original')->nullable()->comment('用户授权的原始信息');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauths');
    }
}
