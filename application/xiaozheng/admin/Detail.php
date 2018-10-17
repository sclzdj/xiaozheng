<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;
use app\common\helper\mail;

class Detail extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
	    $table=config('database.prefix').'detail';
		$count_1=Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($map)->where($table.'.is_del','0')->where($table.'.audit','1')->value('count('.$table.'.id)');
		$count_2=Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($map)->where($table.'.is_del','1')->where($table.'.audit','1')->value('count('.$table.'.id)'); 
		$count_3=Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($map)->where($table.'.is_del','0')->where($table.'.audit','0')->value('count('.$table.'.id)');
		$count_4=Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($map)->where($table.'.is_del','0')->where($table.'.audit','2')->value('count('.$table.'.id)');   
		$list_tab = [
	        '0' => ['title' => '已发布('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	        '1' => ['title' => '回收站('.$count_2.')', 'url' => url('index', ['status' => '1'])],
	        '2' => ['title' => '未审核('.$count_3.')', 'url' => url('index', ['status' => '2'])],
	        '3' => ['title' => '审核不通过('.$count_4.')', 'url' => url('index', ['status' => '3'])],
	    ];
	    $policy=Db::name('policy')->where('class_id',1)->order('is_del asc,pubtime desc,addtime desc,id desc')->select();
		$select_policy=[];
		foreach ($policy as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_policy[$v['id']]=$title;
		}
	    $career=Db::name('career')->order('is_del asc,sort asc,id desc')->select();
		$select_career=[];
		foreach ($career as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_career[$v['id']]=$title;
		}
		$ident=Db::name('ident')->order('is_del asc,sort asc,id desc')->select();
		$select_ident=[];
		foreach ($ident as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_ident[$v['id']]=$title;
		}
		$category=Db::name('category')->order('is_del asc,sort asc,id desc')->select();
		$select_category=[];
		foreach ($category as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_category[$v['id']]=$title;
		}
		$sheng=Db::name('city')->where('id','in',Db::name('policy')->value('GROUP_CONCAT(sheng)'))->order('sort asc,id desc')->select();
		$select_sheng=['0'=>no_font('无')];
		foreach ($sheng as $k => $v) {
			$select_sheng[$v['id']]=$v['title'];
		}
		$shi=Db::name('city')->where('id','in',Db::name('policy')->value('GROUP_CONCAT(shi)'))->order('sort asc,id desc')->select();
		$select_shi=['0'=>no_font('无')];
		foreach ($shi as $k => $v) {
			$select_shi[$v['id']]=$v['title'];
		}
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order=$table.'.pubtime desc,'.$table.'.id desc';
		        }
				$data_list = Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($table.'.is_del','0')->where($table.'.audit','1')->where($map)->order($order)->field($table.'.*')->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('政策清单列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('detail') // 指定数据表名
		        	->addOrder($table.'.id,'.$table.'.pubtime,'.$table.'.click,'.$table.'.laud,'.$table.'.tread,'.$table.'.follow') // 添加排序
		            ->setSearch([$table.'.id']) // 设置搜索参数
					->addTimeFilter($table.'.pubtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilter('career_id', $select_career)  // 添加字段筛选
		            ->addFilter('ident_id', $select_ident)  // 添加字段筛选
		            ->addFilter('category_id', $select_category)  // 添加字段筛选
		            ->addFilter('policy.sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('policy.shi', $select_shi)  // 添加字段筛选
		            ->addFilterMap('policy.shi', 'policy.sheng')//省市条件
		            ->addFilterMap('policy_id,career_id,ident_id,category_id,policy.sheng,policy.shi', [$table.'.is_del'=>0,$table.'.audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['policy_id', '清单内容','callback',function($policy_id,$data){
		        				$policy=Db::name('policy')->where('class_id',1)->order('pubtime desc,addtime desc,id desc')->select();
								$select_policy=[];
								foreach ($policy as $k => $v) {
									$title=$v['title'];
									if($v['is_del']) $title.='(已移除)';
									$select_policy[$v['id']]=$title;
								}
		        				return "<div style='width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;'>{$data['remark']}</div><span style='color:#bbb;display:block;float:left;'>关联：</span><a style='display:block;float:left;width:150px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;' href='".url('xiaozheng/policy/look',['id'=>$policy_id])."' target='_bank' title='查看政策'>{$select_policy[$policy_id]}</a>";
		        			},'__data__'],
		        			['career_id', '事业类型','select',$select_career],
		        			['ident_id', '身份类型','select',$select_ident],
		        			['category_id', '政策类型','select',$select_category],
		        			['sheng', '省','callback',function($data){
		        				$sheng=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('sheng');
		        				if($sheng>0){
		        					return (string)Db::name('city')->where('id',$sheng)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['shi', '市','callback',function($data){
		        				$shi=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('shi');
		        				if($shi>0){
		        					return (string)Db::name('city')->where('id',$shi)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['basis', '基础信息','callback',function($data){
		        				$basis_data=Db::name('basis_data')->where('detail_id',$data['id'])->select();
		        				if($basis_data){
		        					return "<a href='".url('basisdata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['handle', '办理指南','callback',function($data){
		        				$handle_data=Db::name('handle_data')->where('detail_id',$data['id'])->select();
		        				if($handle_data){
		        					return "<a href='".url('handledata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['puber', '发布者','callback','admin_username'],
		        			['pubtime', '发布时间','datetime',no_font('未知')],
		        			['click', '阅读量'],
		        			['laud', '赞'],
		        			['tread', '踩'],
		        			['follow', '关注'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
		        	->addRightButtons(['edit']) 
		        	->addRightButton('custom',['title'=>'移入回收站','href'=>url('del',['ids'=>'__ID__']),'icon'=>'fa fa-fw fa-trash-o','class'=>'btn btn-xs btn-default ajax-get']) 
		    		->addTopButtons(['add']) 
		    		->addTopButton('custom',['title'=>'移入回收站','href'=>url('del'),'icon'=>'fa fa-fw fa-trash-o','class'=>'btn btn-primary ajax-post'])
		    		->addTopButton('custom',['title'=>'excel-导入','href'=>url('excelin')]) 
		    		->addTopButton('custom',['title'=>'生成模板','href'=>url('exceltemplet')]) 
		    		->addTopButton('custom',['title'=>'导入说明','href'=>url('excelremark')],true)
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	        case '1':
	        	$order = $this->getOrder();
		        if($order===''){
		            $order=$table.'.pubtime desc,'.$table.'.id desc';
		        }
				$data_list = Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($table.'.is_del','1')->where($table.'.audit','1')->where($map)->order($order)->field($table.'.*')->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('政策清单列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('detail') // 指定数据表名
		        	->addOrder($table.'.id,'.$table.'.pubtime,'.$table.'.click,'.$table.'.laud,'.$table.'.tread,'.$table.'.follow') // 添加排序
		            ->setSearch([$table.'.id']) // 设置搜索参数
					->addTimeFilter($table.'.pubtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilter('career_id', $select_career)  // 添加字段筛选
		            ->addFilter('ident_id', $select_ident)  // 添加字段筛选
		            ->addFilter('category_id', $select_category)  // 添加字段筛选
		            ->addFilter('policy.sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('policy.shi', $select_shi)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['policy_id', '清单内容','callback',function($policy_id,$data){
		        				$policy=Db::name('policy')->where('class_id',1)->order('pubtime desc,addtime desc,id desc')->select();
								$select_policy=[];
								foreach ($policy as $k => $v) {
									$title=$v['title'];
									if($v['is_del']) $title.='(已移除)';
									$select_policy[$v['id']]=$title;
								}
		        				return "<div style='width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;'>{$data['remark']}</div><span style='color:#bbb;display:block;float:left;'>关联：</span><a style='display:block;float:left;width:150px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;' href='".url('xiaozheng/policy/look',['id'=>$policy_id])."' target='_bank' title='查看政策'>{$select_policy[$policy_id]}</a>";
		        			},'__data__'],
		        			['career_id', '事业类型','select',$select_career],
		        			['ident_id', '身份类型','select',$select_ident],
		        			['category_id', '政策类型','select',$select_category],
		        			['sheng', '省','callback',function($data){
		        				$sheng=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('sheng');
		        				if($sheng>0){
		        					return (string)Db::name('city')->where('id',$sheng)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['shi', '市','callback',function($data){
		        				$shi=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('shi');
		        				if($shi>0){
		        					return (string)Db::name('city')->where('id',$shi)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['basis', '基础信息','callback',function($data){
		        				$basis_data=Db::name('basis_data')->where('detail_id',$data['id'])->select();
		        				if($basis_data){
		        					return "<a href='".url('basisdata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['handle', '办理指南','callback',function($data){
		        				$handle_data=Db::name('handle_data')->where('detail_id',$data['id'])->select();
		        				if($handle_data){
		        					return "<a href='".url('handledata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['puber', '发布者','callback','admin_username'],
		        			['pubtime', '发布时间','datetime',no_font('未知')],
		        			['click', '阅读量'],
		        			['laud', '赞'],
		        			['tread', '踩'],
		        			['follow', '关注'],
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
		            $order=$table.'.addtime desc,'.$table.'.id desc';
		        }
				$data_list = Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($table.'.is_del','0')->where($table.'.audit','0')->where($map)->order($order)->field($table.'.*')->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('政策清单列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('detail') // 指定数据表名
		        	->addOrder($table.'.id,'.$table.'.addtime,'.$table.'.click') // 添加排序
		            ->setSearch([$table.'.id']) // 设置搜索参数
					->addTimeFilter($table.'.addtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilter('career_id', $select_career)  // 添加字段筛选
		            ->addFilter('ident_id', $select_ident)  // 添加字段筛选
		            ->addFilter('category_id', $select_category)  // 添加字段筛选
		            ->addFilter('policy.sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('policy.shi', $select_shi)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['policy_id', '清单内容','callback',function($policy_id,$data){
		        				$policy=Db::name('policy')->where('class_id',1)->order('pubtime desc,addtime desc,id desc')->select();
								$select_policy=[];
								foreach ($policy as $k => $v) {
									$title=$v['title'];
									if($v['is_del']) $title.='(已移除)';
									$select_policy[$v['id']]=$title;
								}
		        				return "<div style='width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;'>{$data['remark']}</div><span style='color:#bbb;display:block;float:left;'>关联：</span><a style='display:block;float:left;width:150px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;' href='".url('xiaozheng/policy/look',['id'=>$policy_id])."' target='_bank' title='查看政策'>{$select_policy[$policy_id]}</a>";
		        			},'__data__'],
		        			['career_id', '事业类型','select',$select_career],
		        			['ident_id', '身份类型','select',$select_ident],
		        			['category_id', '政策类型','select',$select_category],
		        			['sheng', '省','callback',function($data){
		        				$sheng=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('sheng');
		        				if($sheng>0){
		        					return (string)Db::name('city')->where('id',$sheng)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['shi', '市','callback',function($data){
		        				$shi=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('shi');
		        				if($shi>0){
		        					return (string)Db::name('city')->where('id',$shi)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['basis', '基础信息','callback',function($data){
		        				$basis_data=Db::name('basis_data')->where('detail_id',$data['id'])->select();
		        				if($basis_data){
		        					return "<a href='".url('basisdata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['handle', '办理指南','callback',function($data){
		        				$handle_data=Db::name('handle_data')->where('detail_id',$data['id'])->select();
		        				if($handle_data){
		        					return "<a href='".url('handledata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '审核', 'callback','audit_run','__data__','2','311'],
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
		            $order=$table.'.addtime desc,'.$table.'.id desc';
		        }
				$data_list = Db::name('detail')->join('policy',$table.'.policy_id=policy.id','LEFT')->where($table.'.is_del','0')->where($table.'.audit','2')->where($map)->order($order)->field($table.'.*')->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('政策清单列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('detail') // 指定数据表名
		        	->addOrder($table.'.id,'.$table.'.addtime') // 添加排序
		            ->setSearch([$table.'.id']) // 设置搜索参数
					->addTimeFilter($table.'.addtime') // 添加时间段筛选
		            ->addFilter('policy_id', $select_policy)  // 添加字段筛选
		            ->addFilter('career_id', $select_career)  // 添加字段筛选
		            ->addFilter('ident_id', $select_ident)  // 添加字段筛选
		            ->addFilter('category_id', $select_category)  // 添加字段筛选
		            ->addFilter('policy.sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('policy.shi', $select_shi)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['policy_id', '清单内容','callback',function($policy_id,$data){
		        				$policy=Db::name('policy')->where('class_id',1)->order('pubtime desc,addtime desc,id desc')->select();
								$select_policy=[];
								foreach ($policy as $k => $v) {
									$title=$v['title'];
									if($v['is_del']) $title.='(已移除)';
									$select_policy[$v['id']]=$title;
								}
		        				return "<div style='width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;'>{$data['remark']}</div><span style='color:#bbb;display:block;float:left;'>关联：</span><a style='display:block;float:left;width:150px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;' href='".url('xiaozheng/policy/look',['id'=>$policy_id])."' target='_bank' title='查看政策'>{$select_policy[$policy_id]}</a>";
		        			},'__data__'],
		        			['career_id', '事业类型','select',$select_career],
		        			['ident_id', '身份类型','select',$select_ident],
		        			['category_id', '政策类型','select',$select_category],
		        			['sheng', '省','callback',function($data){
		        				$sheng=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('sheng');
		        				if($sheng>0){
		        					return (string)Db::name('city')->where('id',$sheng)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['shi', '市','callback',function($data){
		        				$shi=(int)Db::name('policy')->where('id',DB::name('detail')->where('id',$data['id'])->value('policy_id'))->value('shi');
		        				if($shi>0){
		        					return (string)Db::name('city')->where('id',$shi)->value('title');
		        				}else{
		        					return no_font('无');
		        				}
		        			},'__data__'],
		        			['basis', '基础信息','callback',function($data){
		        				$basis_data=Db::name('basis_data')->where('detail_id',$data['id'])->select();
		        				if($basis_data){
		        					return "<a href='".url('basisdata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['handle', '办理指南','callback',function($data){
		        				$handle_data=Db::name('handle_data')->where('detail_id',$data['id'])->select();
		        				if($handle_data){
		        					return "<a href='".url('handledata',['detail_id'=>$data['id']])."' target='_bank'>查看</a>";
		        				}else{
		        					return no_font('暂无');
		        				}
		        			},'__data__'],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '重审', 'callback','audit_run','__data__','3','311'],
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
		$detail=Db::name("detail")->where('id',$id)->find();
		if(!$detail){
			return $this->error('请求错误');
		}
		$policy=Db::name('policy')->where('class_id',1)->order('is_del asc,pubtime desc,addtime desc,id desc')->select();
		$select_policy=[];
		foreach ($policy as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_policy[$v['id']]=$title;
		}
	    $career=Db::name('career')->order('is_del asc,sort asc,id desc')->select();
		$select_career=[];
		foreach ($career as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_career[$v['id']]=$title;
		}
		$ident=Db::name('ident')->order('is_del asc,sort asc,id desc')->select();
		$select_ident=[];
		foreach ($ident as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_ident[$v['id']]=$title;
		}
		$category=Db::name('category')->order('is_del asc,sort asc,id desc')->select();
		$select_category=[];
		foreach ($category as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_category[$v['id']]=$title;
		}
		if($detail['is_del']==0 && $detail['audit']==1)	$status='已发布';
		elseif($detail['is_del']==1 && $detail['audit']==1)	$status='已移除至回收站';
		elseif($detail['is_del']==0 && $detail['audit']==0)	$status='未审核';
		elseif($detail['is_del']==0 && $detail['audit']==2)	$status='审核不通过';
		else $status=no_font('未知');
		$basis_data=Db::name('basis_data a')->join('basis b','a.basis_id=b.id','LEFT')->where('a.detail_id',$detail['id'])->order('b.is_del asc,b.sort asc,b.id desc')->field('a.*')->select();
		$basis_items=[];
		foreach ($basis_data as $k => $v) {
			$basis=Db::name('basis')->where('id',$v['basis_id'])->find();
			if($basis['is_del']==1){
				$title='基础信息_'.$basis['title'].'(已删除)';
			}else{
				$title='基础信息_'.$basis['title'];
			}
			$basis_items[]=['static','basis_'.$v['basis_id'],$title,'',$v['val']];
		}
		$handle_data=Db::name('handle_data a')->join('handle b','a.handle_id=b.id','LEFT')->where('a.detail_id',$detail['id'])->order('b.is_del asc,b.sort asc,b.id desc')->field('a.*')->select();
		$handle_items=[];
		foreach ($handle_data as $k => $v) {
			$handle=Db::name('handle')->where('id',$v['handle_id'])->find();
			if($handle['is_del']==1){
				$title='办理指南_'.$handle['title'].'(已删除)';
			}else{
				$title='办理指南_'.$handle['title'];
			}
			$handle_items[]=['static','handle_'.$v['handle_id'],$title,'',$v['val']];
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看政策清单详情') // 设置页面标题
			//->setPageTips('以下是政策清单的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('id', 'ID','',$detail['id'])
			->addStatic('policy_id', '关联政策','',"<a href='".url('xiaozheng/policy/look',['id'=>$detail['policy_id']])."' target='_bank'>".issetArrOffset($select_policy[$detail['policy_id']])."</a>")
			->addStatic('career_id', '事业类型','',issetArrOffset($select_career[$detail['career_id']]))
			->addStatic('ident_id', '身份类型','',issetArrOffset($select_ident[$detail['ident_id']]))
			->addStatic('category_id', '政策类型','',issetArrOffset($select_category[$detail['category_id']]))
			->addStatic('adder', '创建者','',staticText($detail['adder'],'admin_username'))
			->addStatic('addtime', '创建时间','',staticText($detail['addtime'],'time'))
			->addStatic('puber', '发布者','',staticText($detail['puber'],'admin_username'))
			->addStatic('pubtime', '发布时间','',staticText($detail['pubtime'],'time'))
			->addStatic('click', '阅读量','',$detail['click'])
			->addStatic('status', '状态','',$status)
			->addStatic('source', '来源','',$detail['source'])
			->addStatic('remark', '内容','',$detail['remark'])
			->addFormItems($basis_items)
	    	->addFormItems($handle_items)
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function excelremark(){
		echo "<head><title>清单导入说明</title></head><body><img width='100%' src='".config('public_url')."uploads/exceltpl/detail_remark.png'></body>";
	}
	public function exceltemplet($pix='1'){
		$title=['序号','关联政策','身份类型','事业类型','政策类型','清单来源','清单内容'];
		$basis=Db::name('basis')->where('is_del',0)->order('sort asc,id desc')->select();
		foreach ($basis as $k => $v) {
			$title[]=$v['title'].'-基础';
		}
		$handle=Db::name('handle')->where('is_del',0)->order('sort asc,id desc')->select();
		foreach ($handle as $k => $v) {
			$title[]=$v['title'].'-办理';
		}

		$header=[];
		foreach ($title as $k => $v) {
			$header[$v]='string';
		}
		$rows = array(

		);
        include_once("lib/PHP_XLSXWriter-master/xlsxwriter.class.php");
		$writer = new \XLSXWriter();
		$writer->writeSheetHeader('Sheet1', $header);
		foreach($rows as $row)
			$writer->writeSheetRow('Sheet1', $row);

		$file_name = 'detail-tpl.xlsx';     //下载文件名    
		$file_dir = "public/uploads/exceltpl/";        //下载文件存放目录   

		$writer->writeToFile($file_dir.$file_name);
		
		//检查文件是否存在    
		if (! file_exists ( $file_dir . $file_name )) {    
		    $this->error('生成模板文件失败，请重试');
		} else {    
			header('Location:'.config('root_url').$file_dir.$file_name);
			die;
		} 
	}
	public function excelin(){
		//判断是否为post请求
        if (Request::instance()->isPost()) {
            //获取请求的post数据
            $post=input('post.');
            //数据输入验证
			$validate = new Validate([
			    'excel|导入excel文件'  => 'require',
			    'is_pub|导入后审核状态'  => 'require',
			]);
			if (!$validate->check($post)) {
			    return $this->error($validate->getError());
			}
			$path = Db::name('admin_attachment')->where('id',$post['excel'])->value('path');
            $file = ROOT_PATH.'public/'.$path;
            set_time_limit(0);
            require_once('lib/spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
			require_once('lib/spreadsheet-reader-master/SpreadsheetReader.php');
			date_default_timezone_set('UTC');
			$StartMem = memory_get_usage();
			try
			{
				$Spreadsheet = new \SpreadsheetReader($file);
				$BaseMem = memory_get_usage();

				$Sheets = $Spreadsheet -> Sheets();
				foreach ($Sheets as $Index => $Name)
				{
					if($Index>0) break;
					$Time = microtime(true);
					$Spreadsheet -> ChangeSheet($Index);
					$data=[];
					foreach ($Spreadsheet as $Key => $Row)
					{
						if ($Row)
						{
							$data[]=$Row;
						}
					}
					$title_templet=['序号','关联政策','身份类型','事业类型','政策类型','清单来源','清单内容'];
					$basis=Db::name('basis')->where('is_del',0)->order('sort asc,id desc')->select();
					foreach ($basis as $k => $v) {
						$title_templet[]=$v['title'].'-基础';
					}
					$handle=Db::name('handle')->where('is_del',0)->order('sort asc,id desc')->select();
					foreach ($handle as $k => $v) {
						$title_templet[]=$v['title'].'-办理';
					}
					$sql = "INSERT ignore INTO `%s`.`%s`(`policy_id`,`ident_id`,`career_id`,`category_id`,`source`,`remark`,`adder`,`addtime`,`audit`,`puber`,`pubtime`) VALUES";
		            $sql = sprintf($sql,config('database.database'),config('database.prefix').'detail'); 
		            $fileds = "('%d','%d','%d','%d','%s','%s','%d','%d','%d','%d','%d')";
		            $result=[];
		            if(count($data)<=1){
		            	$return=['status'=>false,'msg'=>'表格中没有可导入的数据'];
		            	break;
		            }
		            $data=allarrtrim($data);
					if($data[0]!=$title_templet){
		            	$return=['status'=>false,'msg'=>'请下载最新导入模板'];
		            	break;
					}
		            $data=allarraddslashes($data);
		            foreach ($data as $k=>$v)
		            {
		                if($k > 0)
		                {
		                    if(intval($v[1])==0){
		                    	$v[1]=intval(substr($v[1],6));
		                    }
		                    $policy=Db::name('policy')->where('class_id',1)->find($v[1]);
		                	if(!$policy){
		                		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'关联人才政策不存在，插入失败'];
		                		continue;
		                	}
		                    if(intval($v[2])>0){
		                    	$ident=Db::name('ident')->find($v[2]);
		                    	if(!$ident){
		                    		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'身份类型不存在，插入失败'];
		                    		continue;
		                    	}
		                    }else{
		                    	$ident=Db::name('ident')->where('title',$v[2])->find();
		                    	if(!$ident){
		                    		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'身份类型不存在，插入失败'];
		                    		continue;
		                    	}
		                    	$v[2]=$ident['id'];
		                    }
		                    if(intval($v[3])>0){
		                    	$career=Db::name('career')->find($v[3]);
		                    	if(!$career){
		                    		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'事业类型不存在，插入失败'];
		                    		continue;
		                    	}
		                    }else{
		                    	$career=Db::name('career')->where('title',$v[3])->find();
		                    	if(!$career){
		                    		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'事业类型不存在，插入失败'];
		                    		continue;
		                    	}
		                    	$v[3]=$career['id'];
		                    }
		                    if(intval($v[4])>0){
		                    	$category=Db::name('category')->find($v[4]);
		                    	if(!$category){
		                    		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'政策类型不存在，插入失败'];
		                    		continue;
		                    	}
		                    }else{
		                    	$category=Db::name('category')->where('title',$v[4])->find();
		                    	if(!$category){
		                    		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'政策类型不存在，插入失败'];
		                    		continue;
		                    	}
		                    	$v[4]=$category['id'];
		                    }
		                    if(!isset($v[6]) || $v[6]===''){
		                    	$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'清单内容为空，插入失败'];
		                    	continue;
		                    }
		                    $detail=Db::name('detail')->where(['policy_id'=>$v[1],'ident_id'=>$v[2],'career_id'=>$v[3],'category_id'=>$v[4],'source'=>$v[5],'remark'=>$v[6]])->find();
		                    if($detail){
		                    	$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'此条清单记录已经存在，插入失败'];
		                    	continue;
		                    }
		                    if($post['is_pub']){
		                    	$values = sprintf($fileds,$v[1],$v[2],$v[3],$v[4],$v[5],$v[6],UID,time(),1,UID,time());
		                    }else{
		                    	$values = sprintf($fileds,$v[1],$v[2],$v[3],$v[4],$v[5],$v[6],UID,time(),0,0,0);
		                    }
		                    Db::execute($sql.$values);
		                    $detail_id=Db::name('detail')->getLastInsID();
		                    foreach ($basis as $key => $value) {
		                    	if(!isset($v[6+1+$key]) || $v[6+1+$key]==='') continue;
								Db::name('basis_data')->insertGetId(['detail_id'=>$detail_id,'basis_id'=>$value['id'],'val'=>$v[6+1+$key]]);
								if($value['type']=='select'){
									$options=explode(PHP_EOL,$value['options']);
									if(!in_array($v[6+1+$key], $options)){
										$options[]=$v[6+1+$key];
										$options=implode(PHP_EOL, $options);
										Db::name('basis')->update(['id'=>$value['id'],'options'=>$options]);
									}
								}
							}
							foreach ($handle as $key => $value) {
		                    	if(!isset($v[6+count($basis)+1+$key]) || $v[6+count($basis)+1+$key]==='') continue;
								Db::name('handle_data')->insertGetId(['detail_id'=>$detail_id,'handle_id'=>$value['id'],'val'=>$v[6+count($basis)+1+$key]]);
								if($value['type']=='select'){
									$options=explode(PHP_EOL,$value['options']);
									if(!in_array($v[6+count($basis)+1+$key], $options)){
										$options[]=$v[6+count($basis)+1+$key];
										$options=implode(PHP_EOL, $options);
										Db::name('handle')->update(['id'=>$value['id'],'options'=>$options]);
									}
								}
							}
		                    $result[]=['num'=>$v[0],'status'=>1,'id'=>$detail_id,'msg'=>'插入成功'];
		                }
		            }
		            session('detail_excel',$result);
					$return=['status'=>true,'msg'=>'导入成功'];
				}
			}
			catch (\Exception $E)
			{
				$return=['status'=>false,'msg'=>$E -> getMessage()];
			}
			if($return['status']){
				return $this->success('导入成功','excelview','',1);
			}else{
				return $this->error('导入失败：'.$return['msg']);
			}
        }
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('[清单]导入excel') // 设置页面标题
            ->setPageTips("导入后系统会自动更新清单库数据<br><span style='color:rgb(239,162,49);'>导入时请使用最新模板，并且切勿修改模板文件的第一行标题</span><br><span style='color:#f00000;'>数据量过大的话，导入时间会很长，请耐心等待</span>") // 设置页面提示信息
            //->setUrl('') // 设置表单提交地址
            //->hideBtn(['back']) //隐藏默认按钮
            ->setBtnTitle('submit', '确定') //修改默认按钮标题
            ->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
            ->addFile('excel', '请选择excel文件', '只能选择后缀为xls、xlsx的文件', '', '2048', 'xls,xlsx')
            ->addRadio('is_pub','导入后状态','',['0'=>'未审核','1'=>'已发布'])
            //->isAjax(false) //默认为ajax的post提交
            ->fetch();
    }
    public function excelview(){
		$data_list=session('detail_excel');
		if(!$data_list){
			return $this->error('本次登录没有进行过导入操作');
		}
		$success=0;
		$fail=0;
		foreach ($data_list as $k => $v) {
			if($v['status']){
				$success+=1;
			}else{
				$fail+=1;
			}
		}
		return ZBuilder::make('table')
        	->setPageTitle('导入结果') // 设置页面标题
        	->setPageTips('此结果只做临时查看使用，账号退出之后将丢失<br>导入成功<b style="color:#46c37b;">'.$success.'</b>条，失败<b style="color:#f00000;">'.$fail.'</b>条') // 设置页面提示信息
        	->hideCheckbox()
        	->addColumns([
        			['num', '序号'],
        			['id', '新增记录','callback',function($id){
        				return $id>0?"<a title='查看' target='_bank' href='".url('look',['id'=>$id])."'>{$id}</a>":no_font('无');
        			}],
        			['status', '导入状态','status','',['失败','成功']],
        			['msg', '导入情况'],
        		]) //添加多列数据
        	->setRowList($data_list) // 设置表格数据
        	->fetch();
    	break;
	}
	public function delete($record = []){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('detail')->where('id','in',$ids)->delete();
		if($rt!==false){
			Db::name('basis_data')->where('detail_id','in',$ids)->delete();
			Db::name('handle_data')->where('detail_id','in',$ids)->delete();
			return $this->success('删除成功',false,'',1);
        } else {
            return $this->error('删除失败');
        }
	}
	public function audit(){
		$audit=input('audit','0');
		$status=input('status','0');
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('detail')->where('id','in',$ids)->update(['audit'=>$audit]);
		if($rt!==false){
			if($audit=='1'){
				Db::name('detail')->where('id','in',$ids)->update(['pubtime'=>time(),'puber'=>UID]);
			}
			return $this->success('审核成功',false,'',1);
        } else {
            return $this->error('审核失败');
        }
	}
	public function del(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('detail')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('detail')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'policy_id|关联政策'  => 'require',
			    'career_id|事业类型'  => 'require',
			    'ident_id|身份类型'  => 'require',
			    'category_id|政策类型'  => 'require',
			    'remark|清单内容'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$insert=array();
			$insert['policy_id']=$data['policy_id'];
			$insert['career_id']=$data['career_id'];
			$insert['ident_id']=$data['ident_id'];
			$insert['category_id']=$data['category_id'];
			$insert['source']=$data['source'];
			$insert['remark']=$data['remark'];
			$insert['addtime']=time();
			$insert['adder']=UID;
			//数据入库
			$detail_id=Db::name("detail")->insertGetId($insert);
			//跳转
			if($detail_id>0){
				//其他处理
				$basis=Db::name('basis')->where('is_del',0)->order('sort asc,id desc')->select();
				$basis_data=[];
				foreach ($basis as $k => $v) {
					if(isset($data['basis_'.$v['id']]) && $data['basis_'.$v['id']]!==''){
						$basis_data[]=['detail_id'=>$detail_id,'basis_id'=>$v['id'],'val'=>$data['basis_'.$v['id']]];
					}
				}
				Db::name("basis_data")->insertAll($basis_data);
				$handle=Db::name('handle')->where('is_del',0)->order('sort asc,id desc')->select();
				$handle_data=[];
				foreach ($handle as $k => $v) {
					if(isset($data['handle_'.$v['id']]) && $data['handle_'.$v['id']]!==''){
						$handle_data[]=['detail_id'=>$detail_id,'handle_id'=>$v['id'],'val'=>$data['handle_'.$v['id']]];
					}
				}
				Db::name("handle_data")->insertAll($handle_data);
				return $this->success('添加成功',url('index',['status'=>(int)session('jump_status')]),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
		$policy=Db::name('policy')->where('class_id',1)->order('is_del asc,pubtime desc,addtime desc,id desc')->select();
		$select_policy=[];
		foreach ($policy as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_policy[$v['id']]=$title;
		}
	    $career=Db::name('career')->order('is_del asc,sort asc,id desc')->select();
		$select_career=[];
		foreach ($career as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_career[$v['id']]=$title;
		}
		$ident=Db::name('ident')->order('is_del asc,sort asc,id desc')->select();
		$select_ident=[];
		foreach ($ident as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_ident[$v['id']]=$title;
		}
		$category=Db::name('category')->order('is_del asc,sort asc,id desc')->select();
		$select_category=[];
		foreach ($category as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_category[$v['id']]=$title;
		}
		$basis=Db::name('basis')->where('is_del',0)->order('sort asc,id desc')->select();
		$basis_items=[];
		foreach ($basis as $k => $v) {
			switch ($v['type']) {
				case 'text':
					$basis_items[]=['text', 'basis_'.$v['id'],'基础信息_'.$v['title'],'',''];
					break;
				case 'textarea':
					$basis_items[]=['textarea', 'basis_'.$v['id'],'基础信息_'.$v['title'],'',''];
					break;
				case 'view':
					$basis_items[]=['ckeditor', 'basis_'.$v['id'], '基础信息_'.$v['title'],'','','',200];
					break;
				case 'select':
					$options=explode(PHP_EOL, $v['options']);
					foreach($options as $key=>$value){
						$options[$value]=$value;
						unset($options[$key]);
					}
					$basis_items[]=['select', 'basis_'.$v['id'], '基础信息_'.$v['title'],'',$options];
					break;
				default:
					break;
			}
		}
		$handle=Db::name('handle')->where('is_del',0)->order('sort asc,id desc')->select();
		$handle_items=[];
		foreach ($handle as $k => $v) {
			switch ($v['type']) {
				case 'text':
					$basis_items[]=['text', 'handle_'.$v['id'],'办理指南_'.$v['title'],'',''];
					break;
				case 'textarea':
					$handle_items[]=['textarea', 'handle_'.$v['id'],'办理指南_'.$v['title'],'',''];
					break;
				case 'view':
					$handle_items[]=['ckeditor', 'handle_'.$v['id'], '办理指南_'.$v['title'],'','','',200];
					break;
				case 'select':
					$_id=$v['id'];
					$options=explode(PHP_EOL, $v['options']);
					foreach($options as $key=>$value){
						$options[$value]=$value;
						unset($options[$key]);
					}
					$basis_items[]=['select', 'handle_'.$v['id'], '办理指南_'.$v['title'],'',$options];
					break;
				default:
					break;
			}
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加政策清单') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addSelect('policy_id', '关联政策','',$select_policy)
			->addSelect('career_id', '事业类型','',$select_career)
			->addSelect('ident_id', '身份类型','',$select_ident)
			->addSelect('category_id', '政策类型','',$select_category)
    		->addText('source', '清单来源','','')
    		->addTextarea('remark', '清单内容','','')
    		->addFormItems($basis_items)
    		->addFormItems($handle_items)
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
				'id|清单ID'=>'require',
			    'policy_id|关联政策'  => 'require',
			    'career_id|事业类型'  => 'require',
			    'ident_id|身份类型'  => 'require',
			    'category_id|政策类型'  => 'require',
			    'remark|清单内容'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['policy_id']=$data['policy_id'];
			$update['career_id']=$data['career_id'];
			$update['ident_id']=$data['ident_id'];
			$update['category_id']=$data['category_id'];
			$update['source']=$data['source'];
			$update['remark']=$data['remark'];
			//数据更新
			$rt=Db::name("detail")->update($update);
			//跳转
			if($rt!==false){
				//其他处理
				$detail_id=$data['id'];
				$basis=Db::name('basis')->where('is_del',0)->order('sort asc,id desc')->select();
				foreach ($basis as $k => $v) {
					if(isset($data['basis_'.$v['id']])){
						$old=Db::name('basis_data')->where(['detail_id'=>$detail_id,'basis_id'=>$v['id']])->find();
						if($old){
							Db::name("basis_data")->where('id',$old['id'])->update(['val'=>$data['basis_'.$v['id']]]);
						}else{
							$basis_data=['detail_id'=>$detail_id,'basis_id'=>$v['id'],'val'=>$data['basis_'.$v['id']]];
							Db::name("basis_data")->insertGetId($basis_data);
						}
					}
				}
				$handle=Db::name('handle')->where('is_del',0)->order('sort asc,id desc')->select();
				$handle_data=[];
				foreach ($handle as $k => $v) {
					if(isset($data['handle_'.$v['id']])){
						$old=Db::name('handle_data')->where(['detail_id'=>$detail_id,'handle_id'=>$v['id']])->find();
						if($old){
							Db::name("handle_data")->where('id',$old['id'])->update(['val'=>$data['handle_'.$v['id']]]);
						}else{
							$handle_data=['detail_id'=>$detail_id,'handle_id'=>$v['id'],'val'=>$data['handle_'.$v['id']]];
							Db::name("handle_data")->insertGetId($handle_data);
						}
					}
				}
				return $this->success('修改成功',url('index',['status'=>(int)session('jump_status')]),'',1);
	        } else {
	            return $this->error('修改失败');
	        }
		}
		// 接收id
		if ($id>0) {
			// 查处数据
			$detail=Db::name("detail")->where('id',$id)->find();
			if(!$detail){
				return $this->error('请求错误');
			}
			$policy=Db::name('policy')->where('class_id',1)->order('is_del asc,pubtime desc,addtime desc,id desc')->select();
			$select_policy=[];
			foreach ($policy as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_policy[$v['id']]=$title;
			}
		    $career=Db::name('career')->order('is_del asc,sort asc,id desc')->select();
			$select_career=[];
			foreach ($career as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_career[$v['id']]=$title;
			}
			$ident=Db::name('ident')->order('is_del asc,sort asc,id desc')->select();
			$select_ident=[];
			foreach ($ident as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_ident[$v['id']]=$title;
			}
			$category=Db::name('category')->order('is_del asc,sort asc,id desc')->select();
			$select_category=[];
			foreach ($category as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_category[$v['id']]=$title;
			}
			$basis=Db::name('basis')->where('is_del',0)->order('sort asc,id desc')->select();
			$basis_items=[];
			foreach ($basis as $k => $v) {
				switch ($v['type']) {
					case 'text':
						$basis_items[]=['text', 'basis_'.$v['id'],'基础信息_'.$v['title'],'',
						(string)Db::name('basis_data')->where(['detail_id'=>$id,'basis_id'=>$v['id']])->value('val')];
						break;
					case 'textarea':
						$basis_items[]=['textarea', 'basis_'.$v['id'],'基础信息_'.$v['title'],'',
						(string)Db::name('basis_data')->where(['detail_id'=>$id,'basis_id'=>$v['id']])->value('val')];
						break;
					case 'view':
						$basis_items[]=['ckeditor', 'basis_'.$v['id'], '基础信息_'.$v['title'],'',
						(string)Db::name('basis_data')->where(['detail_id'=>$id,'basis_id'=>$v['id']])->value('val'),'',200];
						break;
					case 'select':
						$options=explode(PHP_EOL, $v['options']);
						foreach($options as $key=>$value){
							$options[$value]=$value;
							unset($options[$key]);
						}
						$basis_items[]=['select', 'basis_'.$v['id'], '基础信息_'.$v['title'],'',$options,
						(string)Db::name('basis_data')->where(['detail_id'=>$id,'basis_id'=>$v['id']])->value('val')];
						break;
					default:
						break;
				}
			}
			$handle=Db::name('handle')->where('is_del',0)->order('sort asc,id desc')->select();
			$handle_items=[];
			foreach ($handle as $k => $v) {
				switch ($v['type']) {
					case 'text':
						$handle_items[]=['text', 'handle_'.$v['id'],'办理指南_'.$v['title'],'',
						(string)Db::name('handle_data')->where(['detail_id'=>$id,'handle_id'=>$v['id']])->value('val')];
						break;
					case 'textarea':
						$handle_items[]=['textarea', 'handle_'.$v['id'],'办理指南_'.$v['title'],'',
						(string)Db::name('handle_data')->where(['detail_id'=>$id,'handle_id'=>$v['id']])->value('val')];
						break;
					case 'view':
						$handle_items[]=['ckeditor', 'handle_'.$v['id'], '办理指南_'.$v['title'],'',(string)Db::name('handle_data')->where(['detail_id'=>$id,'handle_id'=>$v['id']])->value('val'),'',200];
						break;
					case 'select':
						$_id=$v['id'];
						$options=explode(PHP_EOL, $v['options']);
						foreach($options as $key=>$value){
							$options[$value]=$value;
							unset($options[$key]);
						}
						$basis_items[]=['select', 'handle_'.$v['id'], '办理指南_'.$v['title'],'',$options,(string)Db::name('handle_data')->where(['detail_id'=>$id,'handle_id'=>$v['id']])->value('val')];
						break;
					default:
						break;
				}
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改政策清单') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addSelect('policy_id', '关联政策','',$select_policy,$detail['policy_id'])
				->addSelect('career_id', '事业类型','',$select_career,$detail['career_id'])
				->addSelect('ident_id', '身份类型','',$select_ident,$detail['ident_id'])
				->addSelect('category_id', '政策类型','',$select_category,$detail['category_id'])
    		    ->addText('source', '清单来源','',$detail['source'])
	    		->addTextarea('remark', '清单内容','',$detail['remark'])
	    		->addFormItems($basis_items)
	    		->addFormItems($handle_items)
				->addHidden('id',$detail['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
	public function basisdata($detail_id){
		$data_list=Db::name('basis_data a')->join('basis b','a.basis_id=b.id','LEFT')->where('a.detail_id',$detail_id)->field('a.id,a.val,b.title,b.is_del,b.type')->order('b.is_del asc,b.sort asc')->select();
		return ZBuilder::make('table')
        	->setPageTitle('清单['.$detail_id.']-基础信息') // 设置页面标题
        	//->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
        	->setTableName('basis_data') // 指定数据表名
            ->addColumns([
            	['title','标题','callback',function($title,$data){
            		if($data['is_del']){
            			return $title.'(已删除)';
            		}else{
            			return $title;
            		}
            	},'__data__'],
            	['val','内容','callback',function($val,$data){
            		if($data['type']=='view'){
            			return "<a href='".url('datalook',['table'=>'basis','id'=>$data['id']])."' target='_bank' class='pop' data-toggle='tooltip' data-original-title='查看详情'>查看</a>";
            		}else{
            			return $val===''?no_font('暂无'):$val;
            		}
            	},'__data__'],
            	['right_button', '操作', 'btn'],
            ])
            ->addRightButton('custom',['title'=>'修改','href'=>url('dataedit',['table'=>'basis','id'=>'__ID__']),'icon'=>'fa fa-fw fa-pencil']) 
		    ->addRightButton('delete',['href'=>url('datadelete',['table'=>'basis','ids'=>'__ID__'])])
		    ->addTopButton('delete',['href'=>url('datadelete',['table'=>'basis'])])
            ->setRowList($data_list) // 设置表格数据
        	->fetch();
	}
	public function handledata($detail_id){
		$data_list=Db::name('handle_data a')->join('handle b','a.handle_id=b.id','LEFT')->where('a.detail_id',$detail_id)->field('a.id,a.val,b.title,b.is_del,b.type')->order('b.is_del asc,b.sort asc')->select();
		return ZBuilder::make('table')
        	->setPageTitle('清单['.$detail_id.']-办理指南') // 设置页面标题
        	//->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
        	->setTableName('handle_data') // 指定数据表名
            ->addColumns([
            	['title','标题','callback',function($title,$data){
            		if($data['is_del']){
            			return $title.'(已删除)';
            		}else{
            			return $title;
            		}
            	},'__data__'],
            	['val','内容','callback',function($val,$data){
            		if($data['type']=='view'){
            			return "<a href='".url('datalook',['table'=>'handle','id'=>$data['id']])."' target='_bank' class='pop' data-toggle='tooltip' data-original-title='查看详情'>查看</a>";
            		}else{
            			return $val===''?no_font('暂无'):$val;
            		}
            	},'__data__'],
            	['right_button', '操作', 'btn'],
            ])
            ->addRightButton('custom',['title'=>'修改','href'=>url('dataedit',['table'=>'handle','id'=>'__ID__']),'icon'=>'fa fa-fw fa-pencil']) 
		    ->addRightButton('delete',['href'=>url('datadelete',['table'=>'handle','ids'=>'__ID__'])])
		    ->addTopButton('delete',['href'=>url('datadelete',['table'=>'handle'])])
            ->setRowList($data_list) // 设置表格数据
        	->fetch();
	}
	public function datadelete($table){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name($table.'_data')->where('id','in',$ids)->delete();
		if($rt!==false){
			return $this->success('删除成功',false,'',1);
        } else {
            return $this->error('删除失败');
        }
	}
	public function datalook($table,$id){
		$val=(string)Db::name($table.'_data')->where('id',$id)->value('val');
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看内容详情') // 设置页面标题
			//->setPageTips('以下是政策的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('content', '内容详情','',staticText($val))
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function dataedit($table,$id){
		
		//判断是否为post请求
		if (Request::instance()->isPost()) {
			//获取请求的post数据
			$data=input('post.');
			//数据输入验证
			$validate = new Validate([
				'id|ID'  => 'require',
				'table|table'  => 'require',
			    'val|内容'  => 'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			if($data['val']===''){
				//数据更新
				$rt=Db::name($data['table'].'_data')->where('id',$data['id'])->delete();
				//跳转
				if($rt!==false){
					return $this->success('因修改为空，已自动删除此条信息',url($data['table'].'data',['detail_id'=>$detail_id]),'',1);
		        } else {
		            return $this->error('修改失败');
		        }
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['val']=$data['val'];
			//数据更新
			$rt=Db::name($data['table'].'_data')->update($update);
			//跳转
			if($rt!==false){
				$detail_id=Db::name($data['table'].'_data')->where('id',$data['id'])->value('detail_id');
				return $this->success('修改成功',url($data['table'].'data',['detail_id'=>$detail_id]),'',1);
	        } else {
	            return $this->error('修改失败');
	        }
		}
		// 接收id
		if ($id>0) {
			// 查处数据
			$da=Db::name($table.'_data a')->join($table.' b','a.'.$table.'_id=b.id','LEFT')->where('a.id',$id)->field('a.*,b.title,b.type,b.options')->find();
			if(!$da){
				return $this->error('请求错误');
			}
			$items=[];
			switch ($da['type']) {
				case 'text':
					$items[]=['text', 'val','内容','',$da['val']];
					break;
				case 'textarea':
					$items[]=['textarea', 'val','内容','',$da['val']];
					break;
				case 'view':
					$items[]=['ckeditor', 'val','内容','',$da['val']];
					break;
				case 'select':
					$options=explode(PHP_EOL, $da['options']);
					foreach($options as $key=>$value){
						$options[$value]=$value;
						unset($options[$key]);
					}
					$items[]=['select', 'val','内容','',$options,$da['val']];
					break;
				default:
					break;
			}
			
			if($table=='basis') $pageTitle='修改基础信息';
			if($table=='handle') $pageTitle='修改办理指南';
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle($pageTitle) // 设置页面标题
				->setPageTips('若修改为空，则删除此条信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addStatic('title', '名称','',$da['title'])
				->addFormItems($items)
				->addHidden('id',$da['id'])
				->addHidden('table',$table)
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}