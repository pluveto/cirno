<?php
// TODO: 按需加载路由
require_once __DIR__ . '/vendor/autoload.php';
$apiRuleExample = [
    "/auth/login" => [
        "param" => [
            "username" => [
                "required" => true,
                "type" => "string", // see: https://www.php.net/manual/en/function.gettype.php        
                "min" => 1,
                "max" => 20,
                "default" => 100,
                "options" => [
                    "bill", "jack"
                ]
            ]
        ]
    ]
];
if (!(isset($argc) && $argc == 2 && $argv[1] == 'run')) {
    echo "Usage: `php generator.php run`\n";
    exit();
}
function varExport($expression, $return = FALSE)
{
    $export = var_export($expression, TRUE);
    $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
    $array = preg_split("/\r\n|\n|\r/", $export);
    $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
    $export = join(PHP_EOL, array_filter(["["] + $array));
    if ((bool) $return) return $export;
    else echo $export;
}
/**
 * 解析参数规则
 * 
 * @param string $str str without `@apiParam `
 * @return array
 */
function getRule($str) //str without `@apiParam `
{
    /**
     * 魔法阵
     */
    $re =
        '/^\s*(?:\(\s*(.+?)\s*\)\s' .
        '*)?\s*(?:\{\s*([a-zA-Z0-9' .
        '()#:\.\/\\\\\[\]_|-]+)\s*' .
        '(?:\{\s*(.+?)\s*\}\s*)?\s' .
        '*(?:=\s*(.+?)(?=\s*\}\s*)' .
        ')?\s*\}\s*)?(\[?\s*([a-zA' .
        '-Z0-9\$\:\.\/\\\\_-]+(?:\[' .
        '[a-zA-Z0-9\.\/\\\\_-]*\])' .
        '?)(?:\s*=\s*(?:"([^"]*)"|' .
        '\'([^\']*)\'|(.*?)(?:\s|' .
        '\]|$)))?\s*\]?\s*)(.*)?$|@/';

    preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

    /**
     * 返回的树
     */
    $retBody = [];

    /**
     * --> 参数的名称
     */
    $paramName = $matches[6][0];

    /**
     * --> type 字段
     */
    $type = "string";
    if ($matches[2][0] && (gettype($matches[2][0]) == "string")) {
        if (substr($type, -strlen($type)) === "[]") {
            $type = "array";
        } else {
            $type = $matches[2][0];
        }
    }
    $in  = ["number",  "object"];
    $out = ["integer", "string"];
    $type = str_replace($in, $out, strtolower($type));

    $retBody["type"] = $type;

    /**
     * --> min/max 字段
     */

    $sizeMin = -1;
    $sizeMax = -1;
    if ($matches[3][0] && (gettype($matches[3][0]) == "string")) {
        [$sizeMin, $sizeMax] = explode($type === "string" ? ".." : "-", $matches[3][0], 2);
    }
    if ($sizeMin != -1)    $retBody["min"] = intval($sizeMin);
    if ($sizeMax != -1)    $retBody["max"] = intval($sizeMax);

    /**
     * --> options 字段
     */

    $options = [];
    $optionsStr = $matches[4][0];
    if ($optionsStr && (gettype($optionsStr) == "string")) {
        $regExp = "";
        if ($optionsStr[0] === '"')
            $regExp = '/\"[^\"]*[^\"]\"/';
        else if ($optionsStr[0] === '\'')
            $regExp = '/\'[^\']*[^\']\'/';
        else
            $regExp = '/[^,\s]+/';
        preg_match($regExp, $optionsStr, $options);
    }
    if (count($options))
        $retBody["options"] = $options;

    /**
     * --> required 字段
     */

    if (!($matches[5][0] && $matches[5][0][0] === '[')) {
        $retBody["required"] = true;
    }

    /**
     * --> default 字段
     */

    $default = null;
    if ($matches[7][0]) $default = $matches[7][0];
    elseif ($matches[8][0]) $default = $matches[8][0];
    elseif ($matches[9][0]) $default = $matches[9][0];

    if ($default) {
        $retBody["default"] = $default;
    }

    /**
     * 完事儿
     */

    return [
        $paramName,
        $retBody
    ];
}
function extractAnnotation($comment)
{
    $httpMethod = "";
    $httpRoute = "";
    $apiPermission = "none";
    $apiRule = [];
    $lines = explode("\n", $comment);
    foreach ($lines as $line) {
        // @api {get} / 欢迎界面
        $line = ltrim($line, " \t/*");

        if (strpos($line, "@api {") === 0) {
            $line = substr($line, strlen("@api {"));
            $blocks = explode("}", $line, 2); // get, / 欢迎界面
            $httpMethod = strtoupper($blocks[0]);
            $blocks[1] = ltrim($blocks[1], " "); /// 欢迎界面
            $subblock = explode(" ", $blocks[1], 2); ///,欢迎界面
            $httpRoute = trim($subblock[0]);
        }
        if (strpos($line, "@apiPermission ") === 0) {
            $line = substr($line, strlen("@apiPermission ")); // user
            $apiPermission = trim($line);
        }
        if (strpos($line, "@apiParam ") === 0) {
            $line = substr($line, strlen("@apiParam ")); // user
            $rule = getRule($line);
            $apiRule[$rule[0]] = $rule[1];
        }
    }
    return [$httpMethod, $httpRoute, $apiPermission, $apiRule];
}

