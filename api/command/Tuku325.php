<?php
namespace app\api\command;


use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;

/**
 * 定时显示自开图库
 */
class Tuku325 extends Command
{

    protected function configure()
    {
        $this->setName('tuku325')->setDescription('采集私彩图片');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('start..');

        while (1){
            try{
                $output->writeln($this->downimg(1));
            }catch (Exception $ex){
                $output->writeln($ex->getMessage().';'.$ex->getLine());
            }
            sleep(5);
        }

    }

    private function downimg($lotteryTypeId){
        $tb = 'twkj';
        $nextarr = Db::name($tb)->where(['addtime'=>['gt', time()]])->order(Db::raw('qihao*1 asc'))->find();
        if(!$nextarr){
            return '暂无下期预告';
        }
        $tu = Db::name('tu_kaijiang6 t')
            ->field('t.*, ifnull(l.tu_id, 0) tu_id')
            ->join('tu_kaijianglist l', 'l.tu_id = t.id and l.qishu = '.$nextarr['qihao'], 'left')
            ->where(['t.lotteryType'=>$lotteryTypeId, 't.status'=>1])->whereNotNull('t.cj_id')->order('t.cj_time asc')->having('tu_id=0')->find();
        if($tu){
            //采集
            $site = Config::get("site");
            $kjarr = Db::name($tb)->where(['addtime'=>['lt', time()]])->order(Db::raw('qihao*1 desc'))->find();
            $param['pictureId'] = $tu['cj_id'];
            $param['peroid'] = str_replace(date('Y'), '', $nextarr['qihao']);
            $param['apikey'] = $site['tuku325key'];
            $param['prevPeroidNumbers'] = $kjarr['num1'].','.$kjarr['num2'].','.$kjarr['num3'].','.$kjarr['num4'].','.$kjarr['num5'].','.$kjarr['num6'].','.$kjarr['num7'];
            $param['sxstr'] = $tu['cj_sxstr'];
            $param['numberstr'] = $tu['cj_numberstr'];
            $res = curl_post_https(Config::get('tuku.tuku325').'api/tuku/build', http_build_query($param));
            $return = json_decode($res, true);
            if($return && $return['code'] == 200){
                //下载图片
                $path = DS .'uploads'. DS. 'tuku'. DS. date('Y'). DS. $nextarr['qihao'];
                $rpath = ROOT_PATH. DS. 'public'.$path;
                if(!is_dir($rpath)){
                    mkdir($rpath,0777,true);
                }
                $localfile = fileDow($return['data']['url'], $rpath, $lotteryTypeId);
                $filename = $path .DS .$localfile[1];
                //插入数据库
                $vo = [];
                $vo['image'] = $filename;
                $vo['qishu'] = $nextarr['qihao'];
                $vo['tu_id'] = $tu['id'];
                $vo['lotteryType'] = $lotteryTypeId;
                $vo['year'] = date('Y');
                $vo['addtime'] = time();
                $vo['show'] = 0;
                Db::name('tu_kaijianglist')->insertGetId($vo);
                //更新采集
                Db::name('tu_kaijiang6')->where(['id'=>$tu['id']])->update(['cj_time'=>time(), 'image'=>$filename, 'lastdown'=>$nextarr['qihao']]);
                return '采集'. $return['data']['url'];
            }else{
                return '采集接口报错'.$res;
            }
        }else{
            return '采集完毕...暂无任务';
        }
    }


}