<?php
/**
 * 前台登录退出
 */

namespace app\index\controller;

use app\index\exception\IndexServiceException;
use app\common\validate\UserValidate;
use app\index\service\AuthService;
use think\Response;
use think\response\Redirect;
use think\Request;
use Exception;

class AuthController extends IndexBaseController
{
    protected array $loginExcept=[
        'index/auth/login',
        'index/auth/register',
    ];

    /**
     * 登录
     * @param Request $request
     * @param AuthService $service
     * @param UserValidate $validate
     * @return string|Response|Redirect
     * @throws Exception
     */
    public function login(Request $request, AuthService $service, UserValidate $validate)
    {
        $param = $request->param();

        //登录逻辑
        if($request->isPost()){

            $check = $validate->scene('index_login')->check($param);
            if (!$check) {
                return index_error($validate->getError());
            }
            try {
                $user = $service->login($param['username'], $param['password']);
                self::authLogin($user,(bool)($param['remember']??false));
                return index_success('登录成功','index/index',$user);
            } catch (IndexServiceException $e) {
                return  index_error($e->getMessage());
            }
        }

        return $this->fetch();
    }

    /**
     * 注册
     * @param Request $request
     * @param AuthService $service
     * @param UserValidate $validate
     * @return string|Response|Redirect
     * @throws Exception
     */
    public function register(Request $request, AuthService $service, UserValidate $validate)
    {
        $param = $request->param();

        //登录逻辑
        if($request->isPost()){
            $check = $validate->scene('index_register')->check($param);
            if (!$check) {
                return index_error($validate->getError());
            }
            try {
                $user = $service->register($param['username'], $param['password']);
                if($user->isExists()) {
                    return $this->fetch('login');
                }
            } catch (IndexServiceException $e) {
                return index_error($e->getMessage());
            }
        }
        return $this->fetch();
    }

    /**
     * 退出
     * @return Redirect
     */
    public function logout(): Redirect
    {
        self::authLogout();

        return redirect(url('index/auth/login'));
    }
}
