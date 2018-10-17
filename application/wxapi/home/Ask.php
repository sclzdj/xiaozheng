<?php
namespace app\wxapi\home;
use think\Db;

class Ask extends Login
{
    //我的提问
    public function index()
    {
        $is_reply=(string)input('is_reply','');
        $page=input('page',1);
        $pageSize=input('pageSize',10);
        $offset=$pageSize*($page-1);
        if($is_reply==='1'){
            $asks=Db::name('ask a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.user_id'=>$this->_userId,'a.is_show'=>1,'b.is_del'=>0,'a.is_reply'=>$is_reply])->field('a.id,a.q,a.remark,a.addtime,a.a,a.qa_class_id,b.title as qa_class_title,a.replytime')->order('a.replytime desc,a.addtime desc,a.id desc')->limit($offset,$pageSize)->select();
            foreach ($asks as $k => $v) {
                $asks[$k]['a']=strip_tags($v['a']);
            }
        }elseif($is_reply==='0'){
            $asks=Db::name('ask')->where(['user_id'=>$this->_userId,'is_show'=>1,'is_reply'=>$is_reply])->field('id,q,remark,addtime')->order('addtime desc,id desc')->limit($offset,$pageSize)->select();
        }else{
            $asks_yes=Db::name('ask a')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.user_id'=>$this->_userId,'a.is_show'=>1,'b.is_del'=>0,'a.is_reply'=>1])->field('a.id,a.q,a.remark,a.addtime,a.a,a.qa_class_id,b.title as qa_class_title,a.replytime,a.is_reply')->order('a.replytime desc,a.addtime desc,a.id desc')->select();
            foreach ($asks_yes as $k => $v) {
                $asks_yes[$k]['a']=strip_tags($v['a']);
            }
            $asks_no=Db::name('ask')->where(['user_id'=>$this->_userId,'is_show'=>1,'is_reply'=>0])->field('id,q,remark,addtime,is_reply')->order('addtime desc,id desc')->select();
            $all=array_merge($asks_yes,$asks_no);
            $all=list_sort_by($all,'addtime','desc');
            $asks=array_slice($all, $offset,$limit);
        }
        return $this->response($asks,$this->_successCode,'');
    }
    //提问
    public function add()
    {
        $this->checkNeedParam(['q'=>'请传入q']);
        $q=input('q');
        $remark=input('remark','');
        $ask=Db::name('ask')->where(['user_id'=>$this->_userId,'q'=>$q])->find();
        if($ask){
            return $this->response([],201,'该问题你已经提过了，请勿重复提问');
        }
        Db::name('ask')->insertGetId(['user_id'=>$this->_userId,'q'=>$q,'remark'=>$remark,'addtime'=>time()]);
        return $this->response([],$this->_successCode,'');
    }
}
