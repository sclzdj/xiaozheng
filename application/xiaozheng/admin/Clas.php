<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Clas extends Admin
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
				$data_list = Db::name('class')->where('is_del','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('政策库列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('class') // 指定数据表名
		        	->addOrder('id,sort') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '名称'],
		        			['type', '库类型'],
		        			['pic_id', '图标','picture'],
		        			['remark', '标语'],
		        			['prefix', '编码前缀'],
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
				$data_list = Db::name('class')->where('is_del','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('政策库列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('class') // 指定数据表名
		        	->addOrder('id,sort') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '名称'],
		        			['type', '库类型'],
		        			['pic_id', '图标','picture'],
		        			['prefix', '编码前缀'],
		        			['remark', '标语'],
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
				Db::name('class')->update(['id'=>$v,'sort'=>$k+1]);
			}
			//跳转
			return $this->success('调整排序成功','index','',1);
		}
		$classs=Db::name('class')->where('is_del','0')->order('sort asc,id desc')->select();
		$list=[];
		foreach ($classs as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$list[$v['id']]=$title;
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('政策库排序') // 设置页面标题
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
		$rt=Db::name('class')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('class')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'pic_id|图片'  => 'require',
			    'remark|标语'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			$data['sort']=(int)$data['sort'];
			//数据处理
			$insert=array();
			$insert['title']=$data['title'];
			$insert['type']=$data['type'];
			$insert['pic_id']=$data['pic_id'];
			$insert['remark']=$data['remark'];
			$insert['prefix']=$data['prefix'];
			$insert['sort']=$data['sort'];
			//数据入库
			$class_id=Db::name("class")->insertGetId($insert);
			//跳转
			if($class_id>0){
				return $this->success('添加成功',url('index',['is_del'=>(int)session('jump_is_del')]),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加政策库') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('title', '名称','','')
			->addSelect('type', '库类型','',['清单库'=>'清单库','知识库'=>'知识库'],'')
			->addImage('pic_id', '图片','','')
			->addText('remark', '标语','用精炼的一句话对此政策库进行描述','')
			->addText('prefix', '编码前缀','推荐使用名称首字母大写','')
			->addText('sort', '排序','此项须为整数','100')
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
			    'pic_id|图片'  => 'require',
			    'remark|标语'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			$data['sort']=(int)$data['sort'];
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['title']=$data['title'];
			$update['type']=$data['type'];
			$update['pic_id']=$data['pic_id'];
			$update['remark']=$data['remark'];
			$update['prefix']=$data['prefix'];
			$update['sort']=$data['sort'];
			//数据更新
			$rt=Db::name("class")->update($update);
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
			$class=Db::name("class")->where('id',$id)->find();
			if(!$class){
				return $this->error('请求错误');
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改政策库') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addText('title', '名称','',$class['title'])
				->addSelect('type', '库类型','',['清单库'=>'清单库','知识库'=>'知识库'],$class['type'])
				->addImage('pic_id', '图标','',$class['pic_id'])
				->addText('remark', '标语','用精炼的一句话对此政策库进行描述',$class['remark'])
				->addText('prefix', '编码前缀','推荐使用名称首字母大写',$class['prefix'])
				->addText('sort', '排序','此项须为整数',$class['sort'])
				->addHidden('id',$class['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}