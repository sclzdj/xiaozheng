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
class Follow extends Command
{
    protected function configure()
    {
        //设置参数
        //$this->addArgument('timed', Argument::REQUIRED);//可选参数


        $this->setName('follow')->setDescription('自动关注');
    }
    protected function execute(Input $input, Output $output)
    {
        //放执行代码
        //$args = $input->getArguments();
        //print_r($args);
        //$timed=(int)$args['timed'];
       
        $now=time();
        $timed_task=Db::name('timed_task')->where('type','follow')->order('time desc,id desc')->limit(1)->find();
        if(!$timed_task){
            $timed_task=['time'=>$now-24*3600];
        }
        set_time_limit(0);
        $users=Db::name('user')->where(['status'=>1,'email'=>['neq','']])->select();
        foreach ($users as $key => $value) {
            $takes=Db::name('take')->where('user_id',$value['id'])->select();
            if($takes){
                $city_ids=[];
                $career_ids=[];
                $ident_ids=[];
                foreach ($takes as $k => $v) {
                    $city_ids[]=$v['city_id'];
                    $career_ids[]=$v['career_id'];
                    $ident_ids[]=$v['ident_id'];
                }
                $details=Db::name('detail a')->join('policy b','a.policy_id=b.id','LEFT')->join('career c','a.career_id=c.id','LEFT')->join('ident d','a.ident_id=d.id','LEFT')->join('category e','a.category_id=e.id','LEFT')->where(['b.city_id'=>['in',$city_ids],'a.career_id'=>['in',$career_ids],'a.ident_id'=>['in',$ident_ids]])->where(['a.is_del'=>0,'a.audit'=>1,'b.is_del'=>0,'b.audit'=>1,'c.is_del'=>0,'d.is_del'=>0,'e.is_del'=>0])->where("a.pubtime>{$timed_task['time']} AND a.pubtime<={$now}")->field('a.*,b.title policy_title,b.city_id,c.title career_title,d.title ident_title,e.title category_title')->select();
                if($details){
                    foreach ($details as $_k => $_v) {
                        $follow=Db::name('follow')->where(['user_id'=>$value['id'],'relate'=>'detail','relate_id'=>$_v['id']])->find();
                        if(!$follow){
                            Db::name('follow')->insertGetId(['user_id'=>$value['id'],'relate'=>'detail','relate_id'=>$_v['id'],'addtime'=>$now]);
                            Db::name('detail')->where('id',$_v['id'])->setInc('follow');
                        }
                    }
                }
            }
        }
        Db::name('timed_task')->insertGetId(['time'=>$now,'type'=>'follow']);
    }
}
