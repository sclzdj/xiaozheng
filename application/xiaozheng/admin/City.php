<?php
// +----------------------------------------------------------------------
// | TPPHP框架 [ ruimeng898 ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017   [ http://www.ruimeng898.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://ruimeng898.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\xiaozheng\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\admin\model\Module as ModuleModel;
use app\xiaozheng\model\City as CityModel;
use think\Db;
use think\Request;
use think\Validate;
/**
 * 管理
 * @package app\admin\controller
 */
class City extends Admin
{
    /**
     * 首页
     * @param string $group 分组
     * @author 杜军
     * @return mixed
     */
    public function index()
    {

        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取数据
        $data_list = CityModel::getCitys();

        $max_level = $this->request->get('max', 0);

        $this->assign('citys', $this->getNestCity($data_list, $max_level));

        $this->assign('page_title', get_comment('city').'管理');
        return $this->fetch();
    }

    /**
     * 新增
     * @param string $module 所属模块
     * @param string $pid 所属id
     * @author 杜军
     * @return mixed
     */
    public function add($action='',$pid='')
    {
        //判断是否为post请求
        if (Request::instance()->isPost()) {
            //获取请求的post数据
            $data=input('post.');
            $insert=array();
            if($data['action']=='ch'){
                //数据输入验证
                $validate = new Validate([
                    'pid|所属'.get_comment('city') => 'require',
                    'title|名称' => 'require',
                ]);
                if (!$validate->check($data)) {
                    return $this->error($validate->getError());
                }
                $data['sort']=(int)$data['sort'];
                $insert['pid']=$data['pid'];
            }else{
                //数据输入验证
                $validate = new Validate([
                    'title|名称' => 'require',
                ]);
                if (!$validate->check($data)) {
                    return $this->error($validate->getError());
                }
                $data['sort']=(int)$data['sort'];
                if($data['sheng']==''){
                    $pid=1;
                }
                elseif(!isset($data['shi']) || $data['shi']==0){
                    $pid=$data['sheng'];
                }else{
                    if(!isset($data['qu']) || $data['qu']==0){
                        $pid=$data['shi'];
                    }else{
                        $pid=$data['qu'];
                    }
                } 
                $insert['pid']=$pid;
            }
            //数据处理
            $insert['title']=$data['title'];
            $insert['sort']=$data['sort'];
            //数据入库
            $version_id=Db::name("city")->insertGetId($insert);
            //跳转
            if($version_id>0){
                return $this->success('添加成功','index','',1);
            } else {
                return $this->error('添加失败');
            }
        }
        $citys=CityModel::getCityTree($pid,'',false);
        unset($citys[0]);
        if($action=='ch'){
            // 使用ZBuilder快速创建表单
            return ZBuilder::make('form')
                ->setPageTitle('添加地区') // 设置页面标题
                ->setPageTips('请认真填写相关信息') // 设置页面提示信息
                //->setUrl('add') // 设置表单提交地址
                //->hideBtn(['back']) //隐藏默认按钮
                ->setBtnTitle('submit', '确定') //修改默认按钮标题
                ->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
                ->addText('title', '名称', '必填','')
                ->addFormItems([
                    ['select', 'pid', '所属地区', '所属上级地区', $citys, $pid],
                ])
                ->addText('sort', '排序', '此项须为整数', 100)
                ->addHidden('action',$action)
                //->isAjax(false) //默认为ajax的post提交
                ->fetch();
        }else{
            $list_sheng=Db::name('city')->order('sort asc,id desc')->where('pid',1)->select();
            $select_sheng=[];
            foreach ($list_sheng as $k => $v) {
                $select_sheng[$v['id']]=$v['title'];
            }
            // 使用ZBuilder快速创建表单
            return ZBuilder::make('form')
                ->setPageTitle('添加地区') // 设置页面标题
                ->setPageTips('请认真填写相关信息') // 设置页面提示信息
                //->setUrl('add') // 设置表单提交地址
                //->hideBtn(['back']) //隐藏默认按钮
                ->setBtnTitle('submit', '确定') //修改默认按钮标题
                ->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
                ->addText('title', '名称', '必填','')
                ->addLinkage('sheng', '所属省份', '可不选', $select_sheng, '', url('get_shi'), 'shi,qu')
                ->addLinkage('shi', '所属市区', '可不选', '', '', url('get_qu'), 'qu')
                ->addSelect('qu', '所属地区','可不选','')
                ->addText('sort', '排序', '此项须为整数', 100)
                ->addHidden('action',$action)
                //->isAjax(false) //默认为ajax的post提交
                ->fetch();
        }
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
     // 根据省份获取市区
    public function get_qu($shi = '')
    {
        $citys=Db::name('city')->where('pid',$shi)->order('sort asc,id desc')->select();
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

    /**
     * 编辑
     * @param int $id ID
     * @author 杜军
     * @return mixed
     */
    public function edit($id='')
    {
        //判断是否为post请求
        if (Request::instance()->isPost()) {
            //获取请求的post数据
            $data=input('post.');
            //数据输入验证
            $validate = new Validate([
                'title|名称' => 'require',
            ]);
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            $data['sort']=(int)$data['sort'];
            //数据处理
            $update=array();
            $update['id']=$data['id'];
            $update['title']=$data['title'];
            $update['sort']=$data['sort'];
            //数据更新
            $rt=Db::name("city")->update($update);
            //跳转
            if($rt!==false){
                return $this->success('修改成功','index','',1);
            } else {
                return $this->error('修改失败');
            }
        }
        // 接收id
        if ($id>0) {
            // 查处数据
            $city=Db::name("city")->where('id',$id)->find();
            if(!$city){
                return $this->error('请求错误');
            }
            // 使用ZBuilder快速创建表单
            return ZBuilder::make('form')
                ->setPageTitle('修改地区') // 设置页面标题
                ->setPageTips('请认真修改相关信息') // 设置页面提示信息
                //->setUrl('add') // 设置表单提交地址
                //->hideBtn(['back']) //隐藏默认按钮
                ->setBtnTitle('submit', '确定') //修改默认按钮标题
                ->addBtn('<button type="reset" class="btn btn-default">重置</button>') //添加额外按钮
                ->addText('title', '名称', '必填',$city['title'])
                ->addText('sort', '排序', '此项须为整数',$city['sort'])
                ->addHidden('id',$city['id'])
                //->isAjax(false) //默认为ajax的post提交
                ->fetch();
        }
    }

    /**
     * 删除
     * @param array $record 行为日志内容
     * @author 杜军
     * @return mixed
     */
    public function delete($record = [])
    {
        $id = $this->request->param('id');
        $city = CityModel::where('id', $id)->find();


        // 获取该的所有后辈id
        $city_childs = CityModel::getChildsId($id);

        // 要删除的所有id
        $all_ids = array_merge([(int)$id], $city_childs);

        $tables=['policy','news'];
        foreach ($tables as $key => $value) {
            $data=Db::name($value)->where('city_id','in',$all_ids)->select();
            if($data){
                return $this->error('其他表有关联数据，请先删除关联数据');
                die;
            }
        }
        // 删除
        if (CityModel::destroy($all_ids)) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    /**
     * 保存排序
     * @author 杜军
     * @return mixed
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!empty($data)) {
                $citys = $this->parseCity($data['citys']);
                foreach ($citys as $city) {
                    if ($city['pid'] == 0) {
                        continue;
                    }
                    CityModel::update($city);
                }
                //处理其他表数据
                $tables=['policy','news'];
                foreach ($tables as $key => $table) {
                    $shis=Db::name($table)->value("GROUP_CONCAT(shi)");
                    $shis=explode(',', $shis);
                    $shis=array_unique($shis);
                    foreach ($shis as $k => $v) {
                        Db::name($table)->where('shi',$v)->update(['sheng'=>(int)Db::name('city')->where('id',$v)->value('pid')]);
                    }
                }
                return $this->success('保存'.get_comment('city'));
            } else {
                return $this->error('没有需要保存的'.get_comment('city'));
            }
        }
        return $this->error('非法请求');
    }

    /**
     * 递归解析
     * @param array $citys 数据
     * @param int $pid 上级id
     * @author 杜军
     * @return array 解析成可以写入数据库的格式
     */
    private function parseCity($citys = [], $pid = 0)
    {
        $sort   = 1;
        $result = [];
        foreach ($citys as $city) {
            $result[] = [
                'id'   => (int)$city['id'],
                'pid'  => (int)$pid,
                'sort' => $sort,
            ];
            if (isset($city['children'])) {
                $result = array_merge($result, $this->parseCity($city['children'], $city['id']));
            }
            $sort ++;
        }
        return $result;
    }

    /**
     * 获取嵌套式
     * @param array $lists 原始数组
     * @param int $pid 父级id
     * @param int $max_level 最多返回多少层，0为不限制
     * @param int $curr_level 当前层数
     * @author 杜军
     * @return string
     */
    private function getNestCity($lists = [], $max_level = 0, $pid = 0, $curr_level = 1)
    {
        $result = '';
        foreach ($lists as $key => $value) {
            if ($value['pid'] == $pid) {
                $disable  = $value['status'] == 0 ? 'dd-disable' : '';

                // 组合
                $result .= '<li class="dd-item dd3-item '.$disable.'" data-id="'.$value['id'].'">';
                $result .= '<div class="dd-handle dd3-handle">拖拽</div><div class="dd3-content">'.$value['title'];
                $result .= '<div class="action">';
                if($value['id']!=1){
                    $result .= '<a href="'.url('add', ['action'=>'ch','pid' => $value['id']]).'" data-toggle="tooltip" data-original-title="新增子地区"><i class="list-icon fa fa-plus fa-fw"></i></a><a href="'.url('edit', ['id' => $value['id']]).'" data-toggle="tooltip" data-original-title="编辑"><i class="list-icon fa fa-pencil fa-fw"></i></a>';
                }else{
                    $result .= '<a href="'.url('add', ['action'=>'ch','pid' => $value['id']]).'" data-toggle="tooltip" data-original-title="新增子地区"><i class="list-icon fa fa-plus fa-fw"></i></a>';
                }
                
                if ($value['status'] == 0) {
                    // 启用
                    //$result .= '<a href="javascript:void(0);" data-ids="'.$value['id'].'" class="enable" data-toggle="tooltip" data-original-title="启用"><i class="list-icon fa fa-check-circle-o fa-fw"></i></a>';
                } else {
                    // 禁用
                    //$result .= '<a href="javascript:void(0);" data-ids="'.$value['id'].'" class="disable" data-toggle="tooltip" data-original-title="禁用"><i class="list-icon fa fa-ban fa-fw"></i></a>';
                }
                if($value['id']!=1){
                    $result .= '<a href="'.url('delete', ['id' => $value['id'], 'table' => 'city']).'" data-toggle="tooltip" data-original-title="删除" class="ajax-get confirm"><i class="list-icon fa fa-times fa-fw"></i></a>';
                }
                
                $result .= '</div></div>';

                if ($max_level == 0 || $curr_level != $max_level) {
                    unset($lists[$key]);
                    // 下级
                    $children = $this->getNestCity($lists, $max_level, $value['id'], $curr_level + 1);
                    if ($children != '') {
                        $result .= '<ol class="dd-list">'.$children.'</ol>';
                    }
                }

                $result .= '</li>';
            }
        }
        return $result;
    }

    /**
     * 启用
     * @param array $record 行为日志
     * @author 杜军
     * @return mixed
     */
    public function enable($record = [])
    {
        $id = input('param.ids');
        return $this->setStatus('enable', []);
    }

    /**
     * 禁用
     * @param array $record 行为日志
     * @author 杜军
     * @return mixed
     */
    public function disable($record = [])
    {
        $id = input('param.ids');
        return $this->setStatus('disable', []);
    }
}
