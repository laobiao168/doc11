<?php


namespace app\api\logic;


use think\Db;

class TuBase
{


    public static function getChengYu($filename='cy', $type=1, $sx=false){
        $str = getline(ROOT_PATH.'/application/api/logic/'.$filename.'.txt', $sx);
        return $str;
    }


}