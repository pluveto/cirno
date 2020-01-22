<?php

/**
 * This is a sample file. you can delete it.
 */

namespace App\Api;

use App;
use App\Domain\Auth as Domain;
use App\Type\Exception\BadRequestException;
use App\Validator\StaticValidator;

class Auth
{
    /**
     * @api {get} /auth/nonce 获取 nonce
     * @apiName getNonce
     * @apiGroup Auth
     * @apiVersion 0.2
     * @apiPermission none
     * @apiSuccess {String} nonce 获得的随机串.
     * @apiSuccessExample 成功响应:
        {
        "nonce": "c65a7eeb9f7ea4db282c2eba674e3705ff8747ac"
        }
     */
    public function getNonce()
    {
        App::$api->json([
            "nonce" => Domain::createNonce()
        ]);
    }


    /**
     * @api {post} /auth/login/email 通过邮箱登录
     * @apiVersion 2.0.0
     * @apiPermission none
     * @apiName loginByEmail
     * @apiGroup Auth
     *
     * @apiParam {String} email 邮箱地址.
     * @apiParam {String} nonce  随机串.
     * @apiParam {String} sign  登录签名, 算法为 sign = sha1(nonce + email + sha1('memori' + password)).
     *
     * @apiSuccess {String} token 有效期为15天(注销自动过期)的token.
     *
     */
    public function loginByEmail()
    {
        /** ------- email validating ------- */
        // 检查格式
        $email = App::$api->data["email"];
        $email = trim($email);
        if (!\App\Validator\StaticValidator::checkEmailFormat($email)) {
            throw new BadRequestException('邮箱格式错误');
        }
        // 检查可用性(重复)
        if (Domain::isEmailAvailable($email)) {
            throw new BadRequestException('邮箱尚未注册');
        }
        // TODO: 登录次数记录, 防止暴力破解. 多次错误之后要求验证码登录.
        /** ------- nonce validating ------- */
        if (!Domain::isNonceValid($this->nonce)) {
            throw new BadRequestException('请求随机串错误');
        }
        $userId = 0;
        /** ------- 密码检查 ------- */
        if (!($userId = Domain::isEmailLoginValid($email, $this->nonce, $this->sign))) {
            throw new BadRequestException('请求签名错误');
        }
        App::$api->json([
            "userId" => $userId,
            "token" => Domain::createToken($userId)            
        ]);
    }
 
    /**
     * 用户邮箱注册
     * 
     * 注册时将不可避免地用明文传参, 除非用非对称加密. 所以建议开启SSL.
     * 
     * 所需参数: username, email, captch, password
     * 
     * @return void
     */
    /**
     * @api {post} /auth/register/email 邮箱注册
     * @apiDescription 注册时, 请求体将不可避免地用明文传参.所以建议开启SSL.
     * @apiVersion 2.0.0
     * @apiPermission none
     * @apiName registerByEmail
     * @apiGroup Auth
     *
     * @apiParam {String} username  用户名.
     * @apiParam {String} email  用户邮箱.
     * @apiParam {String} captch  验证码.
     * @apiParam {String} password  密码.
     *
     */
    public function registerByEmail()
    {
        $email = App::$api->data["email"];
        $password = App::$api->data["password"];
        /** ============== 格式检查 ============== */
        /** ------- 邮箱检查   ------- */
        $email = trim($email);
        if (!StaticValidator::checkEmailFormat($email)) {
            throw new BadRequestException('邮箱格式错误, 必须形如 username@website.domain 的格式');
        }
        /** ============== 正式检查 ============== */
        // 检查可用性(重复)
        if (!Domain::isEmailAvailable($email)) {
            throw new BadRequestException('邮箱已被使用');
        }
        /** ------- captch validating ------- */
        $verificationCode = App::$api->data["verificationCode"];
        $verificationCode = trim($verificationCode);
        $correctCode = Domain::getLastEmailVerificationCode($email);
        if ($correctCode == null) {
            throw new BadRequestException('未发送验证码');
        }
        if ($this->captch != $correctCode) {
            throw new BadRequestException('验证码错误或过期');
        }
        /** ------- password validating ------- */
        // 密码不进行 trim()
        /** ------- 完成注册 ------- */
        $userId = Domain::registerUserByEmail($email, $password);
        App::$api->json([
            "userId" => $userId,
            "token" => Domain::createToken($userId)            
        ]);
    }


    /**
     * @api {post} /auth/logout 退出登录
     * @apiDescription 退出登录, 并清除登录凭据(token).
     * @apiVersion 2.0.0
     * @apiName logout
     * @apiPermission user
     * @apiGroup Auth
     */
    public function logout()
    {        
        var_dump("xx");
        $token = App::$api->data["token"];
        Domain::clearToken($token);
    }
}
