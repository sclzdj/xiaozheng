<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Qa extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('qa')->where($map)->where('is_del','0')->where('audit','1')->value('count(id)');
		$count_2=Db::name('qa')->where($map)->where('is_del','1')->where('audit','1')->value('count(id)'); 
		$count_3=Db::name('qa')->where($map)->where('is_del','0')->where('audit','0')->value('count(id)');
		$count_4=Db::name('qa')->where($map)->where('is_del','0')->where('audit','2')->value('count(id)');   
		$list_tab = [
	        '0' => ['title' => '已发布('.$count_1.')', 'url' => url('index', ['status' => '0'])],
	        '1' => ['title' => '回收站('.$count_2.')', 'url' => url('index', ['status' => '1'])],
	        '2' => ['title' => '未审核('.$count_3.')', 'url' => url('index', ['status' => '2'])],
	        '3' => ['title' => '审核不通过('.$count_4.')', 'url' => url('index', ['status' => '3'])],
	    ];
	    $qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
		$select_qa_class=[];
		foreach ($qa_class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_qa_class[$v['id']]=$title;
		}
	    switch ($status) {
	    	case '0':
	    		$order = $this->getOrder();
		        if($order===''){
		            $order='pubtime desc,id desc';
		        }
				$data_list = Db::name('qa')->where('is_del','0')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('问答列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('qa') // 指定数据表名
		        	->addOrder('id,pubtime,click,laud,tread,follow') // 添加排序
		            ->setSearch(['id','q']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('qa_class_id', $select_qa_class)  // 添加字段筛选
		            ->addFilterMap('qa_class_id', ['is_del'=>0,'audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['q', '问题'],
							//['a', '答案'],
		        			['qa_class_id', '问答分类','select',$select_qa_class],
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
		            $order='pubtime desc,id desc';
		        }
				$data_list = Db::name('qa')->where('is_del','1')->where('audit','1')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('问答列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('qa') // 指定数据表名
		        	->addOrder('id,pubtime,click,laud,tread,follow') // 添加排序
		            ->setSearch(['id','q']) // 设置搜索参数
					->addTimeFilter('pubtime') // 添加时间段筛选
		            ->addFilter('qa_class_id', $select_qa_class)  // 添加字段筛选
		            ->addFilterMap('qa_class_id', ['is_del'=>1,'audit'=>1])//筛选条件
		        	->addColumns([
		        			['id', 'ID'], 
		        			['q', '问题'],
							//['a', '答案'],
		        			['qa_class_id', '问答分类','select',$select_qa_class],
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
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('qa')->where('is_del','0')->where('audit','0')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('问答列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('qa') // 指定数据表名
		        	->addOrder('id,addtime,click') // 添加排序
		            ->setSearch(['id','q']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('qa_class_id', $select_qa_class)  // 添加字段筛选
		            ->addFilterMap('qa_class_id', ['is_del'=>0,'audit'=>0])//筛选条件
		        	->addColumns([
		        			['id', 'ID'],
		        			['q', '问题'],
							//['a', '答案'],
		        			['qa_class_id', '问答分类','select',$select_qa_class],
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
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('qa')->where('is_del','0')->where('audit','2')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	        	return ZBuilder::make('table')
		        	->setPageTitle('问答列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('qa') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id','q']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('qa_class_id', $select_qa_class)  // 添加字段筛选
		            ->addFilterMap('qa_class_id', ['is_del'=>0,'audit'=>2])//筛选条件
		        	->addColumns([
		        			['id', 'ID'],
		        			['q', '问题'],
							//['a', '答案'],
		        			['qa_class_id', '问答分类','select',$select_qa_class],
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
		$qa=Db::name("qa")->where('id',$id)->find();
		if(!$qa){
			return $this->error('请求错误');
		}
		$qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
		$select_qa_class=[];
		foreach ($qa_class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_qa_class[$v['id']]=$title;
		}
		if($qa['is_del']==0 && $qa['audit']==1)	$status='已发布';
		elseif($qa['is_del']==1 && $qa['audit']==1)	$status='已移除至回收站';
		elseif($qa['is_del']==0 && $qa['audit']==0)	$status='未审核';
		elseif($qa['is_del']==0 && $qa['audit']==2)	$status='审核不通过';
		else $status=no_font('未知');
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('查看问答详情') // 设置页面标题
			//->setPageTips('以下是问答的详细容') // 设置页面提示信息
			->hideBtn(['back','submit']) //隐藏默认按钮
			->addStatic('id', 'ID','',$qa['id'])
			->addStatic('q', '问题','',$qa['q'])
			->addStatic('qa_class_id', '问答分类','',issetArrOffset($select_qa_class[$qa['qa_class_id']]))
			->addStatic('adder', '创建者','',staticText($qa['adder'],'admin_username'))
			->addStatic('addtime', '创建时间','',staticText($qa['addtime'],'time'))
			->addStatic('puber', '发布者','',staticText($qa['puber'],'admin_username'))
			->addStatic('pubtime', '发布时间','',staticText($qa['pubtime'],'time'))
			->addStatic('file_ids', '附件','',staticText($qa['file_ids'],'files'))
			->addStatic('click', '阅读量','',$qa['click'])
			->addStatic('status', '状态','',$status)
			->addStatic('a', '答案','',staticText($qa['a']))
			//->isAjax(false) //默认为ajax的post提交
			->fetch();
	}
	public function excelremark(){
		echo "<head><title>问答导入说明</title></head><body><img width='100%' src='".config('public_url')."uploads/exceltpl/qa_remark.png'></body>";
	}
	public function exceltemplet(){
		$header = array(
		  '序号'=>'string',//text
		  '问题'=>'string',//text
		  '问答分类'=>'string',//text
		  '答案'=>'string',//text
		);
		$rows = array(

		);
        include_once("lib/PHP_XLSXWriter-master/xlsxwriter.class.php");
		$writer = new \XLSXWriter();
		$writer->writeSheetHeader('Sheet1', $header);
		foreach($rows as $row)
			$writer->writeSheetRow('Sheet1', $row);

		$file_name = 'qa-tpl.xlsx';     //下载文件名    
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
					$title_templet=['序号','问题','问答分类','答案'];
					$sql = "INSERT ignore INTO `%s`.`%s`(`q`,`qa_class_id`,`a`,`adder`,`addtime`,`audit`,`puber`,`pubtime`) VALUES";
		            $sql = sprintf($sql,config('database.database'),config('database.prefix').'qa'); 
		            $fileds = "('%s','%d','%s','%d','%d','%d','%d','%d')";
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
		                	if(!isset($v[1]) || $v[1]===''){
		                		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'问题为空，插入失败'];
		                    	continue;
		                	}
		                    if(intval($v[2])>0){
		                    	$qa_class=Db::name('qa_class')->find($v[2]);
		                    	if(!$qa_class){
		                    		$result[]=['num'=>$v[1],'status'=>0,'id'=>0,'msg'=>'问答分类不存在，插入失败'];
		                    		continue;
		                    	}
		                    }else{
		                    	$qa_class=Db::name('qa_class')->where('title',$v[2])->find();
		                    	if(!$qa_class){
		                    		$result[]=['num'=>$v[1],'status'=>0,'id'=>0,'msg'=>'问答分类不存在，插入失败'];
		                    		continue;
		                    	}
		                    	$v[2]=$qa_class['id'];
		                    }
		                    if(!isset($v[3]) || $v[3]===''){
		                		$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'答案为空，插入失败'];
		                    	continue;
		                	}
		                    $qa=Db::name('qa')->where(['q'=>$v[1],'qa_class_id'=>$v[2],'a'=>$v[3]])->find();
		                    if($qa){
		                    	$result[]=['num'=>$v[0],'status'=>0,'id'=>0,'msg'=>'此条问答记录已经存在，插入失败'];
		                    	continue;
		                    }
		                    if($post['is_pub']){
		                    	$values = sprintf($fileds,$v[1],$v[2],$v[3],UID,time(),1,UID,time());
		                    }else{
		                    	$values = sprintf($fileds,$v[1],$v[2],$v[3],UID,time(),0,0,0);
		                    }
		                    Db::execute($sql.$values);
		                    $qa_id=Db::name('qa')->getLastInsID();
		                    $result[]=['num'=>$v[0],'status'=>1,'id'=>$qa_id,'msg'=>'插入成功'];
		                }
		            }
		            session('qa_excel',$result);
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
            ->setPageTitle('[问答]导入excel') // 设置页面标题
            ->setPageTips("导入后系统会自动更新问答库数据<br><span style='color:rgb(239,162,49);'>导入时请使用最新模板，并且切勿修改模板文件的第一行标题</span><br><span style='color:#f00000;'>数据量过大的话，导入时间会很长，请耐心等待</span>") // 设置页面提示信息
            //->setUrl('') // 设置表单提交地址
            //->hideBtn(['back']) //隐藏默认按钮
            ->setBtnTitle('submit', '确定') //修改默认按钮标题
            ->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
            ->addFile('excel', '请选择excel文件', '只能选择后缀为xls,xlsx的文件', '', '2048', 'xls,xlsx')
            ->addRadio('is_pub','导入后状态','',['0'=>'未审核','1'=>'已发布'])
            ->isAjax(false) //默认为ajax的post提交
            ->fetch();
	}
	public function excelview(){
		$data_list=session('qa_excel');
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
	public function audit(){
		$audit=input('audit','0');
		$status=input('status','0');
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('qa')->where('id','in',$ids)->update(['audit'=>$audit]);
		if($rt!==false){
			if($audit=='1'){
				Db::name('qa')->where('id','in',$ids)->update(['pubtime'=>time(),'puber'=>UID]);
			}
			return $this->success('审核成功',false,'',1);
        } else {
            return $this->error('审核失败');
        }
	}
	public function del(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('qa')->where('id','in',$ids)->update(['is_del'=>'1']);
		if($rt!==false){
			return $this->success('移除成功',false,'',1);
        } else {
            return $this->error('移除失败');
        }
	}
	public function restore(){
		$ids = (Request::instance()->isGet()) ? input('ids') : input('post.ids/a');
		$rt=Db::name('qa')->where('id','in',$ids)->update(['is_del'=>'0']);
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
			    'q|问题'  => 'require',
			    'qa_class_id|问答分类'  => 'require',
			    'a|答案'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$insert=array();
			$insert['q']=$data['q'];
			$insert['qa_class_id']=$data['qa_class_id'];
			$insert['a']=$data['a'];
			$insert['file_ids']=$data['file_ids'];
			$insert['addtime']=time();
			$insert['adder']=UID;
			//数据入库
			$qa_id=Db::name("qa")->insertGetId($insert);
			//跳转
			if($qa_id>0){
				return $this->success('添加成功',url('index',['status'=>(int)session('jump_status')]),'',1);
	        } else {
	            return $this->error('添加失败');
	        }
		}
	    $qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
		$select_qa_class=[];
		foreach ($qa_class as $k => $v) {
			$title=$v['title'];
			if($v['is_del']) $title.='(已移除)';
			$select_qa_class[$v['id']]=$title;
		}
		// 使用ZBuilder快速创建表单
		return ZBuilder::make('form')
			->setPageTitle('添加问答') // 设置页面标题
			->setPageTips('请认真填写相关信息') // 设置页面提示信息
			//->setUrl('add') // 设置表单提交地址
			//->hideBtn(['back']) //隐藏默认按钮
			->setBtnTitle('submit', '确定') //修改默认按钮标题
			->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
			->addText('q', '问题','','')
			->addSelect('qa_class_id', '问答分类','',$select_qa_class)
    		->addFiles('file_ids', '附件','','')
			->addUeditor('a', '答案','','')
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
			    'q|问题'  => 'require',
			    'qa_class_id|问答分类'  => 'require',
			    'a|答案'=>'require',
			]);
			if (!$validate->check($data)) {
			    return $this->error($validate->getError());
			}
			//数据处理
			$update=array();
			$update['id']=$data['id'];
			$update['qa_class_id']=$data['qa_class_id'];
			$update['q']=$data['q'];
			$update['a']=$data['a'];
			$update['file_ids']=$data['file_ids'];
			//数据更新
			$rt=Db::name("qa")->update($update);
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
			$qa=Db::name("qa")->where('id',$id)->find();
			if(!$qa){
				return $this->error('请求错误');
			}
		    $qa_class=Db::name('qa_class')->order('is_del asc,sort asc,id desc')->select();
			$select_qa_class=[];
			foreach ($qa_class as $k => $v) {
				$title=$v['title'];
				if($v['is_del']) $title.='(已移除)';
				$select_qa_class[$v['id']]=$title;
			}
			// 使用ZBuilder快速创建表单
			return ZBuilder::make('form')
				->setPageTitle('修改问答') // 设置页面标题
				->setPageTips('请认真修改相关信息') // 设置页面提示信息
				//->setUrl('edit') // 设置表单提交地址
				//->hideBtn(['back']) //隐藏默认按钮
				->setBtnTitle('submit', '确定') //修改默认按钮标题
				->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
				->addText('q', '问题','',$qa['q'])
				->addSelect('qa_class_id', '问答分类','',$select_qa_class,$qa['qa_class_id'])
    			->addFiles('file_ids', '附件','',$qa['file_ids'])
				->addUeditor('a', '答案','',$qa['a'])
				->addHidden('id',$qa['id'])
				//->isAjax(false) //默认为ajax的post提交
				->fetch();
		}
	}
}