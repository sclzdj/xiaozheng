<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Timedtask extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('timed_task')->where($map)->value('count(id)');	
		$list_tab = [
	        '0' => ['title' => '服务器定时任务记录('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	    ];
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='time desc,id desc';
		        }
				$data_list = Db::name('timed_task')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('服务器定时任务记录') // 设置页面标题
		        	->setPageTips('定时任务一旦停止运行，请联系开发人员修复') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('timed_task') // 指定数据表名
		        	->addOrder('id,time') // 添加排序
		            ->setSearch(['id']) // 设置搜索参数
					->addTimeFilter('time') // 添加时间段筛选
		            ->addFilter('type', ['mail'=>'邮件推送','follow'=>'自动关注','city'=>'矫正数据'])  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['time', '执行时间','datetime',no_font('未知')],
		        			['type', '类型','callback','array_v', ['mail'=>'邮件推送','follow'=>'自动关注','city'=>'矫正数据']],
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