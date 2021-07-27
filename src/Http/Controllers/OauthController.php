<?php

namespace ZhenMu\LaravelOauth\Http\Controllers;

use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use ZhenMu\LaravelInitTemplate\Http\Controllers\BaseController;
use ZhenMu\LaravelOauth\Entities\WechatOauth;
use ZhenMu\LaravelOauth\Http\Resources\OauthProfileResource;
use ZhenMu\LaravelOauth\Models\Oauth;
use ZhenMu\LaravelOauth\Repositories\OauthRepository;

class OauthController extends BaseController
{
    protected $repository;

    public function __construct(OauthRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getOfficialApp()
    {
        /** @var \EasyWeChat\OfficialAccount\Application $app */
        $app = \EasyWeChat::officialAccount();

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
                return $this->fail($oauthModel->platform_desc);
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
            default:
                return $this->fail($oauthModel->platform_desc);
        }

        $oauthModel = $this->repository->updateOrCreate($oauth);

        return OauthProfileResource::make($oauthModel);
    }

    public function bindUserInfo()
    {
        
    }
}