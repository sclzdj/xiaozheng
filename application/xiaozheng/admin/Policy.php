<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;
use app\xiaozheng\model\City as CityModel;


class Policy extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('policy')->where($map)->where('is_del','0')->where('audit','1')->value('count(id)');
		$count_2=Db::name('policy')->where($map)->where('is_del','1')->where('audit','1')->value('count(id)'); 
		$count_3=Db::name('policy')->where($map)->where('is_del','0')->where('audit','0')->value('count(id)');
		$count_4=Db::name('policy')->where($map)->where('is_del','0')->where('audit','2')->value('count(id)');   
		$list_tab = [
	        '0' => ['title' => '已发布('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	        '1' => ['title' => '回收站('.$count_2.')', 'url' => url('index', ['status' => '1'])],
	        '2' => ['title' => '未审核('.$count_3.')', 'url' => url('index', ['status' => '2'])],
	        '3' => ['title' => '审核不通过('.$count_4.')', 'url' => url('index', ['status' => '3'])],
	    ];
	    $class=Db::name('class')->order('is_del asc,sort asc,id desc')->select();
		$select_class=[];
		foreach ($class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_class[$v['id']]=$title;
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
		$tips="<br><span style='color:rgb(239,162,49);'>解读数量和清单数量的数字分别为【已发布】、【回收站】、【未审核】、【审核不通过】的统计数量，点击可快速链接到相应的条目</span>";
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='pubtime desc,id desc';
		        }
				$data_list = Db::name('policy')->where('is_del','0')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('政策列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作'.$tips) // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('policy') // 指定数据表名
		        	->addOrder('id,pubtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		            ->addFilterMap('shi', 'sheng')//省市条件
		            ->addFilterMap('class_id,sheng,shi', ['is_del'=>0,'audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['code', '编号', 'callback', function($data){
									$policy_max_id=Db::name('policy')->order('id desc')->limit(1)->value('id');
									$code_num_length=strlen(strval(intval($policy_max_id)));
									if($code_num_length<config('xiaozheng.code_num_length')){
										$code_num_length=config('xiaozheng.code_num_length');
									}
									$code="";
									$prefix=Db::name('class')->where('id',$data['class_id'])->value('prefix');
									if($prefix===null){
										$prefix='ZC';
									}
									$year=date('Y',$data['pubtime']);
									$num=str_pad($data['id'],$code_num_length,"0",STR_PAD_LEFT);
									$code=$prefix.$year.$num;
									return $code;
				   				}, '__data__'],
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['department', '实施部门'],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
		        			['puber', '发布者','callback','admin_username'],
		        			['pubtime', '发布时间','datetime',no_font('未知')],
		        			['click', '阅读量'],
		        			['unscrambles','解读数量','callback',function($data){
		        				$count_1=Db::name('unscramble')->where(['audit'=>1,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_2=Db::name('unscramble')->where(['audit'=>1,'is_del'=>1])->where('policy_id',$data['id'])->count();
		        				$count_3=Db::name('unscramble')->where(['audit'=>0,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_4=Db::name('unscramble')->where(['audit'=>2,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$str=[];
		        				$str[]=$count_1>0?"<a target='_bank' title='点击查看已发布的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=0&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_1}</a>":0;
		        				$str[]=$count_2>0?"<a target='_bank' title='点击查看回收站的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=1&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_2}</a>":0;
		        				$str[]=$count_3>0?"<a target='_bank' title='点击查看未审核的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=2&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_3}</a>":0;
		        				$str[]=$count_4>0?"<a target='_bank' title='点击查看审核不通过的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=3&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_4}</a>":0;
		        				return implode('&nbsp;,&nbsp;', $str);
		        			},'__data__'],
		        			['details','清单数量','callback',function($data){
		        				$count_1=Db::name('detail')->where(['audit'=>1,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_2=Db::name('detail')->where(['audit'=>1,'is_del'=>1])->where('policy_id',$data['id'])->count();
		        				$count_3=Db::name('detail')->where(['audit'=>0,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_4=Db::name('detail')->where(['audit'=>2,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$str=[];
		        				$str[]=$count_1>0?"<a target='_bank' title='点击查看已发布的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=0&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_1}</a>":0;
		        				$str[]=$count_2>0?"<a target='_bank' title='点击查看回收站的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=1&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_2}</a>":0;
		        				$str[]=$count_3>0?"<a target='_bank' title='点击查看未审核的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=2&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_3}</a>":0;
		        				$str[]=$count_4>0?"<a target='_bank' title='点击查看审核不通过的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=3&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_4}</a>":0;
		        				return implode('&nbsp;,&nbsp;', $str);
		        			},'__data__'],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('custom',['title'=>'查看详情','href'=>url('look',['id'=>'__ID__']),'icon'=>'fa fa-fw fa-eye'],true) 
		        	->addRightButtons(['edit']) 
		        	->addRightButton('custom',['title'=>'添加解读','href'=>url('xiaozheng/unscramble/add',['policy_id'=>'__ID__']),'icon'=>'fa fa-fw fa-file-text-o']) 
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
				$data_list = Db::name('policy')->where('is_del','1')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('政策列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作'.$tips) // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('policy') // 指定数据表名
		        	->addOrder('id,pubtime,click') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		            ->addFilterMap('shi', 'sheng')//省市条件
		            ->addFilterMap('class_id,sheng,shi', ['is_del'=>1,'audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['code', '编号', 'callback', function($data){
									$policy_max_id=Db::name('policy')->order('id desc')->limit(1)->value('id');
									$code_num_length=strlen(strval(intval($policy_max_id)));
									if($code_num_length<config('xiaozheng.code_num_length')){
										$code_num_length=config('xiaozheng.code_num_length');
									}
									$code="";
									$prefix=Db::name('class')->where('id',$data['class_id'])->value('prefix');
									if($prefix===null){
										$prefix='ZC';
									}
									$year=date('Y',$data['addtime']);
									$num=str_pad($data['id'],$code_num_length,"0",STR_PAD_LEFT);
									$code=$prefix.$year.$num;
									return $code;
				   				}, '__data__'],
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['department', '实施部门'],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
		        			['puber', '发布者','callback','admin_username'],
		        			['pubtime', '发布时间','datetime',no_font('未知')],
		        			['click', '阅读量'],
		        			['unscrambles','解读数量','callback',function($data){
		        				$count_1=Db::name('unscramble')->where(['audit'=>1,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_2=Db::name('unscramble')->where(['audit'=>1,'is_del'=>1])->where('policy_id',$data['id'])->count();
		        				$count_3=Db::name('unscramble')->where(['audit'=>0,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_4=Db::name('unscramble')->where(['audit'=>2,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$str=[];
		        				$str[]=$count_1>0?"<a target='_bank' title='点击查看已发布的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=0&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_1}</a>":0;
		        				$str[]=$count_2>0?"<a target='_bank' title='点击查看回收站的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=1&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_2}</a>":0;
		        				$str[]=$count_3>0?"<a target='_bank' title='点击查看未审核的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=2&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_3}</a>":0;
		        				$str[]=$count_4>0?"<a target='_bank' title='点击查看审核不通过的解读' href='".config('host_url')."/admin.php/xiaozheng/unscramble/index?status=3&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_4}</a>":0;
		        				return implode('&nbsp;,&nbsp;', $str);
		        			},'__data__'],
		        			['details','清单数量','callback',function($data){
		        				$count_1=Db::name('detail')->where(['audit'=>1,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_2=Db::name('detail')->where(['audit'=>1,'is_del'=>1])->where('policy_id',$data['id'])->count();
		        				$count_3=Db::name('detail')->where(['audit'=>0,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$count_4=Db::name('detail')->where(['audit'=>2,'is_del'=>0])->where('policy_id',$data['id'])->count();
		        				$str=[];
		        				$str[]=$count_1>0?"<a target='_bank' title='点击查看已发布的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=0&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_1}</a>":0;
		        				$str[]=$count_2>0?"<a target='_bank' title='点击查看回收站的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=1&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_2}</a>":0;
		        				$str[]=$count_3>0?"<a target='_bank' title='点击查看未审核的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=2&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_3}</a>":0;
		        				$str[]=$count_4>0?"<a target='_bank' title='点击查看审核不通过的清单' href='".config('host_url')."/admin.php/xiaozheng/detail/index?status=3&_filter=policy_id&_filter_content={$data['id']}&_field_display=policy_id'>{$count_4}</a>":0;
		        				return implode('&nbsp;,&nbsp;', $str);
		        			},'__data__'],
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
				$data_list = Db::name('policy')->where('is_del','0')->where('audit','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('政策列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作<br><span style="color:#EFA231;">审核通过后系统自动生成政策编号</span>') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('policy') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		            ->addFilterMap('shi', 'sheng')//省市条件
		            ->addFilterMap('class_id,sheng,shi', ['is_del'=>0,'audit'=>0])//筛选条件
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['department', '实施部门'],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '审核', 'callback','audit_run','__data__','2','293'],
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
				$data_list = Db::name('policy')->where('is_del','0')->where('audit','2')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('政策列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作<br><span style="color:#EFA231;">审核通过后系统自动生成政策编号</span>') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('policy') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','title']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('class_id', $select_class)  // 添加字段筛选
		            ->addFilter('sheng', $select_sheng)  // 添加字段筛选
		            ->addFilter('shi', $select_shi)  // 添加字段筛选
		            ->addFilterMap('shi', 'sheng')//省市条件
		            ->addFilterMap('class_id,sheng,shi', ['is_del'=>0,'audit'=>2])//筛选条件
		        	->addColumns([
		        			['id', 'ID'],
		        			['title', '标题'],
		        			['class_id', '政策类型','select',$select_class],
		        			['pic_id', '图片','picture','暂无图片'],
		        			['department', '实施部门'],
		        			['sheng', '省','callback','array_v',$select_sheng],
		        			['shi', '市','callback','array_v',$select_shi],
		        			['adder', '创建者','callback','admin_username'],
		        			['addtime', '创建时间','datetime',no_font('未知')],
		        			['audit_status', '重审', 'callback','audit_run','__data__','3','293'],
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
		$policy=Db::name("policy")->where('id',$id)->find();
		if(!$policy){
			return $this->error('请求错误');
		}
		$city=Db::name('city')->select();
		$select_city=[0=>''];
		foreach ($city as $k => $v) {
			$select_city[$v['id']]=$v['title'];
		}
		$class=Db::name('class')->order('is_del asc,sort asc,id desc')->select();
		$select_class=['0'=>'其它'];
		foreach ($class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_class[$v['id']]=$title;
		}
		if($policy['is_del']==0 && $policy['audit']==1)	$status='已发布';
		elseif($policy['is_del']==1 && $policy['audit']==1)	$status='已移除至回收站';
		elseif($policy['is_del']==0 && $policy['audit']==0)	$status='未审核';
		elseif($policy['is_del']==0 && $policy['audit']==2)	$status='审核不通过';
		else $status=no_font('未知');
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看政策详情') // 设置页面标题
			//->setPageTips('以下是政策的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('id', 'ID','',$policy['id'])
			->addStatic('title', '标题','',$policy['title'])
			->addStatic('class_id', '政策类型','',issetArrOffset($select_class[$policy['class_id']]))
			->addStatic('pic_id', '图片','',staticText($news['pic_id'],'pic'))
			->addStatic('city', '城市','',issetArrOffset($select_city['1']).'&nbsp;&nbsp;'.issetArrOffset($select_city[$policy['sheng']]).'&nbsp;&nbsp;'.issetArrOffset($select_city[$policy['shi']]))
			->addStatic('department', '实施部门','',staticText($policy['department']))
			->addStatic('adder', '创建者','',staticText($policy['adder'],'admin_username'))
			->addStatic('addtime', '创建时间','',staticText($policy['addtime'],'time'))
			->addStatic('puber', '发布者','',staticText($policy['puber'],'admin_username'))
			->addStatic('pubtime', '发布时间','',staticText($policy['pubtime'],'time'))
			->addStatic('file_ids', '附件','',staticText($policy['file_ids'],'files'))
			->addStatic('click', '阅读量','',$policy['click'])
			->addStatic('status', '状态','',$status)
			->addStatic('content', '原文','',staticText($policy['content']))
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function audit(){
		$audit=input('audit','0');
		$status=input('status','0');
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('policy')->where('id','in',$ids)->update(['audit'=>$audit]);
		if($rt!==false){
			if($audit=='1'){
				Db::name('policy')->where('id','in',$ids)->update(['pubtime'=>time(),'puber'=>UID]);
			}
			return $this->success('审核成功',false,'',1);
        } else {
            return $this->error('审核失败');
        }
	}
	public function del(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('policy')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('policy')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'pic_id|图片'  => 'require',
			    'content|原文'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$insert=array();
			$insert['title']=$data['title'];
			$insert['class_id']=$data['class_id'];
			$insert['pic_id']=$data['pic_id'];
			$insert['sheng']=$data['sheng'];
			$insert['shi']=$data['shi'];
			$insert['content']=$data['content'];
			$insert['department']=$data['department'];
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
			$policy_id=Db::name("policy")->insertGetId($insert);
			//跳转
			if($policy_id>0){
				return $this->success('添加成功',url('index',['status'=>(int)session('jump_status')]),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
	    $class=Db::name('class')->order('is_del asc,sort asc,id desc')->select();
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
			->setPageTitle('添加政策') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('title', '标题','','')
			->addSelect('class_id', '政策类型','',$select_class)
			->addImage('pic_id', '图片','','')
			->addLinkage('sheng', '所属省', '', $select_sheng, '', url('get_shi'), 'shi')
    		->addSelect('shi', '所属市')
    		->addText('department', '实施部门','','')
    		->addFiles('file_ids', '附件','','')
			->addUeditor('content', '原文','','')
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
			    'pic_id|图片'  => 'require',
			    'content|原文'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['class_id']=$data['class_id'];
			$update['pic_id']=$data['pic_id'];
			$update['title']=$data['title'];
			$update['sheng']=$data['sheng'];
			$update['shi']=$data['shi'];
			$update['content']=$data['content'];
			$update['department']=$data['department'];
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
			$rt=Db::name("policy")->update($update);
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
			$policy=Db::name("policy")->where('id',$id)->find();
			if(!$policy){
				return $this->error('请求错误');
			}
		    $class=Db::name('class')->order('is_del asc,sort asc,id desc')->select();
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
	        $list_shi=Db::name('city')->order('sort asc,id desc')->where('pid',$policy['sheng'])->select();
	        $select_shi=[];
	        foreach ($list_shi as $k => $v) {
	            $select_shi[$v['id']]=$v['title'];
	        }
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改政策') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addText('title', '标题','',$policy['title'])
				->addSelect('class_id', '政策类型','',$select_class,$policy['class_id'])
				->addImage('pic_id', '图片','',$policy['pic_id'])
				->addLinkage('sheng', '所属省', '', $select_sheng,$policy['sheng'], url('get_shi'), 'shi')
    			->addSelect('shi', '所属市','',$select_shi,$policy['shi'])
    			->addText('department', '实施部门','',$policy['department'])
    			->addFiles('file_ids', '附件','',$policy['file_ids'])
				->addUeditor('content', '原文','',$policy['content'])
				->addHidden('id',$policy['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}