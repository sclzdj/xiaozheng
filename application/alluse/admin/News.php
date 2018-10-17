<?php
namespace app\alluse\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class News extends Admin
{
	public function index($status='0'){ 
		$count_1=Db::name('news')->where('is_del','0')->where('audit','1')->value('count(id)');
		$count_2=Db::name('news')->where('is_del','1')->where('audit','1')->value('count(id)'); 
		$count_3=Db::name('news')->where('is_del','0')->where('audit','0')->value('count(id)');
		$count_4=Db::name('news')->where('is_del','0')->where('audit','2')->value('count(id)');   
		$list_tab = [
	        '0' => ['title' => '已发布('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	        '1' => ['title' => '回收站('.$count_2.')', 'url' => url('index', ['status' => '1'])],
	        '2' => ['title' => '未审核('.$count_3.')', 'url' => url('index', ['status' => '2'])],
	        '3' => ['title' => '审核不通过('.$count_4.')', 'url' => url('index', ['status' => '3'])],
	    ];
	    $class=Db::name('class')->order('sort asc,id desc')->select();
		$select_class=[];
		foreach ($class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_class[$v['id']]=$title;
		}
		$sheng=Db::name('city')->where('id','in',Db::name('news')->value('GROUP_CONCAT(sheng)'))->order('sort asc,id desc')->select();
		$select_sheng=['0'=>no_font('无')];
		foreach ($sheng as $k => $v) {
			$select_sheng[$v['id']]=$v['title'];
		}
		$shi=Db::name('city')->where('id','in',Db::name('news')->value('GROUP_CONCAT(shi)'))->order('sort asc,id desc')->select();
		$select_shi=['0'=>no_font('无')];
		foreach ($shi as $k => $v) {
			$select_shi[$v['id']]=$v['title'];
		}
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='pubtime desc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('news')->where('is_del','0')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('动态列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('news') // 指定数据表名
		        	->addOrder('id,pubtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
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
		        $map = $this->getMap();
				$data_list = Db::name('news')->where('is_del','1')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('动态列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('news') // 指定数据表名
		        	->addOrder('id,pubtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
		        			['puber', '发布者','callback','admin_username'],
		        			['pubtime', '发布时间','datetime',no_font('未知')],
		        			['click', '阅读量'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
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
		        $map = $this->getMap();
				$data_list = Db::name('news')->where('is_del','0')->where('audit','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('动态列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('news') // 指定数据表名
		        	->addOrder('id,addtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '审核', 'callback','audit_run','__data__','2','295'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true)  
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
		        $map = $this->getMap();
				$data_list = Db::name('news')->where('is_del','0')->where('audit','2')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('动态列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('news') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '重审', 'callback','audit_run','__data__','3','295'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
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
		$news=Db::name("news")->where('id',$id)->find();
		if(!$news){
			return $this->error('请求错误');
		}
		$city=Db::name('city')->select();
		$select_city=[0=>''];
		foreach ($city as $k => $v) {
			$select_city[$v['id']]=$v['title'];
		}
		$class=Db::name('class')->order('sort asc,id desc')->select();
		$select_class=[];
		foreach ($class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_class[$v['id']]=$title;
		}
		if($news['is_del']==0 && $news['audit']==1)	$status='已发布';
		elseif($news['is_del']==1 && $news['audit']==1)	$status='已移除至回收站';
		elseif($news['is_del']==0 && $news['audit']==0)	$status='未审核';
		elseif($news['is_del']==0 && $news['audit']==2)	$status='审核不通过';
		else $status=no_font('未知');
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看动态详情') // 设置页面标题
			//->setPageTips('以下是动态的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('id', 'ID','',$news['id'])
			->addStatic('title', '标题','',$news['title'])
			->addStatic('class_id', '政策类型','',issetArrOffset($select_class[$news['class_id']]))
			->addStatic('city', '城市','',issetArrOffset($select_city['1']).'&nbsp;&nbsp;'.issetArrOffset($select_city[$news['sheng']]).'&nbsp;&nbsp;'.issetArrOffset($select_city[$news['shi']]))
			->addStatic('adder', '创建者','',staticText($news['adder'],'admin_username'))
			->addStatic('addtime', '创建时间','',staticText($news['addtime'],'time'))
			->addStatic('puber', '发布者','',staticText($news['puber'],'admin_username'))
			->addStatic('pubtime', '发布时间','',staticText($news['pubtime'],'time'))
			->addStatic('click', '阅读量','',$news['click'])
			->addStatic('status', '状态','',$status)
			->addStatic('content', '内容','',staticText($news['content']))
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function audit(){
		$audit=input('audit','0');
		$status=input('status','0');
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('news')->where('id','in',$ids)->update(['audit'=>$audit]);
		if($rt!==false){
			if($audit=='1'){
				Db::name('news')->where('id','in',$ids)->update(['pubtime'=>time(),'puber'=>UID]);
			}
			return $this->success('审核成功',false,'',1);
        } else {
            return $this->error('审核失败');
        }
	}
	public function del(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('news')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('news')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'title|标题'  => 'require',
			    'class_id|政策类型'  => 'require',
			    'content|内容'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$insert=array();
			$insert['title']=$data['title'];
			$insert['class_id']=$data['class_id'];
			$insert['sheng']=$data['sheng'];
			$insert['shi']=$data['shi'];
			$insert['content']=$data['content'];
			$insert['file_ids']=$data['file_ids'];
			$insert['addtime']=time();
			$insert['adder']=UID;
			if($data['sheng']==0 && $data['shi']==0){
				$insert['city_id']=1;
			}elseif ($data['sheng']>0 && $data['shi']==0) {
				$insert['city_id']=$data['sheng'];
			}elseif ($data['sheng']>0 && $data['shi']>0) {
				$insert['city_id']=$data['shi'];
			}else{
				$insert['city_id']=0;
			}
			//数据入库
			$news_id=Db::name("news")->insertGetId($insert);
			//跳转
			if($news_id>0){
				return $this->success('添加成功',url('index',['status'=>'2']),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
	    $class=Db::name('class')->order('sort asc,id desc')->select();
		$select_class=[];
		foreach ($class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_class[$v['id']]=$title;
		}
		$list_sheng=Db::name('city')->order('sort asc,id desc')->where('pid',1)->select();
        $select_sheng=[];
        foreach ($list_sheng as $k => $v) {
            $select_sheng[$v['id']]=$v['title'];
        }
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加动态') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('title', '标题','','')
			->addSelect('class_id', '政策类型','不选自动归为其它',$select_class)
			->addLinkage('sheng', '所属省', '', $select_sheng, '', url('get_shi'), 'shi')
    		->addSelect('shi', '所属市')
    		->addFiles('file_ids', '附件','','')
			->addCkeditor('content', '内容','','')
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	// 根据省份获取市区
    public function get_shi($sheng = '')
    {
        $citys=Db::name('city')->where('pid',$sheng)->order('sort asc,id desc')->select();
        $arr['code'] = '1'; //判断状态
        $arr['msg'] = '请求成功'; //回传信息
        $arr['list'] = []; 
        foreach ($citys as $k => $v) {
            $pix=[];
            $pix['key']=$v['id'];
            $pix['value']=$v['title'];
            $arr['list'][]=$pix;
        }
        return json($arr);
    }
	public function edit($id=''){
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			$validate = new Validate([
			    'title|标题'  => 'require',
			    'class_id|政策类型'  => 'require',
			    'content|内容'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['class_id']=$data['class_id'];
			$update['title']=$data['title'];
			$update['sheng']=$data['sheng'];
			$update['shi']=$data['shi'];
			$update['content']=$data['content'];
			$update['file_ids']=$data['file_ids'];
			if($data['sheng']==0 && $data['shi']==0){
				$update['city_id']=1;
			}elseif ($data['sheng']>0 && $data['shi']==0) {
				$update['city_id']=$data['sheng'];
			}elseif ($data['sheng']>0 && $data['shi']>0) {
				$update['city_id']=$data['shi'];
			}else{
				$update['city_id']=0;
			}
			//数据更新
			$rt=Db::name("news")->update($update);
			//跳转
			if($rt!==false){
				return $this->success('修改成功','index','',1);
	        } else {
	            return $this->error('修改失败');
	        }
		}
		// 接收id
		if ($id>0) {
			// 查处数据
			$news=Db::name("news")->where('id',$id)->find();
			if(!$news){
				return $this->error('请求错误');
			}
		    $class=Db::name('class')->order('sort asc,id desc')->select();
			$select_class=[];
			foreach ($class as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_class[$v['id']]=$title;
			}
			$list_sheng=Db::name('city')->order('sort asc,id desc')->where('pid',1)->select();
	        $select_sheng=[];
	        foreach ($list_sheng as $k => $v) {
	            $select_sheng[$v['id']]=$v['title'];
	        }
	        $list_shi=Db::name('city')->order('sort asc,id desc')->where('pid',$news['sheng'])->select();
	        $select_shi=[];
	        foreach ($list_shi as $k => $v) {
	            $select_shi[$v['id']]=$v['title'];
	        }
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改动态') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addText('title', '标题','',$news['title'])
				->addSelect('class_id', '政策类型','不选自动归为其它',$select_class,$news['class_id'])
				->addLinkage('sheng', '所属省', '', $select_sheng,$news['sheng'], url('get_shi'), 'shi')
    			->addSelect('shi', '所属市','',$select_shi,$news['shi'])
    			->addFiles('file_ids', '附件','',$news['file_ids'])
				->addCkeditor('content', '内容','',$news['content'])
				->addHidden('id',$news['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}