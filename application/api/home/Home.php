<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\api\home;


/**
 * 前台公共控制器
 * @package app\index\controller
 */
class Home extends Api
{
    /**
     * 初始化方法
     * @author 蔡伟明 <314013107@qq.com>
     */
    protected function _initialize()
    {
        //检查合法性
        $this->checkNeedParam(['appid'=>'请传入APPID','timeline'=>'请传入当前时间戳','token'=>'请传入登录TOKEN','sign'=>'请传入签名']);
        $data=(array)input();
        array_shift($data);
        $sign=$data['sign'];
        unset($data['sign']);
        $appkey=db('api_config')->where('appid',$data['appid'])->value('appkey');
        if($sign!=makeSign($data, $appkey)){
            return $this->response([], 600, '签名验证错误');
        }
        // 系统开关
        if (!config('web_site_status')) {
            return $this->response([], 400, '站点已经关闭，请稍后访问');
        }
    }
}
