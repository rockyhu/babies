<?php
namespace Home\Model;
use Think\Model;

/**
 * 奖金明细模型
 * @author rockyhu
 *
 */
class UserAwardDetailModel extends Model{

	public function getUserAwardDetailList($userid, $weekid, $type) {
        //类型筛选
        switch ($type) {
            case 'tuijian':
                $type = '推荐补贴';
                break;
            case 'zhekou':
                $type = '购物折扣';
                break;
            case 'chailv':
                $type = '差旅补贴';
                break;
            case 'wuliu':
                $type = '物流补助';
                break;
        }
        $list = $this
            ->join(array(
                'a LEFT JOIN __USER__ AS b ON a.userid=b.id',
                'LEFT JOIN __WEEK_SETTLE__ AS c ON a.weekid=c.weekid',
                'LEFT JOIN __USER__ d ON a.sourceuserid=d.id'))
            ->field('a.id,a.jine,a.type,a.sourceuserid,a.info,a.userid,b.username,b.realname,c.startDate,c.endDate,d.username as sourceusername,d.realname as sourcerealname')
            ->where("a.weekid='{$weekid}' AND a.userid='{$userid}' AND a.type='{$type}'")
            ->order(array('a.userid'=>'ASC'))
            ->select();
        foreach ($list as $key=>$value) {
            $list[$key]['startDate'] = date('Y/m/d H:i:s', $value['startDate']);
            $list[$key]['endDate'] = date('Y/m/d H:i:s', $value['endDate']);
        }
        return $list;
    }
	
}