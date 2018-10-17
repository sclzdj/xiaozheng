<?php
namespace app\wxapi\home;
use think\Db;

class Home extends Api
{
    protected function _initialize()
    {   
        // 系统开关
        if (!config('web_site_status')) {
            return $this->response([], $this->_webSiteCloseCode, '站点已经关闭，请稍后访问');
        }
        $this->_requestLog();
    }
    protected function isLogin(){
    	$rd_session=input('3rd_session','');
    	if($rd_session==='') return false;
        $rd=Db::name('3rd_session a')->join('user b','a.user_id=b.id','LEFT')->where('a.3rd_session',$rd_session)->field('a.*,b.id as true_id,b.status')->order('a.id desc')->find();
        if(!$rd || $rd['true_id']<=0)	return false;
        if($rd['status']==0) return false;
        if($rd['expire']<=time())	return false;
        $this->_userId=$rd['user_id'];
        return true;
    }
}
