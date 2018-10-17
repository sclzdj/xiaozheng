<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Requestlog extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('request_log')->where($map)->value('count(id)');	
		$list_tab = [
	        '0' => ['title' => '接口请求信息记录('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	    ];
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('request_log')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('接口请求信息记录') // 设置页面标题
		        	->setPageTips('定时查看接口请求信息记录，可以更清楚系统运行信息') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('request_log') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','ip','system','brower','url','query']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['ip', 'IP'],
		        			['system', '系统'],
		        			['brower', '浏览器'],
		        			['url', '请求地址','callback',function($url){
		        				return "<div style='width:300px;word-wrap:break-word;'>{$url}</div>";
		        			}],
		        			['query', 'POST参数','callback',function($query){
		        				return "<div style='width:300px;word-wrap:break-word;'>{$query}</div>";
		        			}],
		        			['addtime', '请求时间','datetime',no_font('未知'),'Y-m-d H:i:s'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	    }
	}
}