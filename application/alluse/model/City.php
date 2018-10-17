<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\alluse\model;

use think\Model;
use think\Exception;
use util\Tree;

/**
 * 模型
 * @package app\admin\model
 */
class City extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CITY__';

    public static function getCityTree($id = 0, $default = '',$pix=true)
    {
        $result[0]       = '顶级'.get_comment('city');
        $where['status'] = ['egt', 0];

        // 排除指定及其子
        if ($id !== 0) {
            if($pix){
                $hide_ids    = array_merge([$id], self::getChildsId($id));
            }else{
                $hide_ids=self::getChildsId($id);
            }
            $where['id'] = ['notin', $hide_ids];
        }

        // 获取
        $citys = Tree::toList(self::where($where)->order('pid,id')->column('id,pid,title'));
        foreach ($citys as $city) {
            $result[$city['id']] = $city['title_display'];
        }

        // 设置默认项标题
        if ($default != '') {
            $result[0] = $default;
        }

        // 隐藏默认项
        if ($default === false) {
            unset($result[0]);
        }

        return $result;
    }

    /**
     * 获取顶部
     * @param string $max 最多返回多少个
     * @author 杜军
     * @return array
     */
    public static function getTopCity($max = '', $cache_tag = '')
    {

        $map['status'] = 1;
        $map['pid']    = 0;
        $citys = self::where($map)->order('sort,id')->limit($max)->column('id,pid,title');
        return $citys;
    }


    public static function getCitys($fields = true, $map = [])
    {
        return self::order('sort,id')->column($fields, 'id');
    }
    /**
     * 获取所有子id
     * @param int $pid 父级id
     * @author 杜军
     * @return array
     */
    public static function getChildsId($pid = 0)
    {
        $ids = self::where('pid', $pid)->column('id');
        foreach ($ids as $value) {
            $ids = array_merge($ids, self::getChildsId($value));
        }
        return $ids;
    }
}
