<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Handle extends Admin
{
	public function index($is_del='0'){    
		session('jump_is_del',$is_del);  
		$list_tab = [
	        '0' => ['title' => '正常', 'url' => url('index', ['is_del' => '0'])],
	        '1' => ['title' => '回收站', 'url' => url('index', ['is_del' => '1'])],
	    ];
	    switch ($is_del) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='sort asc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('handle')->where('is_del','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('办理指南字段列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('handle') // 指定数据表名
		        	->addOrder('id,sort') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
		            ->addFilter('type', ['text'=>'单行文本','textarea'=>'多行文本','view'=>'富文本','select'=>'下拉选择'])  // 添加字段筛选
		            ->addFilterMap('type', ['is_del'=>0])//筛选条件
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '名称'],
		        			['pic_id', '图标','picture','暂无图标'],
		        			['type', '字段类型','callback','array_v',['text'=>'单行文本','textarea'=>'多行文本','view'=>'富文本','select'=>'下拉选择']],
		        			['options','下拉类型选项','callback',function($options,$data){
		        				if($data['type']=='下拉选择') return str_replace(['<br>',PHP_EOL], [' ','<br>'], $options);
		        				else return '';
		        			},'__data__'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButtons(['edit']) 
		        	->addRightButton('custom',['title'=>'移入回收站','href'=>url('del',['ids'=>'__ID__']),'icon'=>'fa fa-fw fa-trash-o','class'=>'btn btn-xs btn-default ajax-get']) 
		    		->addTopButtons(['add']) 
		    		->addTopButton('custom',['title'=>'移入回收站','href'=>url('del'),'icon'=>'fa fa-fw fa-trash-o','class'=>'btn btn-primary ajax-post']) 
		    		->addTopButton('custom',['title'=>'前端排序','href'=>url('sort'),'icon'=>'glyphicon glyphicon-sort']) 
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['is_del'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	        case '1':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order='sort asc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('handle')->where('is_del','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('办理指南字段列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('handle') // 指定数据表名
		        	->addOrder('id,sort') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
		            ->addFilter('type', ['text'=>'单行文本','textarea'=>'多行文本','view'=>'富文本','select'=>'下拉选择'])  // 添加字段筛选
		            ->addFilterMap('type', ['is_del'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '名称'],
		        			['pic_id', '图标','picture','暂无图标'],
		        			['type', '字段类型','callback','array_v',['text'=>'单行文本','textarea'=>'多行文本','view'=>'富文本','select'=>'下拉选择']],
		        			['options','下拉类型选项','callback',function($options,$data){
		        				if($data['type']=='下拉选择') return str_replace(['<br>',PHP_EOL], [' ','<br>'], $options);
		        				else return '';
		        			},'__data__'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('edit') 
		        	->addRightButton('custom',['title'=>'还原','href'=>url('restore',['ids'=>'__ID__']),'icon'=>'fa fa-fw fa-retweet','class'=>'btn btn-xs btn-default ajax-get']) 
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'还原','href'=>url('restore'),'icon'=>'fa fa-fw fa-retweet','class'=>'btn btn-primary ajax-post']) 
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['is_del'=>'1']),'icon'=>'fa fa-fw fa-circle-o-notch'])
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
	        	break;
	    }  
	}
	public function sort(){
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			$validate = new Validate([
			    'sorts'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			$data['sorts']=explode(',', $data['sorts']);
			foreach ($data['sorts'] as $k => $v) {
				Db::name('handle')->update(['id'=>$v,'sort'=>$k+1]);
			}
			//跳转
			return $this->success('调整排序成功','index','',1);
		}
		$handles=Db::name('handle')->where('is_del','0')->order('sort asc,id desc')->select();
		$list=[];
		foreach ($handles as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$list[$v['id']]=$title;
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('办理指南字段排序') // 设置页面标题
			->setPageTips('拖拽表头进行排序') // 设置页面提示信息
			//->setUrl('sort') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addSort('sorts', '调整顺序', '', $list)
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function del(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('handle')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('handle')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'title|名称'  => 'require',
			    'type|字段类型'  => 'require',
			    'pic_id|图标'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			$insert=array();
			if($data['type']=='select'){
				if($data['options']==='') return $this->error('下拉类型选项不能为空');
				$insert['options']=$data['options'];
			}
			$data['sort']=(int)$data['sort'];
			//数据处理
			$insert['title']=$data['title'];
			$insert['sort']=$data['sort'];
			$insert['pic_id']=$data['pic_id'];
			$insert['type']=$data['type'];
			//数据入库
			$handle_id=Db::name("handle")->insertGetId($insert);
			//跳转
			if($handle_id>0){
				return $this->success('添加成功',url('index',['is_del'=>(int)session('jump_is_del')]),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加办理指南字段') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('title', '名称','','')
			->addImage('pic_id', '图标','','')
			->addSelect('type', '字段类型','',['text'=>'单行文本','textarea'=>'多行文本','select'=>'下拉选择'],'text')
			->addTextarea('options', '下拉类型选项','一行为一个选项','')
			->addText('sort', '排序','此项须为整数','100')
			->setTrigger('type', 'select', 'options')
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
			    'title|名称'  => 'require',
			    'type|字段类型'  => 'require',
			    'pic_id|图标'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			$update=array();
			if($data['type']=='select'){
				if($data['options']==='') return $this->error('下拉类型选项不能为空');
				$update['options']=$data['options'];
			}
			$data['sort']=(int)$data['sort'];
			//数据处理
			$update['id']=$data['id'];
			$update['title']=$data['title'];
			$update['pic_id']=$data['pic_id'];
			$update['sort']=$data['sort'];
			$update['type']=$data['type'];
			//数据更新
			$rt=Db::name("handle")->update($update);
			//跳转
			if($rt!==false){
				return $this->success('修改成功',url('index',['is_del'=>(int)session('jump_is_del')]),'',1);
	        } else {
	            return $this->error('修改失败');
	        }
		}
		// 接收id
		if ($id>0) {
			// 查处数据
			$handle=Db::name("handle")->where('id',$id)->find();
			if(!$handle){
				return $this->error('请求错误');
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改办理指南字段') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addText('title', '名称','',$handle['title'])
				->addImage('pic_id', '图标','',$handle['pic_id'])
				->addSelect('type', '字段类型','',['text'=>'单行文本','textarea'=>'多行文本','select'=>'下拉选择'],$handle['type'])
				->addTextarea('options', '下拉类型选项','一行为一个选项',$handle['options'])
				->addText('sort', '排序','此项须为整数',$handle['sort'])
				->addHidden('id',$handle['id'])
				->setTrigger('type', 'select', 'options')
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}