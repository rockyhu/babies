<?php
namespace Home\Model;
use Think\Model;

/**
 * 佣金明细模型
 * @author rockyhu
 *
 */
class UserAwardModel extends Model{
	
	/**
	 * 添加佣金明细记录
	 * @param string $data 佣金数组
	 * @return Ambigous <number, \Think\mixed, boolean, unknown, string>
	 */
	public function addAward($data = array()) {
		if(is_array($data) && !empty($data)) {
			$awardid = $this->add($data);
			return $awardid ? $awardid : 0;
		}
	}

    /**
     * 获取结算周的奖金明细
     * @param $userid 当前登陆用户id
     * @param $weekid 结算周id
     */
	public function getUserAwardList($userid, $weekid) {
	    if(empty($userid)) return;
        if(empty($weekid)) {
            $list = $this
                ->join(array('a LEFT JOIN __USER__ AS b ON a.userid=b.id', 'LEFT JOIN __WEEK_SETTLE__ AS c ON a.weekid=c.weekid'))
                ->field('a.id,a.tuijian,a.zhekou,a.chailv,a.wuliu,a.userid,b.username,b.realname,c.weekid,c.startDate,c.endDate,c.state')
                ->where("a.userid='{$userid}' AND c.state=3")
                ->order(array('a.userid'=>'ASC'))
                ->select();
        }else {
            $list = $this
                ->join(array('a LEFT JOIN __USER__ AS b ON a.userid=b.id', 'LEFT JOIN __WEEK_SETTLE__ AS c ON a.weekid=c.weekid'))
                ->field('a.id,a.tuijian,a.zhekou,a.chailv,a.wuliu,a.userid,b.username,b.realname,c.weekid,c.startDate,c.endDate,c.state')
                ->where("a.userid='{$userid}' AND a.weekid='{$weekid}' AND c.state=3")
                ->order(array('a.userid'=>'ASC'))
                ->select();
        }
        //获取系统参数
        $systeminfo = D('System')->getSystem();
        foreach ($list as $key=>$value) {
            $list[$key]['startDate'] = date('Y/m/d H:i:s', $value['startDate']);
            $list[$key]['endDate'] = date('Y/m/d H:i:s', $value['endDate']);

            //状态处理
            $list[$key]['state'] = '已发工资';

            //格式化数字
            $value = numberToFloatval($value);

            //奖金小计
            $list[$key]['xiaoji'] = $value['xiaoji'] = $value['tuijian']+$value['duipeng']+$value['jiandian']+$value['guanli']+$value['zhekou']+$value['chailv']+$value['dongshi']+$value['wuliu']+$value['secondguanli']+$value['jxsguanli']+$value['quyuguanli']+$value['shoplingshou'];

            //参与扣除复消和个税的奖金小计,物流补助不参与
            $jsxiaoji = $value['tuijian']+$value['duipeng']+$value['jiandian']+$value['guanli']+$value['zhekou']+$value['chailv']+$value['dongshi']+$value['shoplingshou'];
            $list[$key]['koushui'] = $value['koushui'] = $jsxiaoji>$systeminfo['geshui']['tichenggeshui'] ? ($jsxiaoji-$systeminfo['geshui']['tichenggeshui'])*$systeminfo['geshui']['geshuifee']/100 : 0;
            if($jsxiaoji > $systeminfo['gouwu']['tichenggouwu']) {
                $fuxiao = ($jsxiaoji-$systeminfo['gouwu']['tichenggouwu'])*$systeminfo['gouwu']['gouwufee']/100;
                $list[$key]['fuxiao'] = $value['fuxiao'] = $fuxiao>$systeminfo['gouwu']['gouwumax'] ? $systeminfo['gouwu']['gouwumax'] : $fuxiao;
            }else {
                $list[$key]['fuxiao'] = $value['fuxiao'] = 0;
            }
            if($jsxiaoji > $systeminfo['peixun']['tichengpeixun']) {
                $list[$key]['peixun'] = $value['peixun'] = ($jsxiaoji - $systeminfo['peixun']['tichengpeixun'])*$systeminfo['peixun']['peixunfee']/100;
            }else {
                $list[$key]['peixun'] = $value['peixun'] = 0;
            }
            //实发
            $list[$key]['shifa'] = $value['shifa'] = $value['xiaoji'] - $value['koushui'] - $value['fuxiao'] - $value['peixun'];

            //添加链接
            $list[$key]['tuijian'] = $value['tuijian']>0 ? '<a href="'.U("User/userAwardDetail", array("weekid"=>$value['weekid'],"type"=>"tuijian")).'">'.$value['tuijian'].'</a>' : 0;
            $list[$key]['zhekou'] = $value['zhekou']>0 ? '<a href="'.U("User/userAwardDetail", array("weekid"=>$value['weekid'],"type"=>"zhekou")).'">'.$value['zhekou'].'</a>' : 0;
            $list[$key]['chailv'] = $value['chailv']>0 ? '<a href="'.U("User/userAwardDetail", array("weekid"=>$value['weekid'],"type"=>"chailv")).'">'.$value['chailv'].'</a>' : 0;
            $list[$key]['wuliu'] = $value['wuliu']>0 ? '<a href="'.U("User/userAwardDetail", array("weekid"=>$value['weekid'],"type"=>"wuliu")).'">'.$value['wuliu'].'</a>' : 0;
        }
        //print_r($list);
        return $list;
    }
	
}