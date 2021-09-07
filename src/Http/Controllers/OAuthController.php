<?php

namespace ZhenMu\LaravelOauth\Http\Controllers;

use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use ZhenMu\LaravelInitTemplate\Http\Controllers\BaseController;
use ZhenMu\LaravelInitTemplate\Repositories\UserRepository;
use ZhenMu\LaravelInitTemplate\Services\Verify;
use ZhenMu\LaravelOauth\Entities\WechatMiniProgramOauth;
use ZhenMu\LaravelOauth\Entities\WechatOauth;
use ZhenMu\LaravelOauth\Events\OAuthUserRegisterEvent;
use ZhenMu\LaravelOauth\Http\Resources\OauthProfileResource;
use ZhenMu\LaravelOauth\Http\Resources\OAuthResource;
use ZhenMu\LaravelOauth\Models\Oauth;
use ZhenMu\LaravelOauth\Repositories\OauthRepository;

class OAuthController extends BaseController
{
    protected $repository;

    public function __construct(OauthRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getOfficialApp()
    {
        /** @var \EasyWeChat\OfficialAccount\Application $app */
        $app = \EasyWeChat\Factory::officialAccount(array_merge(config('wechat.defaults'), config('wechat.official_account.default')));

        return $app;
    }

    protected function getMiniprogramApp()
    {
        if (is_null(config('wechat.mini_program.default'))) {
            throw new \LogicException('请补充小程序配置信息');
        }

        /** @var \EasyWeChat\MiniProgram\Application $app */
        $app = \EasyWeChat\Factory::miniProgram(array_merge(config('wechat.defaults'), config('wechat.mini_program.default')));

        return $app;
    }

    public function redirect(int $platform)
    {
        \request()->validate([
            'required_url' => ['nullable', 'string'],
        ]);

        $oauthModel = new Oauth(['platform' => $platform]);

        switch ($platform) {
            case Oauth::PLATFORM_OFFICIAL:
                return redirect($this->getOfficialApp()->oauth->redirect(\request()->get('redirect_url')));
            default:
                return $this->fail(sprintf("申请授权失败 platform: %s", $oauthModel->platform_desc));
        }
    }

    public function updateInfo(int $platform)
    {
        \request()->validate([
            'code' => ['required', 'string'],
        ]);

        $oauthModel = new Oauth(['platform' => $platform]);

        switch ($platform) {
            case Oauth::PLATFORM_OFFICIAL:
                try {
                    $oauthInfo = $this->getOfficialApp()->oauth->userFromCode(\request()->get('code'));

                    /** @var \ZhenMu\LaravelOauth\Contracts\OauthContract $oauth */
                    $oauth = new WechatOauth($this->getOfficialApp()->getConfig()['app_id'], $oauthInfo->getRaw());
                } catch (AuthorizeFailedException $e) {
                    return $this->fail("{$oauthModel->platform_desc} 授权失败，请稍后重试。原因：{$e->body['errmsg']}");
                }
                break;
            case Oauth::PLATFORM_MINI_PROGRAM:
                try {
                    // session_key, openid
                    $oauthInfo = $this->getMiniprogramApp()->auth->session(\request()->get('code'));

                    $decryptData = $this->getMiniprogramApp()->encryptor->decryptData($oauthInfo['session_key'], \request()->get('iv'), \request()->get('encryptedData'));

                    $oauthInfoData = $oauthInfo + $decryptData;

                    /** @var \ZhenMu\LaravelOauth\Contracts\OauthContract $oauth */
                    $oauth = new WechatMiniProgramOauth($this->getMiniprogramApp()->getConfig()['app_id'], $oauthInfoData);
                } catch (\Throwable $e) {
                    return $this->fail("{$oauthModel->platform_desc} 授权失败，请稍后重试。原因：{$e->body['errmsg']}");
                }
                break;
            default:
                return $this->fail(sprintf("更新授权信息失败 platform: %s", $oauthModel->platform_desc));
        }

        $oauthModel = $this->repository->updateOrCreate($oauth);

        return OauthProfileResource::make($oauthModel);
    }

    public function bindUserInfo(Verify $verify, UserRepository $userRepository)
    {
        \request()->validate([
            'sms_code' => ['required',],
            'mobile' => ['required', 'regex:/^1(3|4|5|6|7|8|9)(\d){9}$/'],
            'mobile_code' => ['nullable', 'string',],
            'type' => ['nullable', 'string',],

            'oauth_id' => ['nullable', 'integer'],
        ]);

        $verify->validate(
            \request()->get('type', 'user'),
            \request()->get('mobile'),
            \request()->get('sms_code'),
            \request()->get('mobile_code')
        );

        $oauth = Oauth::query()->find(\request()->get('oauth_id'));

        /** @var \App\Models\User $user */
        $user = $userRepository->findByMobile($mobile = \request()->get('mobile'));

        // 创建新用户
        if (empty($user)) {
            event(new OAuthUserRegisterEvent(\request()->all(), $oauth));

            if ($oauth) {
                $oauth->refresh();
                $user = $oauth->user;
            }
        } else {
            $oauth->user_id = $user->id;
            $oauth->save();
        }

        // 无授权信息，无用户信息
        if (is_null($oauth) && is_null($user)) {
            throw new \RuntimeException('未授权第三方平台信息');
        }

        if (is_null($user)) {
            throw new \LogicException('请完成创建用户的业务逻辑');
        }

        // h5 用户登录
        if (empty($oauth) && $user) {
            return OAuthResource::make([
                'oauth' => $oauth,
                'user' => $user,
                'access_token' => $user->jwt_token ?? null
            ]);
        }

        // 微信用户登录
        return OAuthResource::make([
            'oauth' => $oauth,
            'user' => $user,
            'access_token' => $user->jwt_token ?? null
        ]);
    }
}