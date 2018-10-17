<?php
namespace app\wxapi\home;
use think\Db;

class Interact extends Home
{
    //评论列表
    public function index()
    {
    	$this->checkNeedParam(['relate'=>'请传入relate','relate_id'=>'请传入relate_id']);
        $relate=input('relate');
        $relate_id=(int)input('relate_id');
        $page=input('page',1);
        $pageSize=input('pageSize',10);
        $offset=$pageSize*($page-1);
        $relate_arr=['policy','qa'];
        if(!in_array($relate, $relate_arr)) return $this->response([],401,'relate参数传入错误');
        $datas=Db::name('interact a')->join('user b','a.user_id=b.id')->where(['a.is_show'=>1,'a.is_public'=>1,'a.audit'=>1,'a.relate'=>$relate,'a.relate_id'=>$relate_id,'b.is_del'=>0,'a.pid'=>0])->field('a.id,a.user_id,b.nickname,b.avatarurl,a.addtime,a.pubtime,a.content,a.pid')->order('a.addtime desc,a.pubtime desc,a.id desc')->limit($offset,$pageSize)->select();
        foreach ($datas as $key => $value) {
            if($value['avatarurl']==''){
                $datas[$key]['avatarurl']=config('static_url').'admin/img/default.jpg';
            }
        }
        foreach ($datas as $k => $v) {
            $da=Db::name('interact a')->join('user b','a.user_id=b.id')->where(['a.is_show'=>1,'a.is_public'=>1,'a.audit'=>1,'b.is_del'=>0,'a.id'=>$v['pid']])->field('a.id,a.user_id,b.nickname,b.avatarurl,a.addtime,a.pubtime,a.content')->find();
            if($da){
                $datas[$k]['parent']=$da;
                if($da['avatarurl']==''){
                    $da['avatarurl']=config('static_url').'admin/img/default.jpg';
                }
            }else{
                $datas[$k]['parent']=[];
            }
            unset($datas[$k]['pid']);
            $childs=Db::name('interact a')->join('user b','a.user_id=b.id')->where(['a.is_show'=>1,'a.is_public'=>1,'a.audit'=>1,'b.is_del'=>0,'a.root_id'=>$v['id'],'a.pid'=>['neq',0]])->field('a.id,a.user_id,b.nickname,b.avatarurl,a.addtime,a.pubtime,a.content,a.pid')->order('a.addtime desc,a.pubtime desc,a.id desc')->limit(0,config('xiaozheng.interact_default_reply_num'))->select();
            foreach ($childs as $key => $value) {
                if($value['avatarurl']==''){
                    $childs[$key]['avatarurl']=config('static_url').'admin/img/default.jpg';
                }
                if($value['pid']!=$v['id']){
                    $user_id=Db::name('interact')->where('id',$value['pid'])->value('user_id');
                    if($value['user_id']!=$user_id && $user_id!=$v['user_id']){
                        $nickname=Db::name('user')->where('id',$user_id)->value('nickname');
                        $childs[$key]['content']="@{$nickname} ".$value['content'];
                    }
                }
                unset($childs[$key]['pid']);
            }
            $datas[$k]['childs']=$childs;
        }
        return $this->response($datas,$this->_successCode,"");
    }
    //某评论的回复
    public function replylst()
    {
        $this->checkNeedParam(['pid'=>'请传入pid']);
        $pid=(int)input('pid');
        $page=input('page',1);
        $pageSize=input('pageSize',10);
        $offset=$pageSize*($page-1)+config('xiaozheng.interact_default_reply_num');
        $da=Db::name('interact a')->join('user b','a.user_id=b.id')->where(['a.is_show'=>1,'a.is_public'=>1,'a.audit'=>1,'b.is_del'=>0,'a.id'=>$pid])->field('a.id,a.user_id,b.nickname,b.avatarurl,a.addtime,a.pubtime,a.content,a.pid')->find();
        if(!$da) return $this->response([],201,'pid参数无效');
        $datas=Db::name('interact a')->join('user b','a.user_id=b.id')->where(['a.is_show'=>1,'a.is_public'=>1,'a.audit'=>1,'b.is_del'=>0,'a.root_id'=>$pid,'a.pid'=>['neq',0]])->field('a.id,a.user_id,b.nickname,b.avatarurl,a.addtime,a.pubtime,a.content,a.pid')->order('a.addtime desc,a.pubtime desc,a.id desc')->limit($offset,$pageSize)->select();
        foreach ($datas as $key => $value) {
            if($value['avatarurl']==''){
                $datas[$key]['avatarurl']=config('static_url').'admin/img/default.jpg';
            }
            if($value['pid']!=$pid){
                $user_id=Db::name('interact')->where('id',$value['pid'])->value('user_id');
                if($value['user_id']!=$user_id && $user_id!=$da['user_id']){
                    $nickname=Db::name('user')->where('id',$user_id)->value('nickname');
                    $datas[$key]['content']="@{$nickname} ".$value['content'];
                }
            }
            unset($datas[$key]['pid']);
        }
        return $this->response($datas,$this->_successCode,"");
    }
}