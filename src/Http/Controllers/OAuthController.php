<?php

namespace ZhenMu\LaravelOauth\Http\Controllers;

use EasyWeChat\MiniApp\Decryptor;
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
        $config = array_merge(config('wechat.defaults'), config('wechat.official_account.default'));

        /** @var \EasyWeChat\OfficialAccount\Application $app */
        $app = new \EasyWeChat\OfficialAccount\Application($config);

        return $app;
    }

    protected function getMiniApp()
    {
        if (is_null(config('wechat.mini_program.default'))) {
            throw new \LogicException('请补充小程序配置信息');
        }

        $config = array_merge(config('wechat.defaults'), config('wechat.mini_program.default'));

        /** @var \EasyWeChat\MiniApp\Application $app */
        $app = new \EasyWeChat\MiniApp\Application($config);

        return $app;
    }

    public function redirect(int $platform)
    {
        \request()->validate([
            'required_url' => ['nullable', 'string'],
        ]);

        $oauthModel = new Oauth(['platform' => $platform]);

        $redirectUrl = \request()->get('redirect_url');

        $callbackUrl = \route('oauth.update-info', ['platform' => $platform]);

        $oauthUrl = $callbackUrl;
        if ($redirectUrl) {
            $oauthUrl = sprintf('%s?redirect_url=%s', $callbackUrl, urlencode($redirectUrl));
        }

        switch ($platform) {
            case Oauth::PLATFORM_OFFICIAL:
                $url = $this->getOfficialApp()->getOAuth()->redirect($oauthUrl);
                break;
            default:
                return $this->fail(sprintf("申请授权失败 platform: %s", $oauthModel->platform_desc));
        }

        return redirect($url);
    }

    public function updateInfo(int $platform)
    {
        \request()->validate([
            'code' => ['required', 'string'],
            'redirect_url' => ['nullable', 'string'],
        ]);

        $oauthModel = new Oauth(['platform' => $platform]);

        switch ($platform) {
            case Oauth::PLATFORM_OFFICIAL:
                try {
                    $oauthInfo = $this->getOfficialApp()->getOAuth()->userFromCode(\request()->get('code'));

                    /** @var \ZhenMu\LaravelOauth\Contracts\OauthContract $oauth */
                    $oauth = new WechatOauth($this->getOfficialApp()->getAccount()->getAppId(), $oauthInfo->getRaw());
                } catch (AuthorizeFailedException $e) {
                    return $this->fail("{$oauthModel->platform_desc} 授权失败，请稍后重试。原因：{$e->body['errmsg']}");
                }
                break;
            case Oauth::PLATFORM_MINI_PROGRAM:
                try {
                    $app = $this->getMiniApp();;

                    $resp = $app->getClient()->post('/sns/jscode2session', [
                        'query' => [
                            'appid' => $app->getAccount()->getAppId(),
                            'secret' => $app->getAccount()->getSecret(),
                            'js_code' => \request()->get('code'),
                            'grant_type' => 'authorization_code',
                        ]
                    ]);

                    // session_key, openid
                    $oauthInfo = $resp->toArray();
                    if (empty($oauthInfo['session_key'])) {
                        throw new \Exception($oauthInfo['errmsg'], $oauthInfo['errcode']);
                    }

                    $decryptData = Decryptor::decrypt($oauthInfo['session_key'], \request()->get('iv'), \request()->get('encryptedData'));

                    $oauthInfoData = $oauthInfo + $decryptData;

                    /** @var \ZhenMu\LaravelOauth\Contracts\OauthContract $oauth */
                    $oauth = new WechatMiniProgramOauth($this->getMiniApp()->getAccount()->getAppId(), $oauthInfoData);
                } catch (\Throwable $e) {
                    return $this->fail("{$oauthModel->platform_desc} 授权失败，请稍后重试。原因：{$e->getMessage()}");
                }
                break;
            default:
                return $this->fail(sprintf("更新授权信息失败 platform: %s", $oauthModel->platform_desc));
        }

        $oauthModel = $this->repository->updateOrCreate($oauth);


        // webview 授权
        if (in_array($oauthModel->platform, [Oauth::PLATFORM_OFFICIAL])) {
            // 解析前端 url
            $redirectUrl = \request()->get('redirect_url', \request()->root());
            $pathInfo = parse_url($redirectUrl);
            $query = [];
            if ($pathInfo['query'] ?? null) {
                parse_str($pathInfo['query'], $query);
            }

            // 拼接前端跳转参数
            $query['oauth_id'] = $oauthModel->getKey();
            $query['user_id'] = $oauthModel->user_id;
            $query['access_token'] = $oauthModel->user->jwt_token ?? null;

            switch ($pathInfo['scheme']) {
                case 'https':
                    $port = $pathInfo['port'] ?? 443;
                    break;
                case 'http':
                default:
                    $port = $pathInfo['port'] ?? 80;
                    break;
            }

            $url = sprintf('%s://%s:%s?%s', $pathInfo['scheme'], $pathInfo['host'], $port, http_build_query($query));

            return redirect($url);
        }

        // 已有绑定的用户信息，直接登录
        if ($oauthModel->user_id) {
            return OAuthResource::make([
                'oauth' => $oauthModel,
                'user' => $oauthModel->user,
                'access_token' => $oauthModel->user->jwt_token ?? null
            ]);
        }

        // 返回授权信息，进行下一步绑定
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

        if (is_null($oauth)) {
            throw new \Exception(sprintf('未找到授权信息 %s', \request('oauth_id')));
        }

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
