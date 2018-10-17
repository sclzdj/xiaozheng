<?php
namespace app\wxapi\home;
use think\Db;

class Policy extends Home
{
	//最新政策
    public function index()
    {
        $class_id=input('class_id',0);
        $city_id=input('city_id',0);
        $keywords=input('keywords','');
    	$page=input('page',1);
    	$pageSize=input('pageSize',10);
    	$offset=$pageSize*($page-1);
        if($class_id>0){
            $where_class=['a.class_id'=>$class_id];
        }else{
            $where_class='';
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
        $policys=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_city)->where($where_class)->where($where_keywords)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id,a.department')->order($order)->limit($offset,$pageSize)->select();
        $policys=handle_arr_city($policys);
        $policys=handle_arr_pic($policys);
        return $this->response($policys,$this->_successCode,'');
    }
    //政策原文
    public function original()
    {
    	$this->checkNeedParam(['policy_id'=>'请传入policy_id']);
    	$policy_id=input('policy_id');
        $policy=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'a.id'=>$policy_id])->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.content,a.click,a.pic_id,a.department')->find();
        if(!$policy) $this->response([],201,'政策不存在');
        Db::name('policy')->where('id',$policy_id)->setInc('click');
        $policy['click']+=1;
        $policy=handle_arr_city($policy,false);
        $policy=handle_arr_pic($policy,false);
        $policy['content']=handle_view_pic($policy['content']);
        return $this->response($policy,$this->_successCode,'');
    }
    //政策解读
    public function unscramble()
    {
    	$this->checkNeedParam(['policy_id'=>'请传入policy_id']);
    	$policy_id=input('policy_id');
    	$page=input('page',1);
    	$pageSize=input('pageSize',10);
    	$offset=$pageSize*($page-1);
        $policy=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'a.id'=>$policy_id])->find();
        if(!$policy) $this->response([],201,'政策不存在');
        $unscrambles=Db::name('unscramble')->where(['audit'=>1,'is_del'=>0,'policy_id'=>$policy_id])->field('id,title,source,pubtime,pic_id')->order('pubtime desc,id desc')->limit($offset,$pageSize)->select();
        if($page==1 && count($unscrambles)==1){
        	$unscramble=Db::name('unscramble')->field('id,title,source,pubtime,pic_id,content,click')->find($unscrambles[0]['id']);
            Db::name('unscramble')->where('id',$unscramble_id)->setInc('click');
            $unscramble['click']+=1;
        	$unscramble=handle_arr_pic($unscramble,false,'pic_id','');
            $unscramble['content']=handle_view_pic($unscramble['content']);
        	return $this->response($unscramble,$this->_successCode,'');
        }
        $unscrambles=handle_arr_pic($unscrambles,true,'pic_id','');
        return $this->response($unscrambles,$this->_successCode,'');
    }
    //政策解读详情
    public function unscramblecontent()
    {
    	$this->checkNeedParam(['unscramble_id'=>'请传入unscramble_id']);
    	$unscramble_id=input('unscramble_id');
    	$unscramble=Db::name('unscramble')->field('id,title,source,pubtime,pic_id,content,policy_id,click')->find($unscramble_id);
    	$policy=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'a.id'=>$unscramble['policy_id']])->find();
    	if(!$unscramble || !$policy) $this->response([],201,'解读不存在');
    	unset($unscramble['policy_id']);
        Db::name('unscramble')->where('id',$unscramble_id)->setInc('click');
        $unscramble['click']+=1;
    	$unscramble=handle_arr_pic($unscramble,false,'pic_id','');
        $unscramble['content']=handle_view_pic($unscramble['content']);
    	return $this->response($unscramble,$this->_successCode,'');
    }
}
