<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Ask extends Admin
{
	public function index($status='0'){ 
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('ask')->where($map)->where('is_reply','1')->value('count(id)');
		$count_2=Db::name('ask')->where($map)->where('is_reply','0')->value('count(id)'); 
		$list_tab = [
	        '0' => ['title' => '已回答('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	        '1' => ['title' => '未回答('.$count_2.')', 'url' => url('index', ['status' => '1'])],
	    ];
	    $qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
		$select_qa_class=[0=>'无'];
		foreach ($qa_class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_qa_class[$v['id']]=$title;
		}
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
		            $order='replytime desc,addtime desc,id desc';
		        }
				$data_list = Db::name('ask')->where('is_reply','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('会员提问列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('ask') // 指定数据表名
		        	->addOrder('id,replytime,addtime') // 添加排序
		            ->setSearch(['id','q']) // 设置搜索参数
					->addTimeFilter('replytime') // 添加时间段筛选
		            ->addFilter('qa_class_id', $select_qa_class)  // 添加字段筛选
		            ->addFilter('is_show', ['隐藏','显示'])  // 添加字段筛选
		            ->addFilter('user_id', $select_users)  // 添加字段筛选
		            ->addFilterMap('qa_class_id,is_show,user_id', ['is_reply'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['user_id', '提问会员','callback','user_nickname'],
		        			['q', '问题'],
							//['a', '答案'],
		        			['addtime', '提问时间','datetime',no_font('未知')],
		        			['qa_class_id', '录入分类','select',$select_qa_class],
		        			['replyer', '回答者','callback','admin_username'],
		        			['replytime', '回答时间','datetime',no_font('未知')],
		        			['is_show', '显示','switch'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
		        	->addRightButton('edit') 
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	        case '1':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('ask')->where('is_reply','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('会员提问列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('ask') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','q']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
					->addFilter('user_id', $select_users)  // 添加字段筛选
					->addFilterMap('user_id', ['is_reply'=>0])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['user_id', '提问会员','callback','user_nickname'],
		        			['q', '问题'],
		        			//['remark', '问题描述'],
		        			['addtime', '提问时间','datetime',no_font('未知')],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'回答','href'=>url('reply',['id'=>'__ID__']),'icon'=>'si si-note']) 
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'1']),'icon'=>'fa fa-fw fa-circle-o-notch'])
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
	        	break;
	    }
	}
	public function look($id=''){
		$ask=Db::name("ask")->where('id',$id)->find();
		if(!$ask){
			return $this->error('请求错误');
		}
		$qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
		$select_qa_class=[];
		foreach ($qa_class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_qa_class[$v['id']]=$title;
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看会员提问详情') // 设置页面标题
			//->setPageTips('以下是会员提问的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('id', 'ID','',$ask['id'])
			->addStatic('user_id', '提问会员','',staticText($ask['user_id'],'user_nickname'))
			->addStatic('q', '问题','',$ask['q'])
			//->addStatic('remark', '问题描述','',$ask['remark'])
			->addStatic('addtime', '提问时间','',staticText($interact['addtime'],'time'))
			->addStatic('qa_class_id', '录入分类','',issetArrOffset($select_qa_class[$ask['qa_class_id']]))
			->addStatic('is_reply', '回答状态','',$ask['is_reply']>0?'未回答':'已回答')
			->addStatic('replyer', '回答者','',staticText($ask['replyer'],'admin_username'))
			->addStatic('replytime', '回答时间','',staticText($ask['replytime'],'time'))
			->addStatic('is_show', '显影状态','',$ask['is_show']>0?'显示':'隐藏')
			->addStatic('a', '回答','',staticText($ask['a']))
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function reply($id=''){
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			$validate = new Validate([
				'id|ID'  => 'require',
			    'qa_class_id|录入分类'  => 'require',
			    'a|答案'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['qa_class_id']=$data['qa_class_id'];
			$update['a']=$data['a'];
			$update['is_show']=$data['is_show'];
			$update['is_reply']=1;
			$update['replyer']=UID;
			$update['replytime']=time();
			//数据更新
			$rt=Db::name("ask")->update($update);
			//跳转
			if($rt!==false){
				return $this->success('回答成功',url('index',['status'=>(int)session('jump_status')]),'',1);
	        } else {
	            return $this->error('回答失败');
	        }
		}
		// 接收id
		if ($id>0) {
			// 查处数据
			$ask=Db::name("ask")->where('id',$id)->find();
			if(!$ask){
				return $this->error('请求错误');
			}
		    $qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
			$select_qa_class=[];
			foreach ($qa_class as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_qa_class[$v['id']]=$title;
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('回答会员提问') // 设置页面标题
				->setPageTips('请认真回答用户提问') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addStatic('user_id', '提问会员','',staticText($ask['user_id'],'user_nickname'))
				->addStatic('q', '问题','',$ask['q'])
				//->addStatic('remark', '问题描述','',$ask['remark'])
				->addSelect('qa_class_id', '录入分类','',$select_qa_class)
				->addUeditor('a', '回答','')
				->addRadio('is_show', '显影状态','',['隐藏','显示'],'1')
				->addHidden('id',$ask['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
	public function edit($id=''){
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			$validate = new Validate([
				'id|ID'  => 'require',
			    'qa_class_id|录入分类'  => 'require',
			    'a|答案'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['qa_class_id']=$data['qa_class_id'];
			$update['a']=$data['a'];
			$update['is_show']=$data['is_show'];
			//数据更新
			$rt=Db::name("ask")->update($update);
			//跳转
			if($rt!==false){
				return $this->success('修改成功',url('index',['status'=>(int)session('jump_status')]),'',1);
	        } else {
	            return $this->error('修改失败');
	        }
		}
		// 接收id
		if ($id>0) {
			// 查处数据
			$ask=Db::name("ask")->where('id',$id)->find();
			if(!$ask){
				return $this->error('请求错误');
			}
		    $qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
			$select_qa_class=[];
			foreach ($qa_class as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_qa_class[$v['id']]=$title;
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改会员提问') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addStatic('user_id', '提问会员','',staticText($ask['user_id'],'user_nickname'))
				->addStatic('q', '问题','',$ask['q'])
				->addStatic('remark', '问题描述','',$ask['remark'])
				->addSelect('qa_class_id', '录入分类','',$select_qa_class,$ask['qa_class_id'])
				->addUeditor('a', '回答','',$ask['a'])
				->addRadio('is_show', '显影状态','',['隐藏','显示'],$ask['is_show'])
				->addHidden('id',$ask['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}