<?php
namespace app\wxapi\home;
use think\Db;

class Qa extends Home
{
    //问答列表
    public function index()
    {
        $qa_class_id=input('qa_class_id',0);
        $keywords=input('keywords','');
        $page=input('page',1);
        $pageSize=input('pageSize',10);
        $offset=$pageSize*($page-1);
        if($qa_class_id>0){
            $where_qa_class=['a.qa_class_id'=>$qa_class_id];
        }else{
            $where_qa_class='';
        }
        if($keywords!==''){
            $where_keywords=['a.q'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $qas=Db::name('qa a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_qa_class)->where($where_keywords)->field('a.id,a.q,a.a,a.pubtime,a.qa_class_id,b.title as qa_class_title,a.click,a.laud,a.tread,a.follow')->order('a.pubtime desc,a.id desc')->limit($offset,$pageSize)->select();
        foreach ($qas as $k => $v) {
            $qas[$k]['a']=strip_tags($v['a']);
             $qas[$k]['is_follow']=0;
             $qas[$k]['is_laud']=0;
             $qas[$k]['is_tread']=0;
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
        return $this->response($qas,$this->_successCode,'');
    }
    //问答搜索结果总量
    public function count()
    {
        $qa_class_id=input('qa_class_id',0);
        $keywords=input('keywords','');
        if($qa_class_id>0){
            $where_qa_class=['a.qa_class_id'=>$qa_class_id];
        }else{
            $where_qa_class='';
        }
        if($keywords!==''){
            $where_keywords=['a.q'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $count=Db::name('qa a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_qa_class)->where($where_keywords)->field('COUNT(a.id) count')->order('a.pubtime desc,a.id desc')->find();
        return $this->response($count,$this->_successCode,'');
    }
    //详情
    public function content()
    {
        $this->checkNeedParam(['qa_id'=>'请传入qa_id']);
        $qa_id=input('qa_id');
        $qa=Db::name('qa a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where(['a.id'=>$qa_id])->field('a.id,a.q,a.pubtime,a.qa_class_id,b.title as qa_class_title,a.a,a.click,a.laud,a.tread,a.follow')->find();
        if(!$qa) $this->response([],201,'问答不存在');
        Db::name('qa')->where('id',$qa_id)->setInc('click');
        $qa['click']+=1;
        $qa['is_follow']=0;
        $qa['is_laud']=0;
        $qa['is_tread']=0;
        if($this->isLogin()){
            $follow=Db::name('follow')->where(['relate'=>'qa','user_id'=>$this->_userId,'relate_id'=>$qa['id']])->find();
            if($follow) $qa['is_follow']=1;
            $laud_tread=Db::name('laud_tread')->where(['relate'=>'qa','user_id'=>$this->_userId,'relate_id'=>$qa['id']])->find();
            if($laud_tread){
                if($laud_tread['type']==1) $qa['is_laud']=1;
                if($laud_tread['type']==-1) $qa['is_tread']=1;
            }
        }
        $qa['a']=handle_view_pic($qa['a']);
        return $this->response($qa,$this->_successCode,'');
    }
    //问答分类列表
    public function qa_class(){
        $limit=input('limit',6);
        $qa_class=Db::name('qa_class')->where(['is_del'=>0])->order('sort asc,id desc')->limit($limit)->field('id,title')->select();
        return $this->response($qa_class,$this->_successCode,'');
    }
    //热门问答
    public function hot()
    {
        $click=input('click',1);
        $limit=input('limit',2);
        $group=input('group',5);
        $interacts=Db::query("SELECT relate_id,count(id) AS count from ".config('database.prefix')."interact where relate='qa' and audit=1 and is_public=1 and is_show=1 GROUP BY relate_id order by count desc limit ".$limit*$group);
        $qas=[];
        $ids=[];
        foreach ($interacts as $k => $v) {
            $qa=Db::name('qa a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where('a.id',$v['relate_id'])->field('a.id,a.q,a.a,a.pubtime,a.qa_class_id,b.title as qa_class_title,a.click,a.laud,a.tread,a.follow')->order('a.click desc,a.pubtime desc,a.id desc')->find();
            if($qa){
                $qa['interact']=$v['count'];
                $qas[]=$qa;  
                $ids[]=$v['relate_id'];
            }
        }
        if(count($qas)<$limit*$group){
            $qa_orthers=Db::name('qa a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where('a.id','not in',$ids)->field('a.id,a.q,a.a,a.pubtime,a.qa_class_id,b.title as qa_class_title,a.click,a.laud,a.tread,a.follow')->order('a.click desc,a.pubtime desc,a.id desc')->limit($limit*$group-count($qas))->select();
            foreach ($qa_orthers as $k => $v) {
                $qa_orthers[$k]['interact']=0;
            }
            $qas=array_merge($qas,$qa_orthers);
        }
        
        foreach ($qas as $k => $v) {
            $qas[$k]['a']=strip_tags($v['a']);
             $qas[$k]['is_follow']=0;
             $qas[$k]['is_laud']=0;
             $qas[$k]['is_tread']=0;
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
        if(count($qas)<=$limit){
            return $this->response($qas,$this->_successCode,'');
        }
        if(count($qas)>$limit && count($qas)<$limit*$group){
            $qa_qas=[];
            for ($i=0; $i < $limit*$group; $i++) { 
                $qa_qas[$i]=$qas[$i%count($qas)];
            }
             $qas=$qa_qas;
        }
        $qas=array_slice($qas, ((($click-1)*$limit)%$group),$limit);
        return $this->response($qas,$this->_successCode,'');
    }
}
