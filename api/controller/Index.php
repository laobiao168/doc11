<?php

namespace app\api\controller;

use app\common\controller\Api;
use QL\QueryList;
use think\Cache;
use think\Db;
use think\Exception;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    /**
     * 彩图
     */
    public function am1(){
        set_time_limit(0);
        $domain = 'https://628818.5kanglin.com/';
        $url = $domain.'k_imageslist.aspx';
        $rules = [
            // DOM解析文章标题
            'title' => ['a','html'],
            // DOM解析文章作者
            'href' => ['a','href'],
        ];
        $rt = QueryList::get($url)->rules($rules)->range('.indexed-list li')->query()->getData();
        $list = $rt->all();
        foreach ($list as $item){
            $tq = QueryList::html($item['title']);
            $tq->find('span')->remove();

            //2.解析参数
            $udata = parse_url($domain.$item['href']);
            parse_str($udata['query'], $params);

            //3.验证分类是否存在
            $tu = Db::name('tu')->where(['typeId'=>$params['id'], 'type'=>1])->find();
            if($tu){
                Db::name('tu')->where(['typeId'=>$params['id']])->update(['qi'=>$params['qi']]);
            }else{
                continue;
            }
            //4.验证图片是否存在
            $pic = Db::name('tulist')->where(['qi'=>$params['qi'], 'tu_id'=>$tu['id']])->find();
            if(!$pic){
                //采集图片
                $tulist = [];
                $picurl = 'https://tk2.shuangshuangjieyanw.com:4949/col/'.$params['qi'].'/'.$tu['pic'];
                $tulist[] = ['pictureId'=>$params['id'],'qi'=>$params['qi'], 'pic'=>$tu['pic'], 'tu_id'=>$tu['id'], 'picurl'=>$picurl];
                Db::name('tulist')->insertAll($tulist);
            }

        }
        echo 'ok';
    }

    /**
     * 黑白
     */
    public function am2(){
        set_time_limit(0);
        $domain = 'https://628818.5kanglin.com/';
        $url = $domain.'k_imageslist2.aspx';
        $rules = [
            // DOM解析文章标题
            'title' => ['a','html'],
            // DOM解析文章作者
            'href' => ['a','href'],
        ];
        $rt = QueryList::get($url)->rules($rules)->range('.indexed-list li')->query()->getData();
        $list = $rt->all();
        foreach ($list as $item){
            $tq = QueryList::html($item['title']);
            $tq->find('span')->remove();

            //2.解析参数
            $udata = parse_url($domain.$item['href']);
            parse_str($udata['query'], $params);

            //3.验证分类是否存在
            $tu = Db::name('tu')->where(['typeId'=>$params['id'], 'type'=>2])->find();
            if($tu){
                Db::name('tu')->where(['typeId'=>$params['id']])->update(['qi'=>$params['qi']]);
            }else{
                continue;
            }
            //4.验证图片是否存在
            $pic = Db::name('tulist')->where(['qi'=>$params['qi'], 'tu_id'=>$tu['id']])->find();
            if(!$pic){
                //采集图片
                $tulist = [];
                $picurl = 'https://tk2.shuangshuangjieyanw.com:4949/col/'.$params['qi'].'/'.$tu['pic'];
                $tulist[] = ['pictureId'=>$params['id'],'qi'=>$params['qi'], 'pic'=>$tu['pic'], 'tu_id'=>$tu['id'], 'picurl'=>$picurl];
                Db::name('tulist')->insertAll($tulist);
            }

        }
        echo 'ok';
    }



    /**
     * 彩图
     */
    public function xginit1(){
        set_time_limit(0);
        $tytype = 1;
        $domain = 'https://www.hk072.com/';
        $url = $domain.'photo-color.html?type=1';
        $rules = [
            // DOM解析文章标题
            'title' => ['a','html'],
            // DOM解析文章作者
            'href' => ['a','href'],
        ];
        $rt = QueryList::get($url)->rules($rules)->range('.ablum ul li')->query()->getData();
        $list = $rt->all();
        foreach ($list as $item){
            $tq = QueryList::html($item['title']);
            //最后1期
            $latestqi = $tq->find('label')->text();
            $tq->find('span')->remove();
            //名称
            $title = trim($tq->getHtml());

            $ql = QueryList::get($domain.$item['href']);
            //1.获取文件名
            $tmprules = [
                // DOM解析文章标题
                'qid' => ['.qid', 'data-number'],
                // DOM解析文章作者
                'picid' => ['.qid','data-id'],
            ];
            $qilist = $ql->range('.swiper-wrapper .swiper-slide')->rules($tmprules)->query()->getData();

            //3.验证分类是否存在
            $tu = Db::name('tu_copy')->where(['name'=>$title, 'type'=>$tytype])->find();
            if(!$tu){
                $tu['id'] = Db::name('tu_copy')->insertGetId(['qi'=>$latestqi, 'pic'=>'', 'name'=>$title, 'type'=>$tytype]);
            }
            $piclist = $qilist->all();
            $picdata = [];
            foreach ($piclist as $pp){
                $picurl = 'https://www.hk072.com/photo/index/img/?id='.$pp['picid'];
                $pic = Db::name('tulist_copy')->where(['picurl'=>$picurl])->find();
                if(!$pic){
                    $picdata[] = ['tu_id'=>$tu['id'], 'picurl'=>$picurl, 'qi'=>$pp['qid']];
                }
            }
            if($picdata){
                Db::name('tulist_copy')->insertAll($picdata);
            }
        }
        echo 'ok';
    }

    /**
     * 黑白
     */
    public function xginit2(){
        set_time_limit(0);
        $tytype = 1;
        $domain = 'https://www.hk072.com/';
        $url = $domain.'photo-black.html?type=1';
        $rules = [
            // DOM解析文章标题
            'title' => ['a','html'],
            // DOM解析文章作者
            'href' => ['a','href'],
        ];
        $rt = QueryList::get($url)->rules($rules)->range('.ablum ul li')->query()->getData();
        $list = $rt->all();
        foreach ($list as $item){
            $tq = QueryList::html($item['title']);
            //最后1期
            $latestqi = $tq->find('label')->text();
            $tq->find('span')->remove();
            //名称
            $title = trim($tq->getHtml());

            $ql = QueryList::get($domain.$item['href']);
            //1.获取文件名
            $tmprules = [
                // DOM解析文章标题
                'qid' => ['.qid', 'data-number'],
                // DOM解析文章作者
                'picid' => ['.qid','data-id'],
            ];
            $qilist = $ql->range('.swiper-wrapper .swiper-slide')->rules($tmprules)->query()->getData();

            //3.验证分类是否存在
            $tu = Db::name('tu_copy')->where(['name'=>$title, 'type'=>$tytype])->find();
            if(!$tu){
                $tu['id'] = Db::name('tu_copy')->insertGetId(['qi'=>$latestqi, 'pic'=>'', 'name'=>$title, 'type'=>$tytype]);
            }
            $piclist = $qilist->all();
            $picdata = [];
            foreach ($piclist as $pp){
                $picurl = 'https://www.hk072.com/photo/index/img/?id='.$pp['picid'];
                $pic = Db::name('tulist_copy')->where(['picurl'=>$picurl])->find();
                if(!$pic){
                    $picdata[] = ['tu_id'=>$tu['id'], 'picurl'=>$picurl, 'qi'=>$pp['qid']];
                }
            }
            if($picdata){
                Db::name('tulist_copy')->insertAll($picdata);
            }
        }
        echo 'ok';
    }

    
    public function getData(){
        $ck = 'api_caiji_data';
        if(Cache::has($ck)){
            echo json_encode(Cache::get($ck), JSON_UNESCAPED_UNICODE);exit;
        }
        $data['dg_amyxym'] = Db::name('dg_amyxym')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_hkgst'] = Db::name('dg_hkgst')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_ptlx'] = Db::name('dg_ptlx')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_dxztam'] = Db::name('dg_dxztam')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_gjpt'] = Db::name('dg_gjpt')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_sbztam'] = Db::name('dg_sbztam')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_tdzt'] = Db::name('dg_tdzt')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_zylx'] = Db::name('dg_zylx')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_ynsj'] = Db::name('dg_ynsj')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_stzt'] = Db::name('dg_stzt')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_dszt'] = Db::name('dg_dszt')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_bssx'] = Db::name('dg_bssx')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_bssxam'] = Db::name('dg_bssxam')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_jzds'] = Db::name('dg_jzds')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_hkyxym'] = Db::name('dg_hkyxym')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_nvztam'] = Db::name('dg_nvztam')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_wcsl'] = Db::name('dg_wcsl')->where(['status'=>1])->order(Db::raw('id desc'))->limit(2)->select();
        $data['dg_zt24m'] = Db::name('dg_zt24m')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_jz3h'] = Db::name('dg_jz3h')->where(['status'=>1])->order(Db::raw('id*1 desc'))->limit(2)->select();
        $data['dg_jx24m'] = Db::name('dg_jx24m')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        $data['dg_qhzt'] = Db::name('dg_qhzt')->where(['status'=>1])->order(Db::raw('id*1 desc'))->limit(2)->select();
        $data['dg_qqsh'] = Db::name('dg_qqsh')->where(['status'=>1])->order(Db::raw('qi*1 desc'))->limit(2)->select();
        Cache::set($ck, $data, 600);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);exit;
    }

    //采集
    public function cj(){
        $url = 'http://local.hintk1.com/api/index/getData';
        $str = ($url);
        $result = json_decode($str, true);
        $dd = '';
        try {
            if($result){
                $kai = Db::name('twkj')->where(['addtime'=>['gt', time()]])->order(Db::raw('qihao*1 asc'))->find();
                foreach ($result as  $tb=>$list){
                    $dd = $tb;
                    foreach ($list as $row){
                        if(!Db::name($tb)->where(['cjid'=>$row['id']])->find()){
                            $row['cjid'] = $row['id'];
                            unset($row['id']);
                            if($kai){
                                $row['qi'] = $kai['qi'];
                            }
                            Db::name($tb)->insertGetId($row);
                        }
                    }
                }
            }
            echo 'ok';
        }catch (Exception $x4){
            echo $x4->getMessage().';'.$dd;
        }
    }

}
