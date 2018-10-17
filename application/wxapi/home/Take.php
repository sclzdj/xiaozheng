<?php
namespace app\wxapi\home;
use think\Db;

class Take extends Login
{
    //我的订阅
    public function index()
    {
        $takes=Db::name('take a')->join('city b','a.city_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->where(['a.user_id'=>$this->_userId,'c.is_del'=>0,'d.is_del'=>0])->order('a.addtime asc,a.id asc')->field('a.id,a.addtime,b.title city,c.title career,d.title ident')->select();
        return $this->response($takes,$this->_successCode,'');
    }
    //添加我的订阅
    public function add()
    {
        $this->checkNeedParam(['city_ids'=>'请传入city_ids','career_ids'=>'请传入career_ids','ident_ids'=>'请传入ident_ids']);
        $city_ids=input('city_ids');
        $career_ids=input('career_ids');
        $ident_ids=input('ident_ids');
        $city_ids=explode(',', $city_ids);
        $career_ids=explode(',', $career_ids);
        $ident_ids=explode(',', $ident_ids);
        $now=time();
        foreach ($city_ids as $city_id) {
            $city=Db::name('city')->find($city_id);
            if(!$city) continue;
            foreach ($career_ids as $career_id) {
                $career=Db::name('career')->find($career_id);
                if(!$career) continue;
                foreach ($ident_ids as $ident_id) {
                    $ident=Db::name('ident')->find($ident_id);
                    if(!$ident) continue;
                    $take=Db::name('take')->where(['user_id'=>$this->_userId,'city_id'=>$city_id,'career_id'=>$career_id,'ident_id'=>$ident_id])->find();
                    if(!$take){
                        Db::name('take')->insertGetId(['user_id'=>$this->_userId,'city_id'=>$city_id,'career_id'=>$career_id,'ident_id'=>$ident_id,'addtime'=>$now]);
                    }
                }
            }
        }
        $where_city='( ';
        if(in_array(1, $city_ids)){
            $where_city.='(b.sheng=0 AND b.shi=0) ';
        }
        $shengs=Db::name('city')->where('id','in',$city_ids)->where('pid',1)->value('GROUP_CONCAT(id)');
        if($shengs){
            if($where_city!='( '){
                $where_city.='OR (b.sheng in ('.$shengs.') AND b.shi=0) ';
            }else{
                $where_city.='(b.sheng in ('.$shengs.') AND b.shi=0) ';
            }
        }
        $shis=Db::name('city')->where('id','in',$city_ids)->where('pid','neq',0)->where('pid','neq',1)->value('GROUP_CONCAT(id)');
        if($shis){
            if($where_city!='( '){
                $where_city.='OR (b.shi in ('.$shis.')) ';
            }else{
                $where_city.='(b.shi in ('.$shis.')) ';
            }
        }
        $where_city.=')';
        $where_career=['a.career_id'=>['in',$career_ids]];
        $where_ident=['a.ident_id'=>['in',$ident_ids]];
        $details=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where($where_city)->where($where_career)->where($where_ident)->field('a.id')->select();
        $now=time();
        foreach ($details as $k => $v) {
            $follow=Db::name('follow')->where(['user_id'=>$this->_userId,'relate'=>'detail','relate_id'=>$v['id']])->find();
            if(!$follow){
                Db::name('follow')->insertGetId(['user_id'=>$this->_userId,'relate'=>'detail','relate_id'=>$v['id'],'addtime'=>$now]);
                Db::name('detail')->where('id',$v['id'])->setInc('follow');
            }
        }
        return $this->response([],$this->_successCode,'');
    }
    //删除我的订阅
    public function del()
    {
        $take_ids=(string)input('take_ids','');
        if($take_ids===''){
            $where=['id'=>['neq',0]];
        }else{
            $where=['id'=>['in',$take_ids]];
        }
        Db::name('take')->where('user_id',$this->_userId)->where($where)->delete();
        return $this->response([],$this->_successCode,'');
    }
    //查出我的订阅邮箱
    public function selectemail()
    {
        $user=Db::name('user')->where('id',$this->_userId)->field('email')->find();
        return $this->response($user,$this->_successCode,'');
    }
    //修改我的订阅邮箱
    public function editemail()
    {
        $this->checkNeedParam(['email'=>'请传入email']);
        $email=input('email');
        if(!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email)){  
            return $this->response([],251,'请输入正确的邮箱地址');
        }
        Db::name('user')->where('id',$this->_userId)->update(['email'=>$email]);
        return $this->response([],$this->_successCode,'');
    }
}
