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

use app\common\controller\Common;

/**
 * 前台公共控制器
 * @package app\index\controller
 */
class Api extends Common
{
    protected $_notLoginCode=501;//未登录
    /**
     * API通用输出
     *
     * @param array|mix $data            
     * @param int|错误码 $errcode            
     * @param string $msg            
     */
    protected function response($data, $errcode, $msg){
        if($errcode == $this->_notLoginCode){
            $msg = '对不起,您的登录状态已失效,请重新登录!';
        }
        $data = !$data ? [] : $data;
        $result = [
            'data' => $data,
            'errcode' => intval($errcode),
            'msg' => $msg
        ];
        $result = json_encode($result, JSON_UNESCAPED_UNICODE);
        echo ($result);
        die();
    }
    //检查必传参数
    protected function checkNeedParam($param){
        foreach ($param as $k => $v) {
            if(input($k)===null){
                $this->response([],'300',$v);
            }
        }
    }
}
