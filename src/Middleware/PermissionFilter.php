<?php

namespace App\Middleware;

use App;
use App\Type\Exception\BadRequestException;
use \Firebase\JWT\JWT;

/**
 * 权限过滤中间件
 */
class PermissionFilter implements IMiddleware
{

    /**
     * 当前的用户. 在 roleCheck() 函数里进行的赋值.
     *
     * @var array|object
     */
    public static $currentUser = null;
    /**
     * 路由 <-> 用户组 表
     *
     * @var array
     */
    public $permissionMap = [];
    /**
     * 用户组 <-> 权限 表
     *
     * @var array
     */
    public $roleMap = [];
    /**
     * 路由参数规则表
     *
     * @var array
     */
    public $ruleMap = [];

    /**
     * 权限过滤中间件的初始化方法
     * 
     * 1. 本函数将为此中间件的各个属性赋值
     * 2. 在所有 API 具体函数执行前会执行过滤器挂载的方法
     * 3. 将会检查参数是否符合规则要求 (规则文件: src/common/config/rule.php, 由生成器(generator.php)生成)
     * 4. 将会检查用户权限是否能够访问想要访问的路由 (规则文件: src/common/config/permission.php, 由生成器(generator.php)生成)
     *
     * @return void
     */
    public function init()
    {

        $this->permissionMap = App::$config->get("permission", []);
        $this->roleMap = App::$config->get("role", ['none' => 0]);
        $this->ruleMap = App::$config->get("rule", ['none' => 0]);

        /**
         * 挂载过滤器到 API
         */
        App::$api->before(
            'start',
            function (&$params, &$output) {

                // 如果路由不存在, 直接报错
                $route = App::$api->router()->route(App::$api->request());
                if (!$route) {
                    throw new BadRequestException("API not found or method not allowed.", 404);
                }
                $pattern = $route->pattern; // such as string(15) "/auth/nonce/@as"
                $this->paramCheck($pattern);
                $this->roleCheck($pattern);
            }
        );
    }
    /**
     * 参数基础静态检查, 检查输入的参数是否缺失, 长度是否在范围内等等.
     * 如果遇到任何问题, 就抛出相应错误
     * 
     * TODO: 上传文件检查
     * 
     * @param string $pattern 用户请求的 api 的路由 
     * @return void
     */
    private function paramCheck($pattern)
    {
        $form =
            App::$api->request()->method == "GET" ?
            App::$api->request()->query :
            App::$api->request()->data;

        // 如果没有给该路由设置规则, 就直接放行
        if (!array_key_exists($pattern, $this->ruleMap)) {
            return;
        }
        // 依次检查各条规则
        $ruleList = $this->ruleMap[$pattern]["param"];
        foreach ($ruleList as $param => $rules) {

            /**
             * 首先可基本分为四种情况:
             * 
             *  1. 参数必要, 表单有 -> 继续处理
             *  2. 参数非必要, 表单有 -> 继续处理
             *  3. 参数必要, 表单无 -> 跳过
             *  3. 参数非必要, 表单无 -> 跳过
             * 
             *  注: 不考虑参数必要但有默认值这种情况. 有默认值就当作参数非必要处理
             * 
             * 因此下面先写跳过的两种情况
             */

            // 检查是否缺少必要参数
            if (array_get_if_key_exists($rules, "required", false)) {
                if (!isset($form->$param)) {
                    throw new BadRequestException("Mising required parameter.");
                }
                if (empty($form->$param)) {
                    throw new BadRequestException("Parameter `$param` is given but it has empty value.");
                }
            }

            // 如果参数是非必要参数, 且提交的表单没有这个参数, 那么就跳过
            if (!array_get_if_key_exists($rules, "required", false) && !isset($form->$param)) {
                // 如果有默认值就赋予一个默认值
                if ($default = array_get_if_key_exists($rules, "default", false)) {
                    App::$api->request()->method == "GET" ?
                        App::$api->request()->query[$param] = $default :
                        App::$api->request()->data[$param] = $default;
                }
                continue;
            }
            /**
             * --> options 检查
             */
            $options = array_get_if_key_exists($rules, "options", []);
            if (count($options)) {
                if (in_array($form->$param, $options)) continue;
                throw new BadRequestException("Parameter `$param` gives unacceptable value. Enum value expected. ");
            }
            /**
             * --> type 检查
             */
            if (!$type = array_get_if_key_exists($rules, "type", false)) {
                continue;
            }
            switch ($type) {
                case 'integer':
                    if (!is_numeric($form->$param)) {
                        throw new BadRequestException("Parameter `$param` is expected to be a integer. ");
                    }
                    $number = intval($form->$param);
                    $min = array_get_if_key_exists($rules, "min", 0);
                    $max = array_get_if_key_exists($rules, "max", 0);
                    if ($min && $number < $min) {
                        throw new BadRequestException("Parameter `$param` is expected to be more than $min. ");
                    }
                    if ($max && $number > $max) {
                        throw new BadRequestException("Parameter `$param` is expected to be less than $max. ");
                    }
                    break;
                case 'string':
                    $min = array_get_if_key_exists($rules, "min", 0);
                    $max = array_get_if_key_exists($rules, "max", 0);
                    $strLength = mb_strlen($form->$param);
                    if ($min && $strLength < $min) {
                        throw new BadRequestException("Parameter `$param` is expected to be more than $min. ");
                    }
                    if ($max && $strLength > $max) {
                        throw new BadRequestException("Parameter `$param` is expected to be less than $max. ");
                    }
                    break;
                case 'boolean':
                    if (!is_bool($form->$param)) {
                        throw new BadRequestException("Parameter `$param` is expected to be a boolean (true / false). ");
                    }
                    break;
                case 'array':
                    // 复杂类型不进行检查, 交给 API 层处理
                default:
                    break;
            }
        }
    }
    /**
     * 用户角色检查. 检查用户是否有调用期望 API 的权限, 
     * 如果没有权限就抛出异常结束请求, 如果有权限就设置好当前用户并放行.
     *
     * @param string $pattern 用户请求的 api 的路由
     * @return void
     */
    private function roleCheck($pattern)
    {
        $map = $this->permissionMap;
        // roleRequired 是该路由的理论最低权限
        $roleRequired = array_key_exists($pattern, $map) ? $map[$pattern] : "none";

        // 如果 API 不需要任何权限, 则跳过检查.
        if ($roleRequired == "none") {
            return;
        }
        // jwt 签名
        $sign =  App::$api->request()->data["sign"];
        if (null == $sign) {
            throw new BadRequestException("Permission denied for missing signature.");
        }
        /**
         * sign 结构: [
         *   createdAt: (timestamp)
         *   userId:
         *   token:
         * ]
         */
        $decoded = NULL;
        try {
            $decoded = JWT::decode($sign, App::$config->get("keys.publicKey"), array('RS256'));
        } catch (\UnexpectedValueException $th) {
            throw new BadRequestException("Invalid sign.");
        }
        // 超过十秒的请求就丢弃
        if (time() - $decoded->createdAt > 10) {
            throw new BadRequestException("Timeout.");
        }
        $userId = \App\Domain\Auth::isTokenValid($decoded->token);
        if (!$userId) {
            throw new BadRequestException("Invalid token.");
        }
        $this->currentUser = \App\Domain\User::get($userId);
        if (!array_key_exists($this->currentUser->role, $this->roleMap)) {
            throw new BadRequestException("Unknown user role.");
        }
        if ($this->roleMap[$roleRequired] > $this->roleMap[$this->currentUser->role]) {
            throw new BadRequestException("Permission denied for user role.");
        }
    }
}
