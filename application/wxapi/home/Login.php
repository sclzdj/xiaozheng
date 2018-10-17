<?php
namespace app\wxapi\home;
use think\Db;

class Login extends Api
{
    protected function _initialize()
    {
        // 系统开关
        if (!config('web_site_status')) {
            return $this->response([], $this->_webSiteCloseCode, '站点已经关闭，请稍后访问');
        }
        $this->_requestLog();
        //检查合法性
        $this->checkNeedParam(['3rd_session'=>'请传入3rd_session']);
        $rd_session=input('3rd_session');
        $rd=Db::name('3rd_session a')->join('user b','a.user_id=b.id','LEFT')->where('a.3rd_session',$rd_session)->field('a.*,b.id as true_id,b.status')->order('a.id desc')->find();
        if(!$rd || $rd['true_id']<=0){
            return $this->response([], $this->_notLoginCode, '无效3rd_session，请登录');
        }
        if($rd['status']==0){
            return $this->response([], $this->_notLoginCode, '此账号已被禁用，不能登录');
        }
        if($rd['expire']<=time()){
            return $this->response([], $this->_notLoginCode, '登录状态已过期，请重新登录');
        }
        $this->_userId=$rd['user_id'];
    }
    public function _getUserInfo($fields='',$pix=false){
        if(is_array($fields)){
            $fields=implode(',', $fields);
        }
        return Db::name('user')->field($fields,$pix)->find($this->_userId);
    }
}
