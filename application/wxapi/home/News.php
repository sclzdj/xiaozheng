<?php
namespace app\wxapi\home;
use think\Db;

class News extends Home
{
    //轮播图
    public function carousel()
    {
        $module=(int)input('module',0);
        if($module>0) $module=1; else $module=0;
        $type=(string)input('type','');
        $limit=input('limit',6);
        $class_id=input('class_id',0);
        $city_id=input('city_id',0);
        if($class_id>0 && $city_id>0){
            if($city_id==1){
                $where_city=['b.sheng'=>0,'b.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_id)->value('pid');
                if($pid==1){
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$city_id} and b.shi=0))";
                }else{
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$pid} and b.shi=0) or b.shi={$city_id})";
                }
            }
            $news=Db::name('carousel a')->join('news b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>0])->where(['b.class_id'=>$class_id,'b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            $policys=Db::name('carousel a')->join('policy b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>1])->where(['b.class_id'=>$class_id,'b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            if($type=='news'){
                $carousels=$news;
            }elseif($type=='policy'){
                $carousels=$policys;
            }else{
                $carousels=array_merge($news,$policys);
            }
            $carousels=list_sort_by($carousels,'sort');
            $carousels=array_slice($carousels, 0,$limit);
        }elseif($class_id>0 && $city_id<=0){
            $news=Db::name('carousel a')->join('news b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>0])->where(['b.class_id'=>$class_id,'b.is_del'=>0])->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            $policys=Db::name('carousel a')->join('policy b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>1])->where(['b.class_id'=>$class_id,'b.is_del'=>0])->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            if($type=='news'){
                $carousels=$news;
            }elseif($type=='policy'){
                $carousels=$policys;
            }else{
                $carousels=array_merge($news,$policys);
            }
            $carousels=list_sort_by($carousels,'sort');
            $carousels=array_slice($carousels, 0,$limit);
        }elseif($city_id>0 && $class_id<=0){
            if($city_id==1){
                $where_city=['b.sheng'=>0,'b.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_id)->value('pid');
                if($pid==1){
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$city_id} and b.shi=0))";
                }else{
                    $where_city="((b.sheng=0 and b.shi=0) or (b.sheng={$pid} and b.shi=0) or b.shi={$city_id})";
                }
            }
            $news=Db::name('carousel a')->join('news b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>0])->where(['b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            $policys=Db::name('carousel a')->join('policy b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>1])->where(['b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            if($type=='news'){
                $carousels=$news;
            }elseif($type=='policy'){
                $carousels=$policys;
            }else{
                $carousels=array_merge($news,$policys);
            }
            $carousels=list_sort_by($carousels,'sort');
            $carousels=array_slice($carousels, 0,$limit);
        }else{
            $news=Db::name('carousel a')->join('news b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>0])->where(['b.is_del'=>0])->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            $policys=Db::name('carousel a')->join('policy b','a.relate_id=b.id','LEFT')->join('class c','b.class_id=c.id','LEFT')->where('a.module',$module)->where(['c.is_del'=>0])->where(['a.is_del'=>0,'a.type'=>1])->where(['b.is_del'=>0])->field('a.id,a.title,a.addtime,a.pic_id,a.type,a.relate_id')->order('a.sort asc,a.addtime desc,a.id desc')->limit($limit)->select();
            if($type=='news'){
                $carousels=$news;
            }elseif($type=='policy'){
                $carousels=$policys;
            }else{
                $carousels=array_merge($news,$policys);
            }
            $carousels=list_sort_by($carousels,'sort');
            $carousels=array_slice($carousels, 0,$limit);
        }
        $carousels=handle_arr_pic($carousels);
        return $this->response($carousels,$this->_successCode,'');
    }
    //最新
    public function blend()
    {
        $limit=input('limit',10);
        $class_id=input('class_id',0);
        $city_id=input('city_id',0);
        if($class_id>0 && $city_id>0){
            if($city_id==1){
                $where_city=['a.sheng'=>0,'a.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_id)->value('pid');
                if($pid==1){
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$city_id} and a.shi=0))";
                }else{
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$pid} and a.shi=0) or a.shi={$city_id})";
                }
            }
            $news=Db::name('news a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'class_id'=>$class_id])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.shi desc,a.sheng desc,a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($news as $k => $v) {
                $news[$k]['type']=0;
            }
            $policys=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'class_id'=>$class_id])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.shi desc,a.sheng desc,a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($policys as $k => $v) {
                $policys[$k]['type']=1;
            }
            $blends=array_merge($news,$policys);
            $blends=list_sort_by($blends,'pubtime','desc');
            $blends=array_slice($blends, 0,$limit);
        }elseif($class_id>0 && $city_id<=0){
            $news=Db::name('news a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'class_id'=>$class_id])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($news as $k => $v) {
                $news[$k]['type']=0;
            }
            $policys=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0,'class_id'=>$class_id])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($policys as $k => $v) {
                $policys[$k]['type']=1;
            }
            $blends=array_merge($news,$policys);
            $blends=list_sort_by($blends,'pubtime','desc');
            $blends=array_slice($blends, 0,$limit);
        }elseif($city_id>0 && $class_id<=0){
            if($city_id==1){
                $where_city=['a.sheng'=>0,'a.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_id)->value('pid');
                if($pid==1){
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$city_id} and a.shi=0))";
                }else{
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$pid} and a.shi=0) or a.shi={$city_id})";
                }
            }
            $news=Db::name('news a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.shi desc,a.sheng desc,a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($news as $k => $v) {
                $news[$k]['type']=0;
            }
            $policys=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.shi desc,a.sheng desc,a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($policys as $k => $v) {
                $policys[$k]['type']=1;
            }
            $blends=array_merge($news,$policys);
            $blends=list_sort_by($blends,'pubtime','desc');
            $blends=array_slice($blends, 0,$limit);
        }else{
            $news=Db::name('news a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($news as $k => $v) {
                $news[$k]['type']=0;
            }
            $policys=Db::name('policy a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_city)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order('a.pubtime desc,a.id desc')->limit($limit)->select();
            foreach ($policys as $k => $v) {
                $policys[$k]['type']=1;
            }
            $blends=array_merge($news,$policys);
            $blends=list_sort_by($blends,'pubtime','desc');
            $blends=array_slice($blends, 0,$limit);
        }
        $blends=handle_arr_pic($blends);
        $blends=handle_arr_city($blends);
        return $this->response($blends,$this->_successCode,'');
    }
	//动态列表
    public function index()
    {
        $class_id=input('class_id',0);
        $city_id=input('city_id',0);
        $keywords=input('keywords','');
    	$page=input('page',1);
    	$pageSize=input('pageSize',10);
    	$offset=$pageSize*($page-1);
        if($class_id>0){
            $where_class=['a.class_id'=>$class_id];
        }else{
            $where_class='';
        }
        if($city_id>0){
            if($city_id==1){
                $where_city=['a.sheng'=>0,'a.shi'=>0];
            }else{
                $pid=Db::name('city')->where('id',$city_id)->value('pid');
                if($pid==1){
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$city_id} and a.shi=0))";
                }else{
                    $where_city="((a.sheng=0 and a.shi=0) or (a.sheng={$pid} and a.shi=0) or a.shi={$city_id})";
                }
            }
            $order="a.shi desc,a.sheng desc,a.pubtime desc,a.id desc";
        }else{
            $where_city='';
            $order="a.pubtime desc,a.id desc";
        }
        if($keywords!==''){
            $where_keywords=['a.title'=>['like','%'.$keywords.'%']];
        }else{
            $where_keywords='';
        }
        $news=Db::name('news a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where($where_city)->where($where_class)->where($where_keywords)->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.click,a.pic_id')->order($order)->limit($offset,$pageSize)->select();
        $news=handle_arr_city($news);
        $news=handle_arr_pic($news);
        return $this->response($news,$this->_successCode,'');
    }
    //详情
    public function content()
    {
    	$this->checkNeedParam(['news_id'=>'请传入news_id']);
    	$news_id=input('news_id');
    	$news=Db::name('news a')->join('class b','a.class_id=b.id','LEFT')->where(['a.audit'=>1,'a.is_del'=>0,'b.is_del'=>0])->where(['a.id'=>$news_id])->field('a.id,a.title,a.pubtime,a.sheng,a.shi,b.title as class_title,a.content,a.click,a.pic_id')->find();
    	if(!$news) $this->response([],201,'动态不存在');
        Db::name('news')->where('id',$news_id)->setInc('click');
        $news['click']+=1;
    	$news=handle_arr_city($news,false);
        $news=handle_arr_pic($news,false);
        $news['content']=handle_view_pic($news['content']);
    	return $this->response($news,$this->_successCode,'');
    }
}
