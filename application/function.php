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

// 为方便系统核心升级，二次开发中需要用到的公共函数请写在这个文件，不要去修改common.php文件

/**
 * 随机字符
 * @param number $length 长度
 * @param string $type 类型
 * @param number $convert 转换大小写
 * @return string
 */
use think\Db;

if (! function_exists('random')) {
    function random($length=4, $type='all', $convert=0){
        $config = array(
            'number'=>'1234567890',
            'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'small'=>'abcdefghijklmnopqrstuvwxyz',
            'big'=>'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
            'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );
        
        if(!isset($config[$type])) $type = 'string';
        $string = $config[$type];
        
        $code = '';
        $strlen = strlen($string) -1;
        for($i = 0; $i < $length; $i++){
            $code .= $string{mt_rand(0, $strlen)};
        }
        if(!empty($convert)){
            $code = ($convert > 0)? strtoupper($code) : strtolower($code);
        }
        return $code;
    }
}


//字符串连接
if(!function_exists('str_linked')){
    function str_linked($str1='',$str2='')
    {
        return $str1.$str2;
    }
}

//取数组每个键值
if(!function_exists('array_v')){
    function array_v($key,$arr=array())
    {
        return $arr?(array_key_exists($key,$arr)?$arr[$key]:$arr[0]):'';

    }
}

//null转为空字符串
if(!function_exists('null2')){
    function null2($data){
        if(is_array($data)){
            return array_map('null2',$data);
        }else{
            if($data===null || $data==='null')
                return $data='';
            else
                return $data;
        }
    }
}

//手机号码保护
if(!function_exists('phoneDis')){
    function phoneDis($arr,$pix=false,$keys=array('username')){
        if($pix){
            foreach ($arr as $key => $value) {
                if(in_array($key, $keys) && preg_match('/^1\d{10}$/', $value)){
                    $arr[$key]=substr_replace($value, '****', 3, 4);
                }
            }
        }else{
            foreach ($arr as $k => $v) {
                foreach ($v as $key => $value) {
                    if(in_array($key, $keys) && preg_match('/^1\d{10}$/', $value)){
                        $arr[$k][$key]=substr_replace($value, '****', 3, 4);
                    }
                }
            }
        }
            
        return $arr;
    }
}

if(!function_exists('makeSign'))
{
    /**
     * 计算SIGN
     * @param $data
     * @param $appkey
     */
    function makeSign($data,$appkey)
    {
        ksort($data, SORT_STRING);
        $makeSign = '';
        foreach ($data as $v) {
            if (is_array($v)) {
                $makeSign .= $v[0];
            } else {
                $makeSign .= $v;
            }
        }
        $makeSign .= $appkey;
        $makeSign = md5($makeSign);
        return $makeSign;
    }
}

if(!function_exists('sendRequestUrl')) {
    /**
     * 调用API请求
     * @param $action API接口名 比如：api/user/login
     * @param $data 接口参数
     * @return 可以请求的url
     */
    function sendRequestUrl($action, $data ,$token = '', $appid = '',$appkey = '')
    {
        if(!$appid && !$appkey){
        	$api=db('api_config')->limit(1)->find();
        	$appid=$api['appid'];
        	$api_appkey=$api['api_appkey'];
        }
        $host = config('root_url');
        $url = $host  .'/'. $action.'?';
        $data['appid'] = $appid;
        $data['timeline'] = time();
        $data['token'] = $token;
        $data['sign'] = makeSign($data, $appkey);
        $url .= http_build_query($data);
        return $url;
    }
}

if(!function_exists('no_font')){
    function no_font($font,$tag="span",$size="12",$color="#ccc"){
        return "<{$tag} style='font-size:{$size}px;color:{$color};'>{$font}</{$tag}>";
    }
}

if(!function_exists('user_nickname')){
    function user_nickname($id=0){
        $nickname=db('user')->where('id',$id)->value('nickname');
        if($nickname===null){
            $nickname=no_font('未知');
        }
        return $nickname;
    }
}

if(!function_exists('admin_username')){
    function admin_username($id=0){
        $username=db('admin_user')->where('id',$id)->value('username');
        if($username===null){
            $username=no_font('未知');
        }
        return $username;
    }
}

if(!function_exists('audit_run')){
    function audit_run($data=[],$status='0',$menu_id='0',$pix=false,$url='audit',$table='',$field='audit',$id='id'){
        if(!if_menu_auth($menu_id)) return '';
        if(isset($data[$field])&&isset($data[$id])){
            if($data[$field]==0 && !$pix){
                return "<a href='".url($url,['ids'=>$data[$id],'audit'=>'1','status'=>$status])."' title='点击通过' class='btn btn-xs btn-default ajax-get'><i class='fa fa-fw fa-calendar-check-o'></i></a><a href='".url($url,['ids'=>$data[$id],'audit'=>'2','status'=>$status])."' title='点击不通过' class='btn btn-xs btn-default ajax-get'><i class='fa fa-fw fa-calendar-times-o'></i></a>";
            }
            if($data[$field]==2 || $pix){
                return "<a href='".url($url,['ids'=>$data[$id],'audit'=>'1','status'=>$status])."' title='点击通过' class='btn btn-xs btn-default ajax-get'><i class='fa fa-fw fa-calendar-check-o'></i></a>";
            }
        }
        if(!isset($data[$id])) return false;
        $value=db($table)->where('id',$data[$id])->value($field);
        if($value==0){
            return "<a href='".url($url,['ids'=>$data[$id],'audit'=>'1','status'=>$status])."' title='点击通过'><i class='fa fa-fw fa-calendar-check-o'></i></a><a href='".url($url,['ids'=>$data[$id],'audit'=>'2','status'=>$status])."'><i class='fa fa-fw fa-calendar-times-o' title='点击不通过'></i></a>";
        }
        if($value==2){
            return "<a href='".url($url,['ids'=>$data[$id],'audit'=>'1','status'=>$status])."' title='点击通过'><i class='fa fa-fw fa-calendar-check-o'></i></a>";
        }
    }
}

if(!function_exists('staticText')){
    function staticText($data='',$type='view',$pp='Y-m-d H:i'){
        if($type=='view'){
            return "<div>{$data}</div>";
        }elseif($type=='url'){
            if(!$data)  return no_font('暂无');
            return "<a href='{$data}' target='_bank' title='点击打开'>{$data}</a>";
        }elseif($type=='pic'){
            if(!$data)  return no_font('暂无');
            return "<a href='".get_file_path($data)."' target='_bank' title='".get_file_name($data)."'><img src='".get_file_path($data)."' style='width:80px;'></a>";
        }elseif ($type=='pics') {
            if(!$data)  return no_font('暂无');
            $html="";
            if(!is_array($data)){
                $data=explode(',',$data);
            }
            foreach ($data as $k => $v) {
                if(!$data)  return no_font('暂无');
                $html.="<a href='".get_file_path($v)."' target='_bank' title='".get_file_name($v)."'><img src='".get_file_path($v)."' style='width:80px;'></a>";
            }
            return $html;
        }elseif($type=='file'){
            if(!$data)  return no_font('暂无');
            return "<a href='".get_file_path($data)."' title='".get_file_name($data)."'>".get_file_name($data)."</a>";
        }elseif ($type=='files') {
            if(!$data)  return no_font('暂无');
            $html="";
            if(!is_array($data)){
                $data=explode(',',$data);
            }
            foreach ($data as $k => $v) {
                $html.="<li><a href='".get_file_path($v)."' title='".get_file_name($v)."'>".get_file_name($v)."</a></li>";
            }
            return $html;
        }elseif($type=='time'){
            if(!$data)  return no_font('未知');
            return date($pp,$data);
        }elseif($type=='admin_username'){
            if(!$data)  return no_font('未知');
            return admin_username($data);
        }else{
            if(!$data)  return no_font('暂无');
            return $data;
        }
    }
} 

if(!function_exists('issetArrOffset')){
    function issetArrOffset($arr_offset,$return=''){
        if(isset($arr_offset)){
            return $arr_offset;
        }else{
            return $return;
        }
    }
}

if(!function_exists('if_menu_auth')){
    function if_menu_auth($menu_id,$admin_id=UID){
        $role=db('admin_user')->where('id',$admin_id)->value('role');
        if($role=='1') return true;
        $menu_auth=db('admin_role')->where('id',$role)->value('menu_auth');
        if(strpos($menu_auth,'"'.$menu_id.'"')===false){
            return false;
        }else{
            return true;
        }
    }
}

if(!function_exists('get_comment')){
    function get_comment($table_name){
        return db()->query("select table_comment from information_schema.tables where table_name ='".config('database.prefix')."{$table_name}'")[0]['table_comment'];
    }
}

if(!function_exists('make_3rd_session')){
    function make_3rd_session(){
        $rd_session=random(64);
        $rd=db('3rd_session')->where('3rd_session',$rd_session)->find();
        if($rd){
            return make_3rd_session();
        }else{
            return $rd_session;
        }
    }
}

if(!function_exists('handle_arr_city')){
    function handle_arr_city($arr,$two=true,$name='city'){
        if($two){
            foreach ($arr as $k => $v) {
                if(!(isset($arr[$k]['sheng']) && isset($arr[$k]['shi']) && !isset($arr[$k][$name]))) continue;
                $shi=Db::name('city')->where('id',$v['shi'])->value('title');
                if($shi!==null){
                    $arr[$k][$name]=$shi;
                }else{
                    $sheng=Db::name('city')->where('id',$v['sheng'])->value('title');
                    if($sheng!==null){
                        $arr[$k][$name]=$sheng;
                    }else{
                        $arr[$k][$name]=Db::name('city')->where('id','1')->value('title');
                    }
                }
                unset($arr[$k]['sheng']);
                unset($arr[$k]['shi']);
            }
        }else{
            if(isset($arr['sheng']) && isset($arr['shi']) && !isset($arr[$name])){
                $shi=Db::name('city')->where('id',$arr['shi'])->value('title');
                if($shi!==null){
                    $arr[$name]=$shi;
                }else{
                    $sheng=Db::name('city')->where('id',$arr['sheng'])->value('title');
                    if($sheng!==null){
                        $arr[$name]=$sheng;
                    }else{
                        $arr[$name]=Db::name('city')->where('id','1')->value('title');
                    }
                }
                unset($arr['sheng']);
                unset($arr['shi']);
            }
        }
        return $arr;
    }
}

if(!function_exists('handle_arr_pic')){
    function handle_arr_pic($arr,$two=true,$filed='pic_id',$no_image=false){
        if($no_image===false) $no_image=config('static_url').'admin/img//nopic.gif';
        if($two){
            foreach ($arr as $k => $v) {
                $path=model('admin/attachment')->getFilePath($v[$filed]);
                if($path){
                    $arr[$k]['pic']=config('public_url').$path;
                }else{
                    $arr[$k]['pic']=$no_image;
                }
                unset($arr[$k][$filed]);
            }
        }else{
            $path=model('admin/attachment')->getFilePath($arr[$filed]);
            if($path){
                $arr['pic']=config('public_url').$path;
            }else{
                $arr['pic']=$no_image;
            }
            unset($arr[$filed]);
        }
        return $arr;
    }
}

if(!function_exists('handle_arr_pics')){
    function handle_arr_pics($arr,$two=true,$filed='pic_ids',$no_image=false){
        if($no_image===false) $no_image=config('static_url').'admin/img/nopic.gif';
        if($two){
            foreach ($arr as $k => $v) {
                $arr[$k]['pics']=[];
                $pic_ids=explode($v[$filed]);
                foreach ($pic_ids as $key => $value) {
                    $path=model('admin/attachment')->getFilePath($value);
                    if($path){
                        $arr[$k]['pics'][]=config('public_url').$path;
                    }else{
                        $arr[$k]['pics'][]=$no_image;
                    }
                }
                unset($arr[$k][$filed]);
            }
        }else{
            $arr['pics']=[];
            $pic_ids=explode($arr[$filed]);
            foreach ($pic_ids as $key => $value) {
                $path=model('admin/attachment')->getFilePath($value);
                if($path){
                    $arr['pics'][]=config('public_url').$path;
                }else{
                    $arr['pics'][]=$no_image;
                }
            }
            unset($arr[$filed]);
        }
        return $arr;
    }
}

/**
 * 对查询结果集进行排序
 * http://www.onethink.cn
 * /Application/Common/Common/function.php
 *
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param string $sortby 排序类型 （asc正向排序 desc逆向排序 nat自然排序）
 * @return array
 */
if (! function_exists('list_sort_by'))
{
    function list_sort_by($list, $field, $sortby = 'asc')
    {
        if (is_array($list))
        {
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
            {
                $refer[$i] = &$data[$field];
            }
            switch ($sortby)
            {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc': // 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val)
            {
                $resultSet[] = &$list[$key];
            }
            return $resultSet;
        }
        return false;
    }
}

//导出xls
if (! function_exists('exportexcel')) {
    function exportexcel($data=array(),$title=array(),$filename='report',$pix=true){
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel;");  
        header("Content-Disposition:attachment;filename=".$filename.".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        //导出xls 开始
        if (!empty($title)){
            foreach ($title as $k => $v) {
                if($pix) $title[$k]=iconv("UTF-8","GB2312",$v);
            }
            $title= implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($data)){
            foreach($data as $key=>$val){
                foreach ($val as $ck => $cv) {
                    $cv=str_replace(["\t","\r","\n"], [" "," "," "], $cv);
                    if($pix) $data[$key][$ck]=iconv("UTF-8","GB2312",$cv);
                }
                $data[$key]=implode("\t", $data[$key]);
                
            }
            echo implode("\n",$data);
        }
    }
}


if (! function_exists('handle_view_pic')) {
    function handle_view_pic($content){
        return str_replace(['src="',"src='"], ['src="'.config('host_url'),"src='".config('host_url')], $content);
    }
}

if (! function_exists('sendToMail')) {
    function sendToMail($to, $title, $content)
    {
        //date_default_timezone_set('PRC'); //东八时区
        require_once('./lib/PHPMailer_v5.1/class.phpmailer.php');
        $mail = new \PHPMailer();
        // 设置为要发邮件
        $mail->IsSMTP();
        // 是否允许发送HTML代码做为邮件的内容
        $mail->IsHTML(TRUE);
        // 是否需要身份验证
        $mail->SMTPAuth=TRUE;
        $mail->CharSet='UTF-8';
        $mail->SMTPSecure='ssl';
        /*  邮件服务器上的账号是什么 */
        $mail->From='service@clinkconsulting.com';
        $mail->FromName='晓政';
        $mail->Host='ssl://smtp.mxhichina.com';
        $mail->Username='service@clinkconsulting.com';
        $mail->Password='Clink112233';
        // 发邮件端口号默认25
        $mail->Port = 465;
        // 收件人
        $mail->AddAddress($to);
        // 邮件标题
        $mail->Subject=$title;
        // 邮件内容
        $mail->Body=$content;
        $ret=$mail->Send();
        $mail->SmtpClose();
        return($ret);
    }
}

if(! function_exists('myfuc')){
    function myfuc($id){
        $menu_auth=Db::name('admin_role')->where('id',$id)->value('menu_auth');
        $menu_auth=json_decode($menu_auth,true);
        Db::name('admin_menu')->where('id','in',$menu_auth)->update(['is_show'=>1]);
    }
}

if(! function_exists('allarrtrim')){
    function allarrtrim($data){
        if(is_array($data)){
            return array_map('allarrtrim', $data);
        }else{
            return trim($data);
        }
    }
}

if(! function_exists('allarraddslashes')){
    function allarraddslashes($data){
        if(is_array($data)){
            return array_map('allarraddslashes', $data);
        }else{
            return addslashes($data);
        }
    }
}

if(! function_exists('curPageURL')){
    function curPageURL() 
    {
      $pageURL = 'http';
     
      if ($_SERVER["HTTPS"] == "on") 
      {
        $pageURL .= "s";
      }
      $pageURL .= "://";
     
      if ($_SERVER["SERVER_PORT"] != "80") 
      {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
      } 
      else
      {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
      }
      return $pageURL;
    }
}

if(! function_exists('getAgentInfo')){
    function getAgentInfo(){  
        $agent = $_SERVER['HTTP_USER_AGENT'];  
        $brower = array(  
            'MSIE',  
            'Firefox',  
            'QQBrowser',  
            'QQ/',  
            'UCBrowser',  
            'MicroMessenger',  
            'Edge',  
            'Chrome',  
            'Opera',  
            'OPR',  
            'Safari',  
            'Trident/',  
        );  
        $system = array(  
            'Windows Phone',  
            'Windows',  
            'Android',  
            'iPhone',  
            'iPad',  
        );  
        $browser_info = '';//未知  
        $system_info = '';//未知  
        foreach($brower as $k => $bro){  
            if(stripos($agent, $bro) !== false){  
                $browser_info = $bro;  
                break;  
            }  
        }  
        foreach($system as $k => $sys){  
            if(stripos($agent, $sys) !== false){  
                $system_info = $sys;  
                break;  
            }  
        }
        $reIP=$_SERVER["REMOTE_ADDR"];   
        $url=curPageURL();
        if($system_info==='' && stripos($agent, 'MicroMessenger')!==false){
            $system_info='WeChat applet';
        }
        return array('system' => $system_info, 'brower' => $browser_info,'ip'=>$reIP,'url'=>$url,'agent'=>$agent);  
    }
}

