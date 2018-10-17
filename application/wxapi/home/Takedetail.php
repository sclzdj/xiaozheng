<?php
namespace app\wxapi\home;
use think\Db;

class Takedetail extends Login
{
    //订阅清单列表
    public function index()
    {
        $type=input('type',0);
        $limit=input('limit',6);
        $details=Db::name('follow f')->join('detail a','f.relate_id=a.id','LEFT')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0,'f.relate'=>'detail','f.user_id'=>$this->_userId])->field('f.id,f.addtime,f.relate_id,a.remark,a.pubtime,b.sheng,b.shi,a.policy_id,b.title as policy_title,a.career_id,c.title as career_title,a.ident_id,d.title as ident_title,a.category_id,e.title as category_title,a.click,a.laud,a.tread,a.follow,b.city_id,a.source')->order('f.addtime desc,f.id desc,a.pubtime desc')->select();
        $details=handle_arr_city($details);
        $ts=[];
        $filter=[];
        $datas=[];
        if($type>0){//按政策类型
            foreach ($details as $key => $value) {
                if(!in_array($value['category_id'],$filter)){
                    $filter[]=$value['category_id'];
                    $ts[]=['id'=>$value['category_id'],'title'=>$value['category_title']];
                }
            }
            foreach ($ts as $key => $value) {
                $pix=[];
                $pix['category']=$value;
                $pix['details']=[];
                foreach ($details as $k => $v) {
                    if(count($pix['details'])>=$limit) break;
                    if($v['category_id']==$value['id']){
                        unset($v['city_id']);
                        $pix['details'][]=$v;
                    }
                }
                $datas[]=$pix;
            }
        }else{//按地区
            foreach ($details as $key => $value) {
                if(!in_array($value['city_id'],$filter)){
                    $filter[]=$value['city_id'];
                    $ts[]=['id'=>$value['city_id'],'title'=>$value['city']];
                }
            }
            foreach ($ts as $key => $value) {
                $pix=[];
                $pix['city']=$value;
                $pix['details']=[];
                foreach ($details as $k => $v) {
                    if(count($pix['details'])>=$limit) break;
                    if($v['city_id']==$value['id']){
                        unset($v['city_id']);
                        $pix['details'][]=$v;
                    }
                }
                $datas[]=$pix;
            }
        }
        return $this->response($datas,$this->_successCode,'');
    }
    /*public function index()
    {
        $type=input('type',0);
        $limit=input('limit',2);
        $takes=Db::name('take')->where(['user_id'=>$this->_userId])->select();
        $city_ids=[];
        $career_ids=[];
        $ident_ids=[];
        foreach ($takes as $k => $v) {
            $city_ids[]=$v['city_id'];
            $career_ids[]=$v['career_id'];
            $ident_ids[]=$v['ident_id'];
        }
        $city_ids=array_unique($city_ids);
        $career_ids=array_unique($career_ids);
        $ident_ids=array_unique($ident_ids);
        $data=[];
        if($type>0){//按政策类型
            $categorys=Db::name('category')->field('id,title')->order('sort asc,id desc')->select();
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
            foreach ($categorys as $k => $v) {
                $details=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where($where_city)->where('a.career_id','in',$career_ids)->where('a.ident_id','in',$ident_ids)->where('a.category_id',$v['id'])->field('a.id,a.remark,a.pubtime,b.sheng,b.shi,a.policy_id,b.title as policy_title,a.career_id,c.title as career_title,a.ident_id,d.title as ident_title,a.category_id,e.title as category_title,a.click,a.laud,a.tread,a.follow')->order('a.pubtime desc,a.id desc')->limit($limit)->select();
                if(!$details){
                    unset($data[$k]);
                    continue;
                }
                $details=handle_arr_city($details);
                foreach ($details as $_k => $_v) {
                     $details[$_k]['is_follow']=0;
                     $details[$_k]['is_laud']=0;
                     $details[$_k]['is_tread']=0;
                }
                foreach ($details as $_k => $_v) {
                    $follow=Db::name('follow')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$_v['id']])->find();
                    if($follow) $details[$_k]['is_follow']=1;
                    $laud_tread=Db::name('laud_tread')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$_v['id']])->find();
                    if($laud_tread){
                        if($laud_tread['type']==1) $details[$_k]['is_laud']=1;
                        if($laud_tread['type']==-1) $details[$_k]['is_tread']=1;
                    }
                }
                $data[$k]['category']=$v;
                $data[$k]['details']=$details;
            }
        }else{//按地区
            $citys=[];
            $guos=Db::name('city')->where('pid',0)->where('id','in',$city_ids)->field('id,title,pid')->select();
            $shengs=Db::name('city')->where('id','in',$city_ids)->where('pid',1)->field('id,title,pid')->order('sort asc,id desc')->select();
            $shis=Db::name('city')->where('id','in',$city_ids)->where('pid','neq',0)->where('pid','neq',1)->order('sort asc,id desc')->field('id,title,pid')->select();
            $citys=array_merge($guos,$shengs,$shis);
            foreach ($citys as $k => $v) {
                if($v['pid']==0 && $v['id']==1){
                    $where_city='b.sheng=0 AND b.shi=0';
                }elseif($v['pid']==1){
                    $where_city='b.sheng='.$v['id'].' AND b.shi=0'; 
                }else{
                    $where_city='b.shi='.$v['id']; 
                }
                $details=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where($where_city)->where('a.career_id','in',$career_ids)->where('a.ident_id','in',$ident_ids)->field('a.id,a.remark,a.pubtime,b.sheng,b.shi,a.policy_id,b.title as policy_title,a.career_id,c.title as career_title,a.ident_id,d.title as ident_title,a.category_id,e.title as category_title,a.click,a.laud,a.tread,a.follow')->order('a.pubtime desc,a.id desc')->limit($limit)->select();
                $details=handle_arr_city($details);
                foreach ($details as $_k => $_v) {
                     $details[$_k]['is_follow']=0;
                     $details[$_k]['is_laud']=0;
                     $details[$_k]['is_tread']=0;
                }
                foreach ($details as $_k => $_v) {
                    $follow=Db::name('follow')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$_v['id']])->find();
                    if($follow) $details[$_k]['is_follow']=1;
                    $laud_tread=Db::name('laud_tread')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$_v['id']])->find();
                    if($laud_tread){
                        if($laud_tread['type']==1) $details[$_k]['is_laud']=1;
                        if($laud_tread['type']==-1) $details[$_k]['is_tread']=1;
                    }
                }
                $citys[$k]['details']=$details;
                unset($v['pid']);
                $data[$k]['city']=$v;
                $data[$k]['details']=$details;
            }
        } 
        return $this->response($data,$this->_successCode,'');
    }*/
}
