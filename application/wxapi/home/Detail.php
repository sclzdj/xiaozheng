<?php
namespace app\wxapi\home;
use think\Db;

class Detail extends Home
{
    //清单列表
    public function index()
    {
        $city_ids=input('city_ids',0);
        $career_ids=input('career_ids','');
        $ident_ids=input('ident_ids','');
        $category_id=input('category_id',0);
        $keywords=input('keywords','');
        $page=input('page',1);
        $pageSize=input('pageSize',10);
        $offset=$pageSize*($page-1);
        /*if($city_ids){
            $city_ids=explode(',', $city_ids);
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
            $order="b.shi desc,b.sheng desc,b.pubtime desc,b.id desc";
        }else{
            $where_city='';
            $order="b.pubtime desc,b.id desc";
        }*/
        if($city_ids>0){
            if($city_ids==1){
                $where_city=['b.sheng'=>0,'b.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_ids)->value('pid');
                if($pid==1){
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$city_ids} and b.shi=0))";
                }else{
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$pid} and b.shi=0) or b.shi={$city_ids})";
                }
            }
            $order="b.shi desc,b.sheng desc,b.pubtime desc,b.id desc";
        }else{
            $where_city='';
            $order="b.pubtime desc,b.id desc";
        }

        if($career_ids){
            $where_career=['a.career_id'=>['in',$career_ids]];
        }else{
            $where_career='';
        }
        if($ident_ids){
            $where_ident=['a.ident_id'=>['in',$ident_ids]];
        }else{
            $where_ident='';
        }
        if($category_id>0){
            $where_category=['a.category_id'=>$category_id];
        }else{
            $where_category='';
        }
        if($keywords!==''){
            $where_keywords=['a.remark'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $details=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where($where_city)->where($where_career)->where($where_ident)->where($where_category)->where($where_keywords)->field('a.id,a.remark,a.pubtime,b.sheng,b.shi,a.policy_id,b.title as policy_title,a.career_id,c.title as career_title,a.ident_id,d.title as ident_title,a.category_id,e.title as category_title,a.click,a.laud,a.tread,a.follow,a.source')->order($order)->limit($offset,$pageSize)->select();
        $details=handle_arr_city($details);
        foreach ($details as $k => $v) {
             $details[$k]['is_follow']=0;
             $details[$k]['is_laud']=0;
             $details[$k]['is_tread']=0;
        }
        if($this->isLogin()){
            foreach ($details as $k => $v) {
                $follow=Db::name('follow')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$v['id']])->find();
                if($follow) $details[$k]['is_follow']=1;
                $laud_tread=Db::name('laud_tread')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$v['id']])->find();
                if($laud_tread){
                    if($laud_tread['type']==1) $details[$k]['is_laud']=1;
                    if($laud_tread['type']==-1) $details[$k]['is_tread']=1;
                }
            }
        }
        return $this->response($details,$this->_successCode,'');
    }
    //有数据的清单分类列表
    public function hasdatacategorys()
    {
        $city_ids=input('city_ids',0);
        $career_ids=input('career_ids','');
        $ident_ids=input('ident_ids','');
        $keywords=input('keywords','');
        /*if($city_ids){
            $city_ids=explode(',', $city_ids);
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
            $order="b.shi desc,b.sheng desc,b.pubtime desc,b.id desc";
        }else{
            $where_city='';
            $order="b.pubtime desc,b.id desc";
        }*/
        if($city_ids>0){
            if($city_ids==1){
                $where_city=['b.sheng'=>0,'b.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_ids)->value('pid');
                if($pid==1){
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$city_ids} and b.shi=0))";
                }else{
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$pid} and b.shi=0) or b.shi={$city_ids})";
                }
            }
            $order="b.shi desc,b.sheng desc,b.pubtime desc,b.id desc";
        }else{
            $where_city='';
            $order="b.pubtime desc,b.id desc";
        }

        if($career_ids){
            $where_career=['a.career_id'=>['in',$career_ids]];
        }else{
            $where_career='';
        }
        if($ident_ids){
            $where_ident=['a.ident_id'=>['in',$ident_ids]];
        }else{
            $where_ident='';
        }
        if($keywords!==''){
            $where_keywords=['a.remark'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $categorys=Db::name('category')->field('id,title')->where('is_del',0)->order('sort asc,id desc')->select();
        $new_categorys=[];
        foreach ($categorys as $k => $v) {
            $where_category=['a.category_id'=>$v['id']];
            $details=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where($where_city)->where($where_career)->where($where_ident)->where($where_category)->where($where_keywords)->field('a.id,a.remark,a.pubtime,b.sheng,b.shi,a.policy_id,b.title as policy_title,a.career_id,c.title as career_title,a.ident_id,d.title as ident_title,a.category_id,e.title as category_title,a.click,a.laud,a.tread,a.follow,a.source')->order($order)->limit(1)->select();
            if($details){
                $new_categorys[]=$v;
            }
        } 
        return $this->response($new_categorys,$this->_successCode,'');
    }
    //清单搜索结果总量
    public function count()
    {
        $city_ids=input('city_ids',0);
        $career_ids=input('career_ids','');
        $ident_ids=input('ident_ids','');
        $category_id=input('category_id',0);
        $keywords=input('keywords','');
        $page=input('page',1);
        $pageSize=input('pageSize',10);
        $offset=$pageSize*($page-1);
        if($city_ids>0){
            if($city_ids==1){
                $where_city=['b.sheng'=>0,'b.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_ids)->value('pid');
                if($pid==1){
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$city_ids} and b.shi=0))";
                }else{
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$pid} and b.shi=0) or b.shi={$city_ids})";
                }
            }
            $order="b.shi desc,b.sheng desc,b.pubtime desc,b.id desc";
        }else{
            $where_city='';
            $order="b.pubtime desc,b.id desc";
        }
        if($career_ids){
            $where_career=['a.career_id'=>['in',$career_ids]];
        }else{
            $where_career='';
        }
        if($ident_ids){
            $where_ident=['a.ident_id'=>['in',$ident_ids]];
        }else{
            $where_ident='';
        }
        if($category_id>0){
            $where_category=['a.category_id'=>$category_id];
        }else{
            $where_category='';
        }
        if($keywords!==''){
            $where_keywords=['a.remark'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $count=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where($where_city)->where($where_career)->where($where_ident)->where($where_category)->where($where_keywords)->field('COUNT(a.id) count')->find();
        return $this->response($count,$this->_successCode,'');
    }
    //清单详情
    public function content(){
        $this->checkNeedParam(['detail_id'=>'请传入detail_id']);
        $detail_id=input('detail_id');
        $detail=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where('a.id',$detail_id)->field('a.id,a.remark,a.pubtime,b.sheng,b.shi,a.policy_id,b.title as policy_title,a.career_id,c.title as career_title,a.ident_id,d.title as ident_title,a.category_id,e.title as category_title,a.click,a.laud,a.tread,a.follow,a.source')->find();
        if(!$detail) $this->response([],201,'清单不存在');
        Db::name('detail')->where('id',$detail_id)->setInc('click');
        $detail['click']+=1;
        $detail=handle_arr_city($detail,false);
        $basis=Db::name('basis_data a')->join('basis b','a.basis_id=b.id')->where(['a.detail_id'=>$detail['id'],'b.is_del'=>0])->field('b.title,a.val,b.type')->order('b.sort asc')->select();
        $detail['basis']=$basis;
        $handles=Db::name('handle_data a')->join('handle b','a.handle_id=b.id')->where(['a.detail_id'=>$detail['id'],'b.is_del'=>0])->field('b.title,b.pic_id,a.val,b.type')->order('b.sort asc')->select();
        $handles=handle_arr_pic($handles);
        $detail['handles']=$handles;
        $detail['is_follow']=0;
        $detail['is_laud']=0;
        $detail['is_tread']=0;
        if($this->isLogin()){
            $follow=Db::name('follow')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$detail['id']])->find();
            if($follow) $detail['is_follow']=1;
            $laud_tread=Db::name('laud_tread')->where(['relate'=>'detail','user_id'=>$this->_userId,'relate_id'=>$detail['id']])->find();
            if($laud_tread){
                if($laud_tread['type']==1) $detail['is_laud']=1;
                if($laud_tread['type']==-1) $detail['is_tread']=1;
            }
        }
        return $this->response($detail,$this->_successCode,'');
    }
    
}
