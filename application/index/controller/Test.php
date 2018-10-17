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

namespace app\index\controller;
/**
 * 测试控制器
 * @package app\index\controller
 */
class Test extends Home{
    public function index(){
    	$result = sendRequestUrl('api/index/index',['type'=>0,'version'=>1],'',1,'1234567890');
        echo($result);
        //echo '<form action="'.url('index/upload/file').'" method="post" enctype="multipart/form-data"><input name="file" type="file" /><input type="submit" value="上传" /></form>';//上传图片接口测试
    }
}