echo "generating route...\n";
$routeFilename = 'src/common/route.php';
$permissionFilename = 'src/common/config/permission.php';
$ruleFilename = 'src/common/config/rule.php';
$fRoute = fopen($routeFilename, 'w');
$fPerms = fopen($permissionFilename, 'w');
$fRule = fopen($ruleFilename, 'w');
fwrite($fRoute, '
<?php
/**
 * Route table. Generated by `' . __FILE__ . '` at ' . date("Y-m-d H:i:s") . '.
 * YOU DON\'T HAVE TO MODIFY THIS FILE, JUST GENERATE IT :)
 */

');
fwrite($fPerms, "<?php\n/*\n    API Permissions\n    Auto generated at " . date("Y-m-d H:i:s") . "\n*/\nreturn[\n");
fwrite($fRule, "<?php\n/*\n    API Parameters rules\n    Auto generated at " . date("Y-m-d H:i:s") . "\n*/\nreturn ");
$apiRuleList = [];
foreach (glob('src/Api/*.php') as $file) {
    require_once $file;
    // get the file name of the current file without the extension
    // which is essentially the class name
    $className = basename($file, '.php');
    $fullClassName = "\App\Api\\" . $className;
    echo "found file: $file\n";
    if (!class_exists($fullClassName)) {
        continue;
    }
    echo "----->found class: \e[1;36m$fullClassName\e[0m\n";
    fwrite($fRoute, "\$api$className = new $fullClassName();\n");
    $reflector = new ReflectionClass($fullClassName);
    $functions = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
    for ($i = 0; $i < count($functions); $i++) {
        $function = $functions[$i];
        $functionName = $function->getName();
        echo "------->found api: \e[1;35m$functionName\e[0m ";
        $comment = $function->getDocComment();
        [$httpMethod, $httpRoute, $apiPermission, $apiRule] = extractAnnotation($comment);
        if (is_array($apiRule) && count($apiRule)) {
            $apiRuleList["$httpRoute"]["param"] = $apiRule;
        }
        echo "route: $httpRoute\n";
        if ($httpRoute) {
            fwrite($fRoute, "Flight::route('$httpMethod $httpRoute', array(\$api$className, '$functionName'));\n");
            fwrite($fPerms, "    '$httpRoute'=>'$apiPermission',\n");
        };
    }
    fwrite($fRoute, "\n");
    //$obj = new $class;
    //$obj->OnCall();


}

fwrite($fPerms, "];");
fwrite($fRule, varExport($apiRuleList, true));
fwrite($fRule, ";");
fclose($fRoute);
fclose($fPerms);
fclose($fRule);
echo "finished. see: \n";
echo " 1. $routeFilename\n";
echo " 2. $permissionFilename\n";
echo " 3. $ruleFilename\n";
