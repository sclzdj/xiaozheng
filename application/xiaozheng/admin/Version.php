<?php
namespace app\alluse\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Version extends Admin
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
		            $order='version desc,addtime desc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('version')->where('is_del','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('APP版本列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('version') // 指定数据表名
		        	->addOrder('id,addtime,version,is_forced') // 添加排序
		            ->setSearch(['id']) // 设置搜索参数
		            ->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('os',['0' => '安卓','1'=>'苹果']) // 添加筛选
		            ->addFilter('is_forced',['0' => '不强制更新','1'=>'强制更新']) // 添加筛选
		        	->addColumns([
		        			['id', 'ID'],
		        			['os', '手机系统', 'callback', 'array_v', ['0' => '安卓','1'=>'苹果']],
		        			['version', '版本号','callback', 'str_linked', '.0.0'],
		        			['is_forced', '强制更新', 'switch'],
		        			['addtime', '更新时间', 'datetime'],
		        			['msg', '更新内容'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButtons(['edit']) 
		        	->addRightButton('custom',['title'=>'移入回收站','href'=>url('del',['ids'=>'__ID__']),'icon'=>'fa fa-fw fa-trash-o']) 
		    		->addTopButtons(['add']) 
		    		->addTopButton('custom',['title'=>'移入回收站','href'=>url('del'),'icon'=>'fa fa-fw fa-trash-o','class'=>'btn btn-primary ajax-post']) 
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['is_del'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	        case '1':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order='version desc,addtime desc,id desc';
		        }
		        $map = $this->getMap();
				$data_list = Db::name('version')->where('is_del','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('APP版本列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $is_del)//分组
		        	->setTableName('version') // 指定数据表名
		        	->addOrder('id,addtime,version,is_forced') // 添加排序
		            ->setSearch(['id']) // 设置搜索参数
		            ->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('os',['0' => '安卓','1'=>'苹果']) // 添加筛选
		            ->addFilter('is_forced',['0' => '不强制更新','1'=>'强制更新']) // 添加筛选
		        	->addColumns([
		        			['id', 'ID'],
		        			['os', '手机系统', 'callback', 'array_v', ['0' => '安卓','1'=>'苹果']],
		        			['version', '版本号','callback', 'str_linked', '.0.0'],
		        			['is_forced', '强制更新', 'switch'],
		        			['addtime', '更新时间', 'datetime'],
		        			['msg', '更新内容'],
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
	public function del(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('version')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('version')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'os'  => 'require',
			    'version' => 'require',
			    'file' => 'require',
			    'is_forced' => 'require',
			    'msg' => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			$data['version']=(int)$data['version'];
			$version=Db::name("version")->where(['os'=>$data['os']])->order('version DESC,addtime DESC,id DESC')->find();
			if($version['version']>=$data['version']){
				return $this->error('必须上传版本号高于'.$version['version'].'.0.0');
			}
			//数据处理
			$insert=array();
			$insert['os']=$data['os'];
			$insert['version']=$data['version'];
			$insert['file']=$data['file'];
			$insert['is_forced']=$data['is_forced'];
			$insert['msg']=$data['msg'];
			$insert['addtime']=time();
			//数据入库
			$version_id=Db::name("version")->insertGetId($insert);
			//跳转
			if($version_id>0){
				return $this->success('添加成功',url('index',['is_del'=>(int)session('jump_is_del')]),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加APP版本') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('version', '版本号', '必须为一个整数','',['','.0.0'])
			->addRadio('os', '手机系统', '', ['0' => '安卓','1'=>'苹果'],'0')
			->addFile('file', '安装包文件')
			->addRadio('is_forced', '强制更新', '', ['0' => '否','1'=>'是'],'1')
			->addTextarea('msg', '更新内容','语言尽量精炼，突出重点')
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
			    'os'  => 'require',
			    'file' => 'require',
			    'is_forced' => 'require',
			    'msg' => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['file']=$data['file'];
			$update['is_forced']=$data['is_forced'];
			$update['msg']=$data['msg'];
			//数据更新
			$rt=Db::name("version")->update($update);
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
			$version=Db::name("version")->where('id',$id)->find();
			if(!$version){
				return $this->error('请求错误');
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改APP版本') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addFile('file', '安装包文件','',$version['file'])
				->addRadio('is_forced', '强制更新', '', ['0' => '否','1'=>'是'],$version['is_forced'])
				->addTextarea('msg', '更新内容','语言尽量精炼，突出重点',$version['msg'])
				->addHidden('id',$version['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}