<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Setcomment extends Admin
{
	public function index(){
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			
			//数据处理
			
			//数据写入
			Db::execute("alter table `".config('database.prefix')."city` comment='".$data['city']."'");
			//Db::name('admin_menu')->where('id','223')->update(['title'=>$data['city'].'列表']);
			Db::execute("alter table `".config('database.prefix')."career` comment='".$data['career']."'");
			//Db::name('admin_menu')->where('id','232')->update(['title'=>$data['career'].'列表']);
			Db::execute("alter table `".config('database.prefix')."ident` comment='".$data['ident']."'");
			//Db::name('admin_menu')->where('id','239')->update(['title'=>$data['ident'].'列表']);
			return $this->success('配置成功','index','',1);
	       
		}
		$city=get_comment('city');
		$career=get_comment('career');
		$ident=get_comment('ident');
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('筛选命名配置类型') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('index') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('city', '地区筛选命名','',$city)
			->addText('career','事业类型筛选命名','',$career)
			->addText('ident', '身份类型筛选命名','',$ident)
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
}