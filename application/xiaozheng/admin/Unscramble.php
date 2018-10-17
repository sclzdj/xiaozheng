<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Unscramble extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('unscramble')->where($map)->where('is_del','0')->where('audit','1')->value('count(id)');
		$count_2=Db::name('unscramble')->where($map)->where('is_del','1')->where('audit','1')->value('count(id)'); 
		$count_3=Db::name('unscramble')->where($map)->where('is_del','0')->where('audit','0')->value('count(id)');
		$count_4=Db::name('unscramble')->where($map)->where('is_del','0')->where('audit','2')->value('count(id)');   
		$list_tab = [
	        '0' => ['title' => '已发布('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	        '1' => ['title' => '回收站('.$count_2.')', 'url' => url('index', ['status' => '1'])],
	        '2' => ['title' => '未审核('.$count_3.')', 'url' => url('index', ['status' => '2'])],
	        '3' => ['title' => '审核不通过('.$count_4.')', 'url' => url('index', ['status' => '3'])],
	    ];
	    $policy=Db::name('policy')->order('is_del asc,addtime desc,id desc')->select();
		$select_policy=[];
		foreach ($policy as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_policy[$v['id']]=$title;
		}
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='pubtime desc,id desc';
		        }
				$data_list = Db::name('unscramble')->where('is_del','0')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('解读列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('unscramble') // 指定数据表名
		        	->addOrder('id,pubtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilterMap('policy_id', ['is_del'=>0,'audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['title', '标题'],
		        			['policy_id', '所属政策','select',$select_policy],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['source', '来源'],
		        			['puber', '发布者','callback','admin_username'],
		        			['pubtime', '发布时间','datetime',no_font('未知')],
		        			['click', '阅读量'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
		        	->addRightButtons(['edit']) 
		        	->addRightButton('custom',['title'=>'移入回收站','href'=>url('del',['ids'=>'__ID__']),'icon'=>'fa fa-fw fa-trash-o','class'=>'btn btn-xs btn-default ajax-get']) 
		    		->addTopButtons(['add']) 
		    		->addTopButton('custom',['title'=>'移入回收站','href'=>url('del'),'icon'=>'fa fa-fw fa-trash-o','class'=>'btn btn-primary ajax-post']) 
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	        case '1':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order='pubtime desc,id desc';
		        }
				$data_list = Db::name('unscramble')->where('is_del','1')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('解读列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('unscramble') // 指定数据表名
		        	->addOrder('id,pubtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilterMap('policy_id', ['is_del'=>1,'audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['title', '标题'],
		        			['policy_id', '所属政策','select',$select_policy],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['source', '来源'],
		        			['puber', '发布者','callback','admin_username'],
		        			['pubtime', '发布时间','datetime',no_font('未知')],
		        			['click', '阅读量'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true)
		        	->addRightButton('edit')  
		        	->addRightButton('custom',['title'=>'还原','href'=>url('restore',['ids'=>'__ID__']),'icon'=>'fa fa-fw fa-retweet','class'=>'btn btn-xs btn-default ajax-get']) 
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'还原','href'=>url('restore'),'icon'=>'fa fa-fw fa-retweet','class'=>'btn btn-primary ajax-post']) 
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'1']),'icon'=>'fa fa-fw fa-circle-o-notch'])
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
	        	break;
	        case '2':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('unscramble')->where('is_del','0')->where('audit','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('解读列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('unscramble') // 指定数据表名
		        	->addOrder('id,addtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilterMap('policy_id', ['is_del'=>0,'audit'=>0])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['title', '标题'],
		        			['policy_id', '所属政策','select',$select_policy],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['source', '来源'],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '审核', 'callback','audit_run','__data__','2','287'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true)
		        	->addRightButton('edit')   
		    		->addTopButtons(['add']) 
		        	->addTopButton('custom',['title'=>'审核通过','href'=>url('audit',['audit'=>'1','status'=>'2']),'icon'=>'fa fa-fw fa-calendar-check-o','class'=>'btn btn-primary ajax-post'])
		        	->addTopButton('custom',['title'=>'审核不通过','href'=>url('audit',['audit'=>'2','status'=>'2']),'icon'=>'fa fa-fw fa-calendar-times-o','class'=>'btn btn-primary ajax-post'])
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'2']),'icon'=>'fa fa-fw fa-circle-o-notch'])
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
	        	break;
	    	case '3':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('unscramble')->where('is_del','0')->where('audit','2')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('解读列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('unscramble') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilterMap('policy_id', ['is_del'=>0,'audit'=>2])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['title', '标题'],
		        			['policy_id', '所属政策','select',$select_policy],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['source', '来源'],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '重审', 'callback','audit_run','__data__','3','287'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true)
		        	->addRightButton('edit')  
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		        	->addTopButton('custom',['title'=>'重审通过','href'=>url('audit',['audit'=>'1','status'=>'3']),'icon'=>'fa fa-fw fa-calendar-check-o','class'=>'btn btn-primary ajax-post'])
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'3']),'icon'=>'fa fa-fw fa-circle-o-notch'])
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
	        	break;
	    }
	}
	public function look($id=''){
		$unscramble=Db::name("unscramble")->where('id',$id)->find();
		if(!$unscramble){
			return $this->error('请求错误');
		}
		$policy=Db::name('policy')->order('is_del asc,addtime desc,id desc')->select();
		$select_policy=[];
		foreach ($policy as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_policy[$v['id']]=$title;
		}
		if($unscramble['is_del']==0 && $unscramble['audit']==1)	$status='已发布';
		elseif($unscramble['is_del']==1 && $unscramble['audit']==1)	$status='已移除至回收站';
		elseif($unscramble['is_del']==0 && $unscramble['audit']==0)	$status='未审核';
		elseif($unscramble['is_del']==0 && $unscramble['audit']==2)	$status='审核不通过';
		else $status=no_font('未知');
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看解读详情') // 设置页面标题
			//->setPageTips('以下是解读的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('id', 'ID','',$unscramble['id'])
			->addStatic('title', '标题','',$unscramble['title'])
			->addStatic('policy_id', '所属政策','',"<a href='".url('xiaozheng/policy/look',['id'=>$unscramble['policy_id']])."' target='_bank'>".issetArrOffset($select_policy[$unscramble['policy_id']])."</a>")
			->addStatic('pic_id', '图片','',staticText($unscramble['pic_id'],'pic'))
			->addStatic('source', '来源','',$unscramble['source'])
			->addStatic('adder', '创建者','',staticText($unscramble['adder'],'admin_username'))
			->addStatic('addtime', '创建时间','',staticText($unscramble['addtime'],'time'))
			->addStatic('puber', '发布者','',staticText($unscramble['puber'],'admin_username'))
			->addStatic('pubtime', '发布时间','',staticText($unscramble['pubtime'],'time'))
			->addStatic('click', '阅读量','',$unscramble['click'])
			->addStatic('status', '状态','',$status)
			->addStatic('content', '内容','',staticText($unscramble['content']))
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function audit(){
		$audit=input('audit','0');
		$status=input('status','0');
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('unscramble')->where('id','in',$ids)->update(['audit'=>$audit]);
		if($rt!==false){
			if($audit=='1'){
				Db::name('unscramble')->where('id','in',$ids)->update(['pubtime'=>time(),'puber'=>UID]);
			}
			return $this->success('审核成功',false,'',1);
        } else {
            return $this->error('审核失败');
        }
	}
	public function del(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('unscramble')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('unscramble')->where('id','in',$ids)->update(['is_del'=>'0']);
		if($rt!==false){
			return $this->success('还原成功',false,'',1);
        } else {
            return $this->error('还原失败');
        }
	}
	public function add(){
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			$validate = new Validate([
			    'policy_id|所属政策'  => 'require',
			    'title|标题'  => 'require',
			    'content|内容'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$insert=array();
			$insert['title']=$data['title'];
			$insert['policy_id']=$data['policy_id'];
			$insert['pic_id']=$data['pic_id'];
			$insert['source']=$data['source'];
			$insert['content']=$data['content'];
			$insert['adder']=UID;
			$insert['addtime']=time();
			//数据入库
			$unscramble_id=Db::name("unscramble")->insertGetId($insert);
			//跳转
			if($unscramble_id>0){
				return $this->success('添加成功',url('index',['status'=>(int)session('jump_status')]),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
	    $policy=Db::name('policy')->order('is_del asc,addtime desc,id desc')->select();
		$select_policy=[];
		foreach ($policy as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_policy[$v['id']]=$title;
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加解读') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addSelect('policy_id', '所属政策','',$select_policy,input('policy_id',''))
			->addText('title', '标题','','')
			->addImage('pic_id', '图片','','')
			->addText('source', '来源','','')
			->addUeditor('content', '内容','','')
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function edit($id=''){
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			$validate = new Validate([
			    'policy_id|所属政策'  => 'require',
			    'title|标题'  => 'require',
			    'content|内容'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['policy_id']=$data['policy_id'];
			$update['pic_id']=$data['pic_id'];
			$update['title']=$data['title'];
			$update['source']=$data['source'];
			$update['content']=$data['content'];
			//数据更新
			$rt=Db::name("unscramble")->update($update);
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
			$unscramble=Db::name("unscramble")->where('id',$id)->find();
			if(!$unscramble){
				return $this->error('请求错误');
			}
		    $policy=Db::name('policy')->order('is_del asc,addtime desc,id desc')->select();
			$select_policy=[];
			foreach ($policy as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_policy[$v['id']]=$title;
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改解读') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addSelect('policy_id', '所属政策','',$select_policy,$unscramble['policy_id'])
				->addText('title', '标题','',$unscramble['title'])
				->addImage('pic_id', '图片','',$unscramble['pic_id'])
				->addText('source', '来源','',$unscramble['source'])
				->addUeditor('content', '内容','',$unscramble['content'])
				->addHidden('id',$unscramble['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}