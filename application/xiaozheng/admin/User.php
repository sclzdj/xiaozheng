<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class User extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('user')->where($map)->value('count(id)');	
		$list_tab = [
	        '0' => ['title' => '会员列表('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	    ];
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('user')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('会员列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作<br>未授权的会员用户名和头像是系统生成的') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('user') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','nickname','email']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('status', ['禁用','正常'])  // 添加字段筛选
		            ->addFilter('is_auth', ['未授权','已授权'])  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['nickname','昵称'],
		        			['avatarurl','头像','callback',function($avatarurl){
		        				if($avatarurl){
		        					return "<a href='{$avatarurl}' target='_bank'><img src='{$avatarurl}' width='40'></a>";
		        				}else{
		        					return no_font('无');
		        				}
		        			}],
		        			['email','邮箱'],
		        			['is_auth', '微信授权','status','', ['未授权','已授权']],
		        			['addtime', '注册时间','datetime',no_font('未知')],
		        			['status', '状态','switch'],
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