<?php
namespace Common\Libs;

class Arr {

    static public function toUnderScoreParams($params=[]) {
        $ret = [];
        foreach ($params as $key => $value) {
            if(is_array($value)){
                $value = Arr::toUnderScoreParams($value);
            }
            $ret[Str::toUnderScore($key)] = $value;        
        }
        return $ret;
    }

    static public function toCamelCaseParams($params=[]) {
        $ret = [];
        foreach ($params as $key => $value) {
            if(is_array($value)){
                $value = Arr::toCamelCaseParams($value);
            }
            $ret[Str::toCamelCase($key)] = $value;        
        }
        return $ret;
    }

    static public function ksort(&$arr){
        ksort($arr);
        foreach($arr as $k=>$v){
            if(is_array($v)){
                self::ksort($arr[$k]);
            }
        }
    }
}