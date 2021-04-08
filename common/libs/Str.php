<?php
namespace Common\Libs;

class Str {

   static public function toUnderScore($str) {

        $dstr = preg_replace_callback(
            '/([A-Z]{1})/',
            function($matchs){
                return '_'.strtolower($matchs[0]);
            },
            $str
        );
        
        return trim(preg_replace('/_{2,}/','_',$dstr),'_');
    }

    static function toCamelCase($str){
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i',function($matches){
            return strtoupper($matches[2]);
        },$str);
        return $str;
    }
}