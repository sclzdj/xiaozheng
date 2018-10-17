<?php
namespace app\wxapi\home;

use app\common\controller\Common;
use think\Db;

class Api extends Common
{
    protected $_successCode=200;//请求正确
    protected $_notLoginCode=501;//未登录
    protected $_webSiteCloseCode=511;//站点关闭
    protected $_notNeedParamCode=521;//未传必传参数
    protected $_userId;
    /**
     * API通用输出
     *
     * @param array|mix $data            
     * @param int|错误码 $errcode            
     * @param string $msg            
     */
    protected function response($data, $code, $msg){
        $data = !$data ? [] : $data;
        $result = [
            'data' => $data,
            'code' => intval($code),
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
                $this->response([],$this->_notNeedParamCode,$v);
            }
        }
    }
    /**
     * 请求函数
     * @param    $type 请求类型
     * @param    $url  请求服务器url
     * @param    $data post请求数据
     * @param    $ssl  是否为https协议 boolean类型
     * @return   返回请求结果
     */
    public function _request($type,$url,$data=[],$ssl=true){
        //提交方式
        if($type!='get'&&$type!='GET'&&$type!='post'&&$type!='POST'){
            return false;
        }
        //请求数据处理
        if(is_array($data)){
            $data=json_encode($data);
        } 
        //curl完成
        $curl=curl_init();
        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//请求url
        $user_agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'HTTP_USER_AGENT_'.$data;//配置代理信息
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);//设置请求时间
        //SSL相关
        if($ssl){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后curl将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个。。       
        }
        //post请求相关
        if($type=='post'||$type=='POST'){
            curl_setopt($curl, CURLOPT_POST, true);//是否为post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//处理post请求数据
        }
        //处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

        //发出请求
        $response=curl_exec($curl);
        if(false===$response){
            return false;
        }
        return $response;
    }
    public function _requestLog(){
        if(config('wxapi_requestlog_on')){
            $post=(array)input('post.');
            $query=http_build_query($post);
            $agent=getAgentInfo();
            $insert=$agent;
            $insert['addtime']=time();
            $insert['query']=$query;
            Db::name('request_log')->insertGetId($insert);
        } 
    }
}
