<?php

namespace App\Domain;

use App;

class Auth
{
    public static function createNonce()
    {
        $time = time();
        $nonce = sha1($time . "~memori~" . mt_rand(0, $time));
        App::$db->insert("nonce", [
            "createdAt" => $time,
            "value" => $nonce
        ]);
        return $nonce;
    }

    public static function clearExpiredNonce()
    {
        App::$db->delete("nonce", ["createdAt[<]" => time() - 10]);
    }
    public static function isNonceValid(string $nonce)
    {
        Auth::clearExpiredNonce();
        App::$db->delete("nonce", ["value" => $nonce]);        
        return App::$db->delete("nonce", ["value" => $nonce])->rowCount()>0;
    }
    /**
     * 判断 token 是否有效
     *
     * @param string $token
     * @return boolean 如果无效返回 false, 否则返回用户 id.
     */
    public static function isTokenValid(string $token){
        $ret = App::$db->get("token", ["createdAt","userId"],["value"=>$token]);
        if(!$ret){
            return false;
        }
        if(time() - $ret["createdAt"] > App::$config->get("auth.time.tokenExpired", 1296000)){
            return false;
        }
        return $ret["userId"];
    }

    /**
     * 检查登录签名
     *
     * @param string $email
     * @param string $nonce
     * @param string $signToCheck
     * @return bool 是否和预期一致(即是否正确), 是为true
     */
    public static function isEmailLoginValid(string $email, string $nonce, string $signToCheck)
    {
        $user = App::$db->get("user", ["id","password"], ["email" => $email]);        
        $expectedSign = sha1($email. $nonce. $user->password);        
        return $expectedSign == $signToCheck?$user->id:false;
    }


    /**
     * 为用户创建 token
     *
     * @param string $email
     * @return string token
     */
    public static function createToken(string $userId)
    {
        $token = sha1(random_bytes(40));
        App::$db->insert("token",["userId"=>$userId, "createdAt"=>time(), "value"=>$token]);        
        return $token;
    }
   
    /**
     * 判断邮箱是否可用
     *
     * @param string $email
     * @return boolean 可用为 true
     */
    public static function isEmailAvailable(string $email)
    {
        return !App::$db->has("user",["email"=>$email]);
    }

    /**
     * 通过邮箱注册用户
     * 
     * @param string $username
     * @param string $email
     * @param string $password 客户端混淆过的密码
     * @return int id
     */
    public static function registerUserByEmail(string $email, string $password)
    {
        $ts = time();
        App::$db->insert("user",[
            "email"=>$email,
            $password=>sha1("memori$ts$password"),
            "role"=>"user",
            "displayName"=>"昵称未设置",
            "createdAt"=> time()
        ]);
        return App::$db->id();
    }

    /**
     * 通过邮箱清理token
     */
    public static function clearAllToken($userId)
    {
        App::$db->delete("token", ["userId"=>$userId]);
    }
    public static function clearToken($token)
    {
        App::$db->delete("token", ["value"=>$token]);
    }
    // TODO: fill this func
    public static function getLastEmailVerificationCode($email){
        return '111111';
    }
}
