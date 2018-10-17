<?php
namespace app\wxapi\home;
use think\Db;

class Unlogged extends Home
{
    //测试
    public function test()
    {
        return $this->response(['str'=>"hello"],$this->_successCode,'晓政接口测试（未登录）');
    }
    //登录
    public function login()
    {
    	$this->checkNeedParam(['code'=>'请传入code','nickname'=>'请传入nickname','avatarurl'=>'请传入avatarurl']);
    	//请求微信服务器获取openid和session_key
        $code=input('code');
        $nickname=input('nickname');
        $avatarurl=input('avatarurl');
        $appid=config('xiaozheng.appid');
        $secret=config('xiaozheng.secret');
        $url="https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        $response=$this->_request('get',$url);
        if($response===false){
            return $this->response([],301,'请求微信服务器出错');
        }
        $response=json_decode($response,true);
        if($response===null){
            return $this->response([],302,'微信服务器返回数据无法解析');
        }
        if(!isset($response['openid']) || !isset($response['session_key'])){
            return $this->response([],303,'未从微信服务器上获取到需求的数据');
        }
    	$openid=$response['openid'];
    	$session_key=$response['session_key'];
    	//生成3rd_session
    	$rd_session=make_3rd_session();
    	//入库
    	$user=Db::name('user')->where('openid',$openid)->find();
    	if(!$user){
            if($nickname===''){$nickname='用户-'.random(4);$is_auth=0;}else{$is_auth=1;}
    		$user_id=Db::name('user')->insertGetId(['openid'=>$openid,'session_key'=>$session_key,'nickname'=>$nickname,'avatarurl'=>$avatarurl,'is_auth'=>$is_auth,'addtime'=>time(),'status'=>1]);
    	}else{
    		$user_id=$user['id'];
            if($nickname===''){
                $nickname=$user['nickname'];
                if($user['nickname']===''){
                    $is_auth=0;
                }else{
                    $is_auth=1;
                }
            }else{
                $is_auth=1;
            }
            Db::name('user')->update(['id'=>$user_id,'session_key'=>$session_key,'nickname'=>$nickname,'avatarurl'=>$avatarurl,'is_auth'=>$is_auth]);
    	}
    	$now=time();
    	$expire_length=config('xiaozheng.3rd_session_expire_length');
    	$expire=$now+$expire_length;
    	Db::name('3rd_session')->insertGetId(['user_id'=>$user_id,'3rd_session'=>$rd_session,'addtime'=>$now,'expire'=>$expire]);
        return $this->response(['3rd_session'=>$rd_session,'expire'=>$expire,'expire_length'=>$expire_length],$this->_successCode,'');
    }
    //树状地区结构
    public function citytree(){
        $citys=Db::name('city')->field('id,title')->find(1);
        $citys['shengs']=Db::name('city')->where('pid',$citys['id'])->field('id,title')->order('sort asc,id desc')->select();
        foreach ($citys['shengs'] as $k => $v) {
            $citys['shengs'][$k]['shis']=Db::name('city')->where('pid',$v['id'])->field('id,title')->order('sort asc,id desc')->select();
        }
        return $this->response($citys,$this->_successCode,'');
    }
    //一级排列所有地区
    public function cityall(){
        $citys=[];
        $citys[]=Db::name('city')->field('id,title')->find(1);
        $shengs=Db::name('city')->where('pid',$citys[0]['id'])->field('id,title')->order('sort asc,id desc')->select();
        $shis=[];
        foreach ($shengs as $k => $v) {
            $shi=Db::name('city')->where('pid',$v['id'])->order('sort asc,id desc')->field('id,title')->select();
            $shis=array_merge($shis,$shi);
        }
        $citys=array_merge($citys,$shis);
        return $this->response($citys,$this->_successCode,'');
    }
    //根据父ID获得子地区
    public function citychilds(){
        $this->checkNeedParam(['city_id'=>'请传入city_id']);
        $city_id=input('city_id');
        $city=Db::name('city')->where('id',$city_id)->find();
        if(!$city){
            return $this->response([],201,'传入的city_id无效');
        }
        $citys=Db::name('city')->where('pid',$city_id)->field('id,title')->order('sort asc,id desc')->select();
        return $this->response($citys,$this->_successCode,'');
    }
    //获取清单筛选条件
    public function datailfilter(){
        $city_comment=get_comment('city');
        if($city_comment==='') $city_comment='地区';
        $career_comment=get_comment('career');
        if($career_comment==='') $career_comment='事业';
        $ident_comment=get_comment('ident');
        if($ident_comment==='') $ident_comment='身份';
        $citys='（**请根据地区接口取地区数据！**）';
        $careers=Db::name('career')->field('id,title')->where('is_del',0)->order('sort asc,id desc')->select();
        $idents=Db::name('ident')->field('id,title')->where('is_del',0)->order('sort asc,id desc')->select();
        $filters=[
            [
                'filter_type'=>['field'=>'career_ids','title'=>$career_comment],
                'filter_options'=>$careers,
            ],
            [
                'filter_type'=>['field'=>'ident_ids','title'=>$ident_comment],
                'filter_options'=>$idents,
            ],
            [
                'filter_type'=>['field'=>'city_ids','title'=>$city_comment],
                'filter_options'=>$citys,
            ],
        ];
        return $this->response($filters,$this->_successCode,'');
    }
    //获取清单分类
    public function categorys(){
        $categorys=Db::name('category')->field('id,title')->where('is_del',0)->order('sort asc,id desc')->select();
        return $this->response($categorys,$this->_successCode,'');
    }
    //获取政策库
    public function clas(){
        $categorys=Db::name('class')->field('id,title,remark,pic_id,type')->where('id','in',['1','2'])->where('is_del',0)->order('sort asc,id desc')->select();
        $categorys=handle_arr_pic($categorys);
        return $this->response($categorys,$this->_successCode,'');
    }
    //根据经纬度定位地区
    public function location(){
        $this->checkNeedParam(['lng'=>'请传入lng','lat'=>'请传入lat']);
        $lng=input('lng');
        $lat=input('lat');
        $url="http://api.map.baidu.com/geocoder/v2/?callback=&location={$lat},{$lng}&output=json&pois=1&ak=".config('xiaozheng.location_baidu_ak');
        $response=$this->_request('get',$url,[],false);
        if($response===false){
            return $this->response([],301,'请求百度服务器出错');
        }//dump($response);
        $response=json_decode($response,true);
        if($response===null){
            return $this->response([],302,'百度服务器返回数据无法解析');
        }
        if(!isset($response['result']['addressComponent']['province']) || !isset($response['result']['addressComponent']['city'])){
            return $this->response([],303,'未从百度服务器上获取到需求的数据');
        }
        $lo_shi=str_replace(['市','自治州'], ['',''], $response['result']['addressComponent']['city']);
        $shi=Db::name('city')->where('title','like','%'.$lo_shi.'%')->field('id,title')->find();
        if($shi){
            return $this->response($shi,200,'');
        }else{
            /*$sheng=Db::name('city')->where('title','like','%'.$response['result']['addressComponent']['province'].'%')->field('id,title')->find();
            if($sheng){
                return $this->response($sheng,200,'');
            }else{*/
                $no=Db::name('city')->field('id,title')->find(1);
                return $this->response($no,200,'');
            //}
        }
    }
    //问答和政策混合列表
    public function gq()
    {
        $keywords=input('keywords','');
        $city_id=input('city_id',0);
        if($keywords!==''){
            $where_keywords=['a.q'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $qas=Db::name('qa a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_qa_class)->where($where_keywords)->field('a.id,a.q,a.a,a.pubtime,a.qa_class_id,b.title as qa_class_title,a.click,a.laud,a.tread,a.follow')->order('a.pubtime desc,a.id desc')->select();
        foreach ($qas as $k => $v) {
             $qas[$k]['a']=strip_tags($v['a']);
             $qas[$k]['is_follow']=0;
             $qas[$k]['is_laud']=0;
             $qas[$k]['is_tread']=0;
             $qas[$k]['interact']=Db::name('interact')->where(['relate'=>'qa','relate_id'=>$v['id'],'is_show'=>1,'audit'=>1,'is_public'=>1])->count();
             $qas[$k]['type']='qa';
        }
        if($this->isLogin()){
            foreach ($qas as $k => $v) {
                $follow=Db::name('follow')->where(['relate'=>'qa','user_id'=>$this->_userId,'relate_id'=>$v['id']])->find();
                if($follow) $qas[$k]['is_follow']=1;
                $laud_tread=Db::name('laud_tread')->where(['relate'=>'qa','user_id'=>$this->_userId,'relate_id'=>$v['id']])->find();
                if($laud_tread){
                    if($laud_tread['type']==1) $qas[$k]['is_laud']=1;
                    if($laud_tread['type']==-1) $qas[$k]['is_tread']=1;
                }
            }
        }
        if($city_id>0){
            if($city_id==1){
                $where_city=['a.sheng'=>0,'a.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_id)->value('pid');
                if($pid==1){
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$city_id} and a.shi=0))";
                }else{
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$pid} and a.shi=0) or a.shi={$city_id})";
                }
            }
            $order="a.shi desc,a.sheng desc,a.pubtime desc,a.id desc";
        }else{
            $where_city='';
            $order="a.pubtime desc,a.id desc";
        }
        if($keywords!==''){
            $where_keywords=['a.title'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $policys=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_city)->where('a.class_id','2')->where($where_keywords)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id,a.department,a.content')->order($order)->select();
        foreach ($policys as $k => $v) {
            $policys[$k]['content']=strip_tags($v['a']);
            $policys[$k]['type']='policy';
            $policys[$k]['interact']=Db::name('interact')->where(['relate'=>'policy','relate_id'=>$v['id'],'is_show'=>1,'audit'=>1,'is_public'=>1])->count();
        }
        $policys=handle_arr_city($policys);
        $policys=handle_arr_pic($policys);
        $datas=array_merge($qas,$policys);
        $datas=list_sort_by($datas,'pubtime','desc');
        return $this->response($datas,$this->_successCode,'');
    }    
}
