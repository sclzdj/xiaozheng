<?php
namespace app\wxapi\home;
use think\Db;

class Index extends Login
{
    public function index()
    {
        return $this->response(['id'=>1],$this->_successCode,'是吗');
    }
    //测试
    public function test()
    {
        return $this->response(['str'=>"hello"],$this->_successCode,'晓政接口测试（已登录）');
    }
    //点赞和点踩
    public function laud_tread(){
    	$this->checkNeedParam(['type'=>'请传入type','relate'=>'请传入relate','relate_id'=>'请传入relate_id']);
    	$type=(int)input('type');
    	if($type>=0) $type=1;else $type=-1;
    	$relate_id=(int)input('relate_id');
    	$relate=input('relate');
    	$relate_arr=['detail','qa'];
    	if($type==1){
    		$pix='赞';
    	}else{
    		$pix='踩';
    	}
    	if(!in_array($relate, $relate_arr)) return $this->response([],401,'relate参数传入错误');
    	$real=Db::name($relate)->where('id',$relate_id)->find();
    	if(!$real){
    		return $this->response([],201,"数据不存在");
    	}
		$laud_tread=Db::name('laud_tread')->where(['user_id'=>$this->_userId,'relate'=>$relate,'relate_id'=>$relate_id])->find();
		if($laud_tread){
			if($laud_tread['type']==$type){
				return $this->response([],202,"你已经{$pix}过了");
			}else{
				if($laud_tread['type']==1){
    				Db::name($relate)->where('id',$relate_id)->setDec('laud');
    			}else{
    				Db::name($relate)->where('id',$relate_id)->setDec('tread');
    			}
    			Db::name('laud_tread')->update(['id'=>$laud_tread['id'],'type'=>$type,'addtime'=>time()]);
			}
		}else{
			Db::name('laud_tread')->insertGetId(['user_id'=>$this->_userId,'relate'=>$relate,'relate_id'=>$relate_id,'type'=>$type,'addtime'=>time()]);
		}
		if($type==1){
			Db::name($relate)->where('id',$relate_id)->setInc('laud');
		}else{
			Db::name($relate)->where('id',$relate_id)->setInc('tread');
		}
		return $this->response([],$this->_successCode,"");
    }
    //取消赞和取消踩
    public function cancel_laud_tread(){
    	$this->checkNeedParam(['type'=>'请传入type','relate'=>'请传入relate','relate_id'=>'请传入relate_id']);
    	$type=(int)input('type');
    	if($type>=0) $type=1;else $type=-1;
    	$relate_id=(int)input('relate_id');
    	$relate=input('relate');
    	$relate_arr=['detail','qa'];
    	if($type==1){
    		$pix='赞';
    	}else{
    		$pix='踩';
    	}
    	if(!in_array($relate, $relate_arr)) return $this->response([],401,'relate参数传入错误');
    	$real=Db::name($relate)->where('id',$relate_id)->find();
    	if(!$real){
    		return $this->response([],201,"数据不存在");
    	}
		$laud_tread=Db::name('laud_tread')->where(['user_id'=>$this->_userId,'relate'=>$relate,'relate_id'=>$relate_id,'type'=>$type])->find();
		if(!$laud_tread){
			return $this->response([],202,"你还没有{$pix}过");
		}
		Db::name('laud_tread')->delete($laud_tread['id']);
		if($type==1){
			Db::name($relate)->where('id',$relate_id)->setDec('laud');
		}else{
			Db::name($relate)->where('id',$relate_id)->setDec('tread');
		}
		return $this->response([],$this->_successCode,"");
    }
    //关注
    public function follow(){
    	$this->checkNeedParam(['relate'=>'请传入relate','relate_id'=>'请传入relate_id']);
    	$relate_id=(int)input('relate_id');
    	$relate=input('relate');
    	$relate_arr=['detail','qa'];
    	if(!in_array($relate, $relate_arr)) return $this->response([],401,'relate参数传入错误');
    	$real=Db::name($relate)->where('id',$relate_id)->find();
    	if(!$real){
    		return $this->response([],201,"数据不存在");
    	}
		$follow=Db::name('follow')->where(['user_id'=>$this->_userId,'relate'=>$relate,'relate_id'=>$relate_id])->find();
		if($follow){
			return $this->response([],202,"你已经关注过了");
		}
		Db::name('follow')->insertGetId(['user_id'=>$this->_userId,'relate'=>$relate,'relate_id'=>$relate_id,'addtime'=>time()]);
		Db::name($relate)->where('id',$relate_id)->setInc('follow');
		return $this->response([],$this->_successCode,"");
    }
    //取消关注
    public function cancel_follow(){
    	$this->checkNeedParam(['relate'=>'请传入relate','relate_id'=>'请传入relate_id']);
    	$relate_id=(int)input('relate_id');
    	$relate=input('relate');
    	$relate_arr=['detail','qa'];
    	if(!in_array($relate, $relate_arr)) return $this->response([],401,'relate参数传入错误');
    	$real=Db::name($relate)->where('id',$relate_id)->find();
    	if(!$real){
    		return $this->response([],201,"数据不存在");
    	}
		$follow=Db::name('follow')->where(['user_id'=>$this->_userId,'relate'=>$relate,'relate_id'=>$relate_id])->find();
		if(!$follow){
			return $this->response([],202,"你还没有关注过");
		}
		Db::name('follow')->delete($follow['id']);
		Db::name($relate)->where('id',$relate_id)->setDec('follow');
		return $this->response([],$this->_successCode,"");
    }
    //我的关注
    public function myfollows(){
        $this->checkNeedParam(['relate'=>'请传入relate']);
        $relate=input('relate');
        $page=input('page',1);
        $pageSize=input('pageSize',10);
        $offset=$pageSize*($page-1);
        $relate_arr=['detail','qa'];
        if(!in_array($relate, $relate_arr)) return $this->response([],401,'relate参数传入错误');
        if($relate=='detail'){
            $datas=Db::name('follow f')->join($relate.' a','f.relate_id=a.id','LEFT')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.audit'=>1,'b.is_del'=>0,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0,'f.relate'=>$relate,'f.user_id'=>$this->_userId])->field('f.id,f.addtime,f.relate_id,a.remark,a.pubtime,b.sheng,b.shi,a.policy_id,b.title as policy_title,a.career_id,c.title as career_title,a.ident_id,d.title as ident_title,a.category_id,e.title as category_title,a.click,a.laud,a.tread,a.follow,a.source')->order('f.addtime desc,f.id desc,a.pubtime desc')->limit($offset,$pageSize)->select();
            $datas=handle_arr_city($datas);
        }
        if($relate=='qa'){
            $datas=Db::name('follow f')->join($relate.' a','f.relate_id=a.id','LEFT')->join('qa_class b','a.qa_class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'f.relate'=>$relate,'f.user_id'=>$this->_userId])->field('f.id,f.addtime,f.relate_id,a.q,a.a,a.pubtime,a.qa_class_id,b.title as qa_class_title,a.click,a.laud,a.tread,a.follow')->order('f.addtime desc,f.id desc,a.pubtime desc,a.id desc')->limit($offset,$pageSize)->select();
            foreach ($datas as $k => $v) {
                $datas[$k]['a']=strip_tags($v['a']);
            }
        }
        return $this->response($datas,$this->_successCode,"");
    }
    //评论
    public function comment(){
        $this->checkNeedParam(['relate'=>'请传入relate','relate_id'=>'请传入relate_id','content'=>'请传入content','is_public'=>'请传入is_public']);
        $relate=input('relate');
        $relate_id=(int)input('relate_id');
        $content=input('content');
        $content=mb_substr($content,0,config('xiaozheng.interact_length'),'utf-8'); 
        $is_public=(int)input('is_public');
        $is_public=$is_public>0?1:0;
        $relate_arr=['policy','qa'];
        if(!in_array($relate, $relate_arr)) return $this->response([],401,'relate参数传入错误');
        $data=Db::name($relate)->where(['is_del'=>0,'audit'=>1])->find($relate_id);
        if(!$data) return $this->response([],201,'relate_id参数无效');
        $insert_id=Db::name('interact')->insertGetId(['user_id'=>$this->_userId,'addtime'=>time(),'relate'=>$relate,'relate_id'=>$relate_id,'content'=>$content,'is_public'=>$is_public,'is_show'=>1]);
        Db::name('interact')->update(['id'=>$insert_id,'root_id'=>$insert_id]);
        return $this->response([],$this->_successCode,"");
    }
    //回复
    public function reply(){
        $this->checkNeedParam(['pid'=>'请传入pid','content'=>'请传入content','is_public'=>'请传入is_public']);
        $pid=(int)input('pid');
        $content=input('content');
        $content=mb_substr($content,0,config('xiaozheng.interact_length'),'utf-8'); 
        $is_public=(int)input('is_public');
        $is_public=$is_public>0?1:0;
        $data=Db::name('interact a')->join('user b','a.user_id=b.id')->where(['a.is_show'=>1,'a.is_public'=>1,'a.audit'=>1,'b.is_del'=>0,'a.id'=>$pid])->find();
        if(!$data) return $this->response([],201,'pid参数无效');
        Db::name('interact')->insertGetId(['user_id'=>$this->_userId,'addtime'=>time(),'relate'=>$data['relate'],'relate_id'=>$data['relate_id'],'content'=>$content,'is_public'=>$is_public,'is_show'=>1,'pid'=>$pid,'root_id'=>$data['root_id']]);
        return $this->response([],$this->_successCode,"");
    }
}
