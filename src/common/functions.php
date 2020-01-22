<?php
/**
 * 如果存在 key 就返回对应 value, 否则返回默认值
 *
 * @param array $array
 * @param string $key
 * @param object $default
 * @return mixed
 */
function array_get_if_key_exists($array, $key, $default){
    return array_key_exists($key, $array)?$array[$key]:$default;
}