<?php
namespace app\command\home;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
use app\common\controller\Common;
use think\Db;
use app\common\helper\mail as send;
class City extends Command
{
    protected function configure()
    {
        //设置参数
        //$this->addArgument('timed', Argument::REQUIRED);//可选参数


        $this->setName('city')->setDescription('矫正城市');
    }
    protected function execute(Input $input, Output $output)
    {
        //放执行代码
        //$args = $input->getArguments();
        //print_r($args);
        //$timed=(int)$args['timed'];
       
        $now=time();
        $timed_task=Db::name('timed_task')->where('type','city')->order('time desc,id desc')->limit(1)->find();
        set_time_limit(0);
        //矫正城市
        $tables=['news','policy'];
        foreach ($tables as $key => $value) {
            Db::name($value)->where(['sheng'=>0,'shi'=>0])->update(['city_id'=>1]);
            $shengs=Db::name($value)->where(['sheng'=>['neq',0],'shi'=>0])->select();
            foreach ($shengs as $k => $v) {
                Db::name($value)->where('id',$v['id'])->update(['city_id'=>$v['sheng']]);
            }
            $shis=Db::name($value)->where('shi','neq','0')->select();
            foreach ($shis as $k => $v) {
                Db::name($value)->where('id',$v['id'])->update(['sheng'=>(int)Db::name('city')->where('id',$v['shi'])->value('pid')]);
                Db::name($value)->where('id',$v['id'])->update(['city_id'=>$v['shi']]);
            }
        }
        //矫正数据
        $tables=['basis','handle'];
        foreach ($tables as $key => $value) {
            $selects=Db::name($value)->where('type','select')->select();
            foreach ($selects as $k => $v) {
                $options=explode(PHP_EOL,$v['options']);
                $datas=Db::name($value.'_data')->where($value.'_id',$v['id'])->select();
                foreach ($datas as $_k => $_v) {
                    if(!in_array($_v['val'], $options)){
                        Db::name($value.'_data')->where('id',$_v['id'])->update(['val','']);
                    }
                }
            }
            Db::name($value)->where('type','neq','select')->update(['options'=>'']);
        }
        $tables=['detail','qa'];
        foreach ($tables as $key => $value) {
            $follows=Db::query("SELECT relate_id,count(id) AS count from ".config('database.prefix')."follow where relate='{$value}' GROUP BY relate_id");
            foreach ($follows as $k => $v) {
                Db::name($value)->update(['id'=>$v['relate_id'],'follow'=>$v['count']]);
            }
            $lauds=Db::query("SELECT relate_id,count(id) AS count from ".config('database.prefix')."laud_tread where relate='{$value}' AND type=1 GROUP BY relate_id");
            foreach ($lauds as $k => $v) {
                Db::name($value)->update(['id'=>$v['relate_id'],'laud'=>$v['count']]);
            }
            $treads=Db::query("SELECT relate_id,count(id) AS count from ".config('database.prefix')."laud_tread where relate='{$value}' AND type=-1 GROUP BY relate_id");
            foreach ($treads as $k => $v) {
                Db::name($value)->update(['id'=>$v['relate_id'],'tread'=>$v['count']]);
            }
        }
        Db::name('timed_task')->insertGetId(['time'=>$now,'type'=>'city']);
    }
}
