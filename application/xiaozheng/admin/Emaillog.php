<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Emaillog extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('send_email_log')->where($map)->value('count(id)');	
		$list_tab = [
	        '0' => ['title' => '邮件推送记录('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	    ];
	    $users=Db::name('user')->order('addtime desc,id desc')->select();
		$select_users=[];
		foreach ($users as $k => $v) {
			$nickname=$v['nickname'];
			if($v['status']==0) $nickname.='(已禁用)';
			$select_users[$v['id']]=$nickname;
		}
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('send_email_log')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('邮件推送记录') // 设置页面标题
		        	->setPageTips('定时查看邮件推送记录，可以更清楚系统运行信息') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('send_email_log') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','email']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('user_id', $select_users)  // 添加字段筛选
		            ->addFilter('status', ['失败','成功'])  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['user_id','会员昵称','callback','array_v',$select_users],
		        			['email','邮箱'],
		        			['detail_ids','推送清单','callback',function($detail_ids){
		        				if($detail_ids){
		        					$detail_ids=explode(',',$detail_ids);
		        					foreach ($detail_ids as $k => $v) {
		        						$detail_ids[$k]="<a href='".url('xiaozheng/detail/look',['id'=>$v])."' target='_bank' title='点击查看清单详情'>{$v}</a>";
		        					}
		        					return implode('&nbsp;,&nbsp;',$detail_ids);
		        				}else{
		        					return no_font('无');
		        				}
		        			}],
		        			['status','状态','status','',['失败','成功']],
		        			['addtime', '推送时间','datetime',no_font('未知')],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看推送邮件','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true)
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	    }
	}
	public function look($id=''){
		$email=Db::name("send_email_log")->where('id',$id)->find();
		if(!$email){
			return $this->error('请求错误');
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看推送邮件') // 设置页面标题
			//->setPageTips('以下是政策的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('title', '标题','',$email['title'])
			->addStatic('content', '内容','',staticText($email['content']))
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
}