<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Interact extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('interact')->where($map)->where('audit',1)->value('count(id)');
		$count_2=Db::name('interact')->where($map)->where('audit',0)->value('count(id)'); 
		$list_tab = [
	        '0' => ['title' => '已审核('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	        '1' => ['title' => '未审核('.$count_2.')', 'url' => url('index', ['status' => '1'])],
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
		            $order='pubtime desc,addtime desc,id desc';
		        }
				$data_list = Db::name('interact')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('会员提问列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('interact') // 指定数据表名
		        	->addOrder('id,pubtime,addtime') // 添加排序
		            ->setSearch(['id','content']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('is_show', ['隐藏','显示'])  // 添加字段筛选
		            ->addFilter('is_public', ['私信','公开'])  // 添加字段筛选
		            ->addFilter('relate', ['policy'=>'政策','qa'=>'问答'])  // 添加字段筛选
		            ->addFilter('user_id', $select_users)  // 添加字段筛选
		            ->addFilterMap('is_show,is_public,relate,user_id', ['audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['relate', '板块','callback','array_v', ['policy'=>'政策','qa'=>'问答']],
		        			['relate_id', '关联','callback',function($relate_id,$data){
		        				if($data['relate']=='政策'){$da='title';$table='policy';}
		        				if($data['relate']=='问答'){$da='q';$table='qa';}
		        				return "<a style='display:block;width:150px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;' href='".url('xiaozheng/'.$table.'/look',['id'=>$relate_id])."' target='_bank' title='查看'>".Db::name($table)->where('id',$relate_id)->value($da)."</a>";
		        			},'__data__'],
		        			['pid','上级','callback',function($pid){
		        				if($pid>0){
		        					$parent=Db::name('interact')->where('id',$pid)->find();
		        					if($parent){
		        						return "<a href='".url('xiaozheng/interact/look',['id'=>$parent['id']])."' target='_bank' title='查看详情'>{$pid}</a>";
		        					}else{
		        						return no_font('上级已删除');
		        					}
		        				}else{
		        					return '顶级评论';
		        				}
		        			}],
		        			['user_id', '会员','callback','user_nickname'],
		        			['content', '内容','callback',function($content){
		        				return "<div style='width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;'>{$content}</div>";
		        			}],
		        			['is_public', '公开状态','status','', ['私信','公开']],
		        			['addtime', '互动时间','datetime',no_font('未知')],
		        			['puber', '审核者','callback','admin_username'],
		        			['pubtime', '审核时间','datetime',no_font('未知')],
		        			['is_show', '显示','switch'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->setExtraJs("<script>var ul = document.getElementsByTagName('body')[0];twemoji.parse(ul, {'size':72});</script>")
		        	->setExtraCss("<style>img.emoji { cursor: pointer; height: 1em; width: 1em; margin: 0 .05em 0 .1em; vertical-align: -0.1em; } </style>")
		        	->fetch();
		        break;
	        case '1':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('interact')->where('audit','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('会员提问列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('interact') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','content']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('is_public', ['私信','公开'])  // 添加字段筛选
		            ->addFilter('relate', ['policy'=>'政策','qa'=>'问答'])  // 添加字段筛选
		            ->addFilter('user_id', $select_users)  // 添加字段筛选
		            ->addFilterMap('is_show,is_public,relate,user_id', ['audit'=>0])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['relate', '板块','callback','array_v', ['policy'=>'政策','qa'=>'问答']],
		        			['relate_id', '关联','callback',function($relate_id,$data){
		        				if($data['relate']=='政策'){$da='title';$table='policy';}
		        				if($data['relate']=='问答'){$da='q';$table='qa';}
		        				return "<a style='display:block;width:150px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;' href='".url('xiaozheng/'.$table.'/look',['id'=>$relate_id])."' target='_bank' title='查看'>".Db::name($table)->where('id',$relate_id)->value($da)."</a>";
		        			},'__data__'],
		        			['pid','上级','callback',function($pid){
		        				if($pid>0){
		        					$parent=Db::name('interact')->where('id',$pid)->find();
		        					if($parent){
		        						return "<a href='".url('xiaozheng/interact/look',['id'=>$parent['id']])."' target='_bank' title='查看详情'>{$pid}</a>";
		        					}else{
		        						return no_font('上级已删除');
		        					}
		        				}else{
		        					return '顶级评论';
		        				}
		        			}],
		        			['user_id', '会员','callback','user_nickname'],
		        			['content', '内容','callback',function($content){
		        				return "<div style='width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;'>{$content}</div>";
		        			}],
		        			['is_public', '公开状态','status','', ['私信','公开']],
		        			['addtime', '互动时间','datetime',no_font('未知')],
		        			['is_show', '显示','switch'],
		        			['audit_status', '审核', 'callback','audit_run','__data__','1','342',true],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
		        	->addRightButton('delete')
		        	->addTopButton('custom',['title'=>'审核通过','href'=>url('audit',['audit'=>'1','status'=>'1']),'icon'=>'fa fa-fw fa-calendar-check-o','class'=>'btn btn-primary ajax-post'])
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'1']),'icon'=>'fa fa-fw fa-circle-o-notch'])
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->setExtraJs("<script>var ul = document.getElementsByTagName('body')[0];twemoji.parse(ul, {'size':72});</script>")
		        	->setExtraCss("<style>img.emoji { cursor: pointer; height: 1em; width: 1em; margin: 0 .05em 0 .1em; vertical-align: -0.1em; } </style>")
		        	->fetch();
	        	break;
	    }
	}
	public function audit(){
		$audit=input('audit','0');
		$status=input('status','0');
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('interact')->where('id','in',$ids)->update(['audit'=>$audit]);
		if($rt!==false){
			if($audit=='1'){
				Db::name('interact')->where('id','in',$ids)->update(['pubtime'=>time(),'puber'=>UID]);
			}
			return $this->success('审核成功',false,'',1);
        } else {
            return $this->error('审核失败');
        }
	}
	public function look($id=''){
		$interact=Db::name("interact")->where('id',$id)->find();
		if(!$interact){
			return $this->error('请求错误');
		}
		if($interact['relate']=='policy') $da='title';
		if($interact['relate']=='qa') $da='q';
		$parent=Db::name('interact')->where('id',$interact['pid'])->find();
		$li=Db::name($interact['relate'])->where('id',$interact['relate_id'])->field($da.',is_del')->find();
		if($li){
			if($li['is_del']){
				$del='(已移除)';
			}else{
				$del='';
			}
			$li="<a href='".url('xiaozheng/'.$interact['relate'].'/look',['id'=>$interact['relate_id']])."' target='_bank' title='查看详情'>".$li[$da].$del."</a>";
		}else{
			$li=no_font('已删除');
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看互动详情') // 设置页面标题
			//->setPageTips('以下是会员提问的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('id', 'ID','',$interact['id'])
			->addStatic('relate', '板块','',array_v($interact['relate'],['policy'=>'政策','qa'=>'问答']))
			->addStatic('relate_id', '关联','',$li)
			->addStatic('pid', '上级','',$interact['pid']>0?($parent?"<a href='".url('xiaozheng/interact/look',['id'=>$parent['id']])."' target='_bank' title='查看详情'>{$parent['content']}</a>":no_font('上级已删除')):'顶级评论')
			->addStatic('user_id', '会员','',staticText($interact['user_id'],'user_nickname'))
			->addStatic('content', '内容','',$interact['content'])
			->addStatic('addtime', '互动时间','',staticText($interact['addtime'],'time'))
			->addStatic('is_public', '公开状态','',$interact['is_public']>0?'公开':'私信')
			->addStatic('audit', '审核状态','',$interact['audit']>0?'已审核':'未审核')
			->addStatic('puber', '审核者','',staticText($interact['puber'],'admin_username'))
			->addStatic('pubtime', '审核时间','',staticText($interact['pubtime'],'time'))
			->addStatic('is_show', '显影状态','',$interact['is_show']>0?'显示':'隐藏')
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
}