<?php
namespace app\alluse\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Carousel extends Admin
{
	public function index($is_del='0'){     
		$list_tab = [
	        '0' => ['title' => '正常', 'url' => url('index', ['is_del' => '0'])],
	        '1' => ['title' => '回收站', 'url' => url('index', ['is_del' => '1'])],
	    ];
	    switch ($is_del) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='sort asc,addtime desc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('carousel')->where('is_del','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('轮播图列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('carousel') // 指定数据表名
		        	->addOrder('id,sort,addtime') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('type',['0'=>'动态','1'=>'政策']) // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '标题'],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['type','关联模块','callback','array_v',['0'=>'动态','1'=>'政策']],
		        			['relate_id','关联内容','callback',function($relate_id,$data){
		        				if($data['type']=='政策'){
		        					$policy=Db::name('policy')->order('addtime desc,id desc')->select();
									$select_policy=[];
									foreach ($policy as $k => $v) {
										$title=$v['title'];
										if($v['is_del']) $title.='(已移除)';
										$select_policy[$v['id']]=$title;
									}
									return "<a href='".url('xiaozheng/policy/look',['id'=>$relate_id])."' target='_bank'>".issetArrOffset($select_policy[$relate_id])."</a>";
		        				}else{
		        					$news=Db::name('news')->order('addtime desc,id desc')->select();
									$select_news=[];
									foreach ($news as $k => $v) {
										$title=$v['title'];
										if($v['is_del']) $title.='(已移除)';
										$select_news[$v['id']]=$title;
									}
									return "<a href='".url('alluse/news/look',['id'=>$relate_id])."' target='_bank'>".issetArrOffset($select_news[$relate_id])."</a>";
		        				}
		        			},'__data__'],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
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
		            $order='sort asc,addtime desc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('carousel')->where('is_del','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('轮播图列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('carousel') // 指定数据表名
		        	->addOrder('id,sort,addtime') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		        	->addFilter('type',['0'=>'动态','1'=>'政策']) // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '标题'],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['type','关联模块','callback','array_v',['0'=>'动态','1'=>'政策']],
		        			['relate_id','关联内容','callback',function($relate_id,$data){
		        				if($data['type']=='政策'){
		        					$policy=Db::name('policy')->order('addtime desc,id desc')->select();
									$select_policy=[];
									foreach ($policy as $k => $v) {
										$title=$v['title'];
										if($v['is_del']) $title.='(已移除)';
										$select_policy[$v['id']]=$title;
									}
									return "<a href='".url('xiaozheng/policy/look',['id'=>$relate_id])."' target='_bank'>".issetArrOffset($select_policy[$relate_id])."</a>";
		        				}else{
		        					$news=Db::name('news')->order('addtime desc,id desc')->select();
									$select_news=[];
									foreach ($news as $k => $v) {
										$title=$v['title'];
										if($v['is_del']) $title.='(已移除)';
										$select_news[$v['id']]=$title;
									}
									return "<a href='".url('alluse/news/look',['id'=>$relate_id])."' target='_bank'>".issetArrOffset($select_news[$relate_id])."</a>";
		        				}
		        			},'__data__'],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
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
				Db::name('carousel')->update(['id'=>$v,'sort'=>$k+1]);
			}
			//跳转
			return $this->success('调整排序成功','index','',1);
		}
		$carousels=Db::name('carousel')->order('sort asc,id desc')->select();
		$list=[];
		foreach ($carousels as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$list[$v['id']]=$title;
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('轮播图排序') // 设置页面标题
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
		$rt=Db::name('carousel')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('carousel')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'pic_id|图片'  => 'require',
			    'type|关联模块'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			if($data['type']=='1'){
				if(!$data['policy_id']){
					return $this->error('请选择关联的政策');
				}
			}else{
				if(!$data['news_id']){
					return $this->error('请选择关联的动态');
				}
			}
			$data['sort']=(int)$data['sort'];
			//数据处理
			$insert=array();
			$insert['title']=$data['title'];
			$insert['pic_id']=$data['pic_id'];
			$insert['type']=$data['type'];
			$insert['relate_id']=$data['type']=='1'?$data['policy_id']:$data['news_id'];
			$insert['sort']=$data['sort'];
			$insert['addtime']=time();
			$insert['adder']=UID;
			//数据入库
			$carousel_id=Db::name("carousel")->insertGetId($insert);
			//跳转
			if($carousel_id>0){
				return $this->success('添加成功','index','',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
		$news=Db::name('news')->order('addtime desc,id desc')->select();
		$select_news=[];
		foreach ($news as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_news[$v['id']]=$title;
		}
		$policy=Db::name('policy')->order('addtime desc,id desc')->select();
		$select_policy=[];
		foreach ($policy as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_policy[$v['id']]=$title;
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加轮播图') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('title', '标题','','')
			->addImage('pic_id', '图片','','')
			->addSelect('type', '关联模块','',['0'=>'动态','1'=>'政策'])
			->addSelect('news_id', '关联的动态','',$select_news)
			->addSelect('policy_id', '关联的政策','',$select_policy)
			->addText('sort', '排序','此项须为整数','100')
			->setTrigger('type', '0', 'news_id')
    		->setTrigger('type', '1', 'policy_id')
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
			    'title|标题'  => 'require',
			    'pic_id|图片'  => 'require',
			    'type|关联模块'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			if($data['type']=='1'){
				if(!$data['policy_id']){
					return $this->error('请选择关联的政策');
				}
			}else{
				if(!$data['news_id']){
					return $this->error('请选择关联的动态');
				}
			}
			$data['sort']=(int)$data['sort'];
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['title']=$data['title'];
			$update['pic_id']=$data['pic_id'];
			$update['type']=$data['type'];
			$update['relate_id']=$data['type']=='1'?$data['policy_id']:$data['news_id'];
			$update['sort']=$data['sort'];
			//数据更新
			$rt=Db::name("carousel")->update($update);
			//跳转
			if($rt!==false){
				return $this->success('修改成功','index','',1);
	        } else {
	            return $this->error('修改失败');
	        }
		}
		// 接收id
		if ($id>0) {
			$news=Db::name('news')->order('addtime desc,id desc')->select();
			$select_news=[];
			foreach ($news as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_news[$v['id']]=$title;
			}
			$policy=Db::name('policy')->order('addtime desc,id desc')->select();
			$select_policy=[];
			foreach ($policy as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_policy[$v['id']]=$title;
			}
			// 查处数据
			$carousel=Db::name("carousel")->where('id',$id)->find();
			if(!$carousel){
				return $this->error('请求错误');
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改轮播图') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addText('title', '标题','',$carousel['title'])
				->addImage('pic_id', '图片','',$carousel['pic_id'])
				->addSelect('type', '关联模块','',['0'=>'动态','1'=>'政策'],$carousel['type'])
				->addSelect('news_id', '关联的动态','',$select_news,$carousel['relate_id'])
				->addSelect('policy_id', '关联的政策','',$select_policy,$carousel['relate_id'])
				->addText('sort', '排序','此项须为整数',$carousel['sort'])
				->addHidden('id',$carousel['id'])
				->setTrigger('type', '0', 'news_id')
	    		->setTrigger('type', '1', 'policy_id')
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}