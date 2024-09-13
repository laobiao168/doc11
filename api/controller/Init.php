<?php

namespace app\api\controller;

use app\common\controller\Api;
use QL\QueryList;
use think\Db;
use think\Config;

/**
 * 首页接口
 */
class Init extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    //同步数据
    public function sync(){
        $this->wy();
        $this->upimage();
    }

    //更新图库
    private function upimage(){
        $id = 1;
        $res = curl_get_https('https://www.4988868.one/api/tu/tuapi');
        $data = json_decode($res, true);
        $up = 0;
        if($data){
            foreach ($data['data']['list1'] as $item){
                $has = Db::name('tu_kaijiang6')->where(['cj_id'=>$item['cj_id']])->find();
                if(!$has){
                    Db::name('tu_kaijiang6')->insertGetId(['cj_id'=>$item['cj_id'], 'color'=>1,'letter'=>'','pictureName'=>$item['pictureName'], 'pictureUrl'=>$item['pictureUrl'],'pictureTypeId'=>$item['cj_id']]);
                }else{
                    Db::name('tu_kaijiang6')->where(['id'=>$has['id']])->update(['pictureName'=>$item['pictureName'], 'pictureUrl'=>$item['pictureUrl']]);
                }
                $up ++;
            }
        }
        echo '更新'.$up.'个图片';
    }

    //更新开奖
    private function wy(){
        $url = 'http://api.bxjtuku.com/api/Lottery/getxxAmKJ';
        $arr = json_decode(curl_post_https($url), true);
        if ($arr && isset($arr['list'])){
            $temp = $arr['list'][0];
            //处理开奖兼容
            $item['qihao'] = $temp['period'];
            $item['addtime'] = strtotime(str_replace(' PM', '', $temp['opentime']));
            $nextqi['addtime'] = $item['addtime']+86400;
            $num = explode(',', $temp['openCode']);
            $item['num1'] = $num[0]??'';
            $item['num2'] = $num[1]??'';
            $item['num3'] = $num[2]??'';
            $item['num4'] = $num[3]??'';
            $item['num5'] = $num[4]??'';
            $item['num6'] = $num[5]??'';
            $item['num7'] = $num[6]??'';

            //本期刷新
            Db::name('twkj')->where(['qihao'=>$item['qihao']])->update(['num1'=>$item['num1'],'num2'=>$item['num2'],'num3'=>$item['num3'],'num4'=>$item['num4'],'num5'=>$item['num5'],'num6'=>$item['num6'],'num7'=>$item['num7']]);

            //生成预告
            $has = Db::name('twkj')->where(['qihao'=>$item['qihao']+1])->find();
            if(!$has){
                Db::name('twkj')->insertGetId(['qihao'=>$item['qihao']+1, 'addtime'=>$nextqi['addtime'],'num1'=>'','num2'=>'','num3'=>'','num4'=>'','num5'=>'','num6'=>'','num7'=>'']);
            }
        }

        echo '同步开奖;';
    }


}
