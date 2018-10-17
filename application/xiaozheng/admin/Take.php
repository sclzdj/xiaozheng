<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Take extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$count_1=Db::name('Follow')->value('count(id)');	
		$list_tab = [
	        '0' => ['title' => '订阅列表('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	    ];
	    $users=Db::name('user')->order('addtime desc,id desc')->select();
		$select_users=[];
		foreach ($users as $k => $v) {
			$nickname=$v['nickname'];
			if($v['status']==0) $nickname.='(已禁用)';
			$select_users[$v['id']]=$nickname;
		}
		$career=Db::name('career')->order('sort asc,id desc')->select();
		$select_career=[];
		foreach ($career as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_career[$v['id']]=$title;
		}
		$ident=Db::name('ident')->order('sort asc,id desc')->select();
		$select_ident=[];
		foreach ($ident as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_ident[$v['id']]=$title;
		}
		$citys=[];
        $citys[]=Db::name('city')->field('id,title')->find(1);
        $shengs=Db::name('city')->where('pid',$citys[0]['id'])->field('id,title')->order('sort asc,id desc')->select();
        $shis=[];
        foreach ($shengs as $k => $v) {
            $shi=Db::name('city')->where('pid',$v['id'])->order('sort asc,id desc')->field('id,title')->select();
            $shis=array_merge($shis,$shi);
        }
        $citys=array_merge($citys,$shengs,$shis);
        $select_city=[];
        foreach ($citys as $k => $v) {
        	$select_city[$v['id']]=$v['title'];
        }
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('take')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('订阅列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('take') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('user_id', $select_users)  // 添加字段筛选
		            ->addFilter('city_id', $select_city)  // 添加字段筛选
		            ->addFilter('career_id', $select_career)  // 添加字段筛选
		            ->addFilter('ident_id', $select_ident)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['user_id','会员','callback','user_nickname'],
		        			['city_id', '地区','callback','array_v',$select_city],
		        			['career_id', '事业类型','callback','array_v',$select_career],
		        			['ident_id', '身份类型','callback','array_v',$select_ident],
		        			['addtime', '订阅时间','datetime',no_font('未知')],
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