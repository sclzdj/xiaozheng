<?php
namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use think\Validate;
use app\common\builder\ZBuilder;


class Laudtread extends Admin
{
	public function index($status='0'){  
		session('jump_status',$status);
		$map = $this->getMap();
		$count_1=Db::name('laud_tread')->where($map)->value('count(id)');	
		$list_tab = [
	        '0' => ['title' => '赞踩列表('.$count_1.')', 'url' => url('index', ['status' => '0'])],
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
		            $order='addtime desc,id desc';
		        }
				$data_list = Db::name('laud_tread')->where($map)->order($order)->paginate();
				$page = $data_list->render();
	            return ZBuilder::make('table')
		        	->setPageTitle('赞踩列表') // 设置页面标题
		        	->setPageTips('某些操作执行之后可能会导致其他的相关数据失效，所以请谨慎操作') // 设置页面提示信息
		        	->setTabNav($list_tab,  $status)//分组
		        	->setTableName('laud_tread') // 指定数据表名
		        	->addOrder('id,addtime') // 添加排序
		            ->setSearch(['id']) // 设置搜索参数
					->addTimeFilter('addtime') // 添加时间段筛选
		            ->addFilter('relate', ['detail'=>'清单','qa'=>'问答'])  // 添加字段筛选
		            ->addFilter('type', ['-1'=>'踩','1'=>'赞'])  // 添加字段筛选
		            ->addFilter('user_id', $select_users)  // 添加字段筛选
		        	->addColumns([
		        			['id', 'ID'], 
		        			['user_id','会员','callback','user_nickname'],
		        			['relate', '板块','callback','array_v', ['detail'=>'清单','qa'=>'问答']],
		        			['relate_id', '关联','callback',function($relate_id,$data){
		        				if($data['relate']=='清单'){$da='remark';$table='detail';}
		        				if($data['relate']=='问答'){$da='q';$table='qa';}
		        				return "<a style='display:block;width:150px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;' href='".url('xiaozheng/'.$table.'/look',['id'=>$relate_id])."' target='_bank' title='查看'>".Db::name($table)->where('id',$relate_id)->value($da)."</a>";
		        			},'__data__'],
		        			['type', '类型','callback','array_v', ['-1'=>'踩','1'=>'赞']],
		        			['addtime', '点击时间','datetime',no_font('未知')],
		        			['right_button', '操作', 'btn'],
		        		]) //添加多列数据
		        	->addRightButton('delete')
		        	->addTopButton('delete')
		    		->addTopButton('custom',['title'=>'无筛选','href'=>url('index',['status'=>'0']),'icon'=>'fa fa-fw fa-circle-o-notch']) 
		        	->setRowList($data_list) // 设置表格数据
		        	->setPages($page) // 设置分页数据
		        	->fetch();
		        break;
	    }
	}
	public function delete($record = [])
    {
    	$ids   = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $table = input('param.table');
        $ids=(array)$ids;
        foreach ($ids as $k => $v) {
        	$laud_tread=Db::name($table)->find($v);
        	if(!$laud_tread){
        		continue;
        	}
			Db::name($table)->delete($laud_tread['id']);
			if($laud_tread['type']==1){
				Db::name($laud_tread['relate'])->where('id',$laud_tread['relate_id'])->setDec('laud');
			}else{
				Db::name($laud_tread['relate'])->where('id',$laud_tread['relate_id'])->setDec('tread');
			}
        }
        return $this->setStatus('delete', $record);
    }
}