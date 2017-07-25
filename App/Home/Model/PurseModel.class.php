<?php
namespace Home\Model;
use Think\Model;

/**
 * 账户明细模型
 * @author rockyhu
 *
 */
class PurseModel extends Model{
	
	/**
	 * 创建账户明细记录
	 * @param array $data 账户明细数组
	 * @return boolean
	 */
	public function addPurse($data = array()) {
		if(is_array($data) && !empty($data)) {
			$purseid = $this->add($data);
			return $purseid ? $purseid : 0;
		}
	}
	
	/**
	 * 获取用户的账户明细
     * @param string $kind 账户类型
     * @param string $_date 起始日期
	 * @param string $userid 用户id
	 * @param string $limit 获取所有账户明细
	 * @return boolean
	 */
	public function getUserPurse($kind, $_date, $userid) {
	    $map['userid'] = $userid;
        //奖金类型
        switch ($kind) {
            case 1:
                $map['kind'] = '奖金券账户';
                break;
            case 2:
                $map['kind'] = '报单券账户';
                break;
            case 3:
                $map['kind'] = '复消券账户';
                break;
            case 4:
                $map['kind'] = '培训券账户';
                break;
        }
	    if($_date) {
            $dateArr = explode(' - ', $_date);
            $start = strtotime($dateArr[0]);
            $end = strtotime('+1 day', strtotime($dateArr[1]));
            if($start && $end) {
                $map['create'] = array(array('egt', $start),array('elt', $end));
            }
        }
        $purselist = $this->field('id,jine,yue,tradeType,type,kind,info,userid,create')->where($map)->order(array('create'=>'DESC'))->select();
        foreach ($purselist as $key => $value) {
			$purselist[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
            $purselist[$key]['jine'] = numberToFloatval($value['jine']);
            $purselist[$key]['yue'] = numberToFloatval($value['yue']);
            if($value['type'] == '收入') {
                $purselist[$key]['shouru'] = $purselist[$key]['jine'];
                $purselist[$key]['zhichu'] = '-';
            }else if($value['type'] == '支出') {
                $purselist[$key]['shouru'] = '-';
                $purselist[$key]['zhichu'] = $purselist[$key]['jine'];
            }
		}
		return $purselist;
	}

    /**
     * 获取用户转账记录
     * @param $userid 用户id
     * @return mixed 转账记录数组
     */
    public function getUserTransferList($userid) {
        $purselist = $this->field('id,jine,yue,tradeType,type,kind,info,userid,create')->where("userid='{$userid}' AND tradeType='电子币互转'")->order(array('create'=>'DESC'))->select();
        foreach ($purselist as $key => $value) {
            $purselist[$key]['create'] = date('Y/m/d H:i:s', $value['create']);
            $purselist[$key]['jine'] = numberToFloatval($value['jine']);
            $purselist[$key]['yue'] = numberToFloatval($value['yue']);
            if($value['type'] == '收入') {
                $purselist[$key]['shouru'] = $purselist[$key]['jine'];
                $purselist[$key]['zhichu'] = '-';
            }else if($value['type'] == '支出') {
                $purselist[$key]['shouru'] = '-';
                $purselist[$key]['zhichu'] = $purselist[$key]['jine'];
            }
        }
        return $purselist;
    }

    /**
     * 获取用户会员明细
     * @param $userid 会员id
     * @param $type
     */
    public function getUserPurseList($userid, $type = 'all') {
        if($type == 'all') {
            $purselist = $this
                ->field('id,jine,yue,tradeType,type,kind,userid,info,create')
                ->where("userid='{$userid}'")
                ->order(array('create'=>'DESC'))
                ->limit(0, 20)
                ->select();
        }else if($type == 'recharge') {
            $purselist = $this
                ->field('id,jine,yue,tradeType,type,kind,userid,info,create')
                ->where("userid='{$userid}' AND tradeType IN ('现金充值','微信充值')")
                ->order(array('create'=>'DESC'))->select();
        }else if($type == 'cashout') {
            $purselist = $this
                ->field('id,jine,yue,tradeType,type,kind,userid,info,create')
                ->where("userid='{$userid}' AND tradeType IN ('余额提现')")
                ->order(array('create'=>'DESC'))->select();
        }
        $newPurselist = [];//新数组
        $purseDateArr = [];//保存日期数组
        foreach ($purselist as $key => $value) {
            $purselist[$key]['create'] = date('m月d日 H:i', $value['create']);
            $purselist[$key]['jine'] = floatval($value['jine']);
            $purselist[$key]['yue'] = floatval($value['yue']);
            if($value['type'] == '收入') {
                $purselist[$key]['type'] = '<span class="green">收入</span>';
            }else if($value['type'] == '支出') {
                $purselist[$key]['type'] = '<span class="red">支出</span>';
            }
            //日期字符串
            $dateString = date('m月d日', $value['create']);
            if(!inArray($dateString, $purseDateArr)) {
                $purseDateArr[] = $dateString;
                $newPurselist[] = [
                    'date'=>$dateString,
                    'list'=>[$purselist[$key]]
                ];
            }else {
                foreach ($newPurselist as $k=>$v) {
                    if($v['date'] === $dateString) {
                        $newPurselist[$k]['list'][] = $purselist[$key];
                        continue;
                    }
                }
            }
        }
        unset($purselist);
        unset($purseDateArr);
        //print_r($newPurselist);
        return $newPurselist;
    }

    /**
     * 获取指定页码的财务明细
     * @param $userid 当前登陆用户id
     * @param $page 指定页的页码
     * @param int $length 每页指定获取的数量
     */
    public function getPageUserPurseList($userid, $page, $length = 20) {
        $purselist = $this
            ->field('id,jine,yue,tradeType,type,kind,userid,info,create')
            ->where("userid='{$userid}'")
            ->limit(intval($page*$length), intval($length))
            ->order(array('create'=>'DESC'))
            ->select();
        $newPurselist = [];//新数组
        $purseDateArr = [];//保存日期数组
        foreach ($purselist as $key => $value) {
            $purselist[$key]['create'] = date('m月d日 H:i', $value['create']);
            $purselist[$key]['jine'] = floatval($value['jine']);
            $purselist[$key]['yue'] = floatval($value['yue']);
            if($value['type'] == '收入') {
                $purselist[$key]['type'] = '<span class="green">收入</span>';
            }else if($value['type'] == '支出') {
                $purselist[$key]['type'] = '<span class="red">支出</span>';
            }
            //日期字符串
            $dateString = date('m月d日', $value['create']);
            if(!inArray($dateString, $purseDateArr)) {
                $purseDateArr[] = $dateString;
                $newPurselist[] = [
                    'date'=>$dateString,
                    'list'=>[$purselist[$key]]
                ];
            }else {
                foreach ($newPurselist as $k=>$v) {
                    if($v['date'] === $dateString) {
                        $newPurselist[$k]['list'][] = $purselist[$key];
                        continue;
                    }
                }
            }
        }
        unset($purseDateArr);
        return json_encode([
            'purses'=>$newPurselist ? $newPurselist : array(),
            'length'=>count($purselist),
            'pagesize'=>$length
        ]);
    }

    /**
     * 获取用户指定账户类型的明细
     * @param $type 指定类型
     * @param $userid 会员id
     * @return mixed
     */
    public function getUserTypePurseList($type, $userid) {
        $purselist = $this
            ->field('id,jine,yue,tradeType,type,kind,userid,info,create')
            ->where("userid='{$userid}' AND kind='{$type}'")
            ->order(array('create'=>'DESC'))
            ->limit(0, 20)
            ->select();
        $newPurselist = [];//新数组
        $purseDateArr = [];//保存日期数组
        foreach ($purselist as $key => $value) {
            $purselist[$key]['create'] = date('m月d日 H:i', $value['create']);
            $purselist[$key]['jine'] = $value['jine'];
            $purselist[$key]['yue'] = $value['yue'];
            if($value['type'] == '收入') {
                $purselist[$key]['type'] = '<span class="green">收入</span>';
            }else if($value['type'] == '支出') {
                $purselist[$key]['type'] = '<span class="red">支出</span>';
            }
            //日期字符串
            $dateString = date('m月d日', $value['create']);
            if(!inArray($dateString, $purseDateArr)) {
                $purseDateArr[] = $dateString;
                $newPurselist[] = [
                    'date'=>$dateString,
                    'list'=>[$purselist[$key]]
                ];
            }else {
                foreach ($newPurselist as $k=>$v) {
                    if($v['date'] === $dateString) {
                        $newPurselist[$k]['list'][] = $purselist[$key];
                        continue;
                    }
                }
            }
        }
        unset($purselist);
        unset($purseDateArr);
        return $newPurselist;
    }

    /**
     * 获取指定页码的账户明细
     * @param $type 账户类型
     * @param $userid 当前登陆用户id
     * @param $page 指定页的页码
     * @param int $length 每页指定获取的数量
     */
    public function getPageUserTypePurseList($type, $userid, $page, $length = 20) {
        switch ($type) {
            case 1:
                $type = '钱包余额账户';
                break;
            case 2:
                $type = '消费积分账户';
                break;
            case 3:
                $type = '共享积分账户';
                break;
            case 4:
                $type = '增值积分账户';
                break;
            case 5:
                $type = '分销佣金账户';
                break;
            case 6:
                $type = '分销资格佣金账户';
                break;
            case 7:
                $type = '招商佣金账户';
                break;
            case 8:
                $type = '招商资格佣金账户';
                break;
            case 9:
                $type = '商家增值积分账户';
                break;
            case 10:
                $type = '商家共享积分账户';
                break;
        }
        $purselist = $this
            ->field('id,jine,yue,tradeType,type,kind,userid,info,create')
            ->where("userid='{$userid}' AND kind='{$type}'")
            ->limit(intval($page*$length), intval($length))
            ->order(array('create'=>'DESC'))
            ->select();
        $newPurselist = [];//新数组
        $purseDateArr = [];//保存日期数组
        foreach ($purselist as $key => $value) {
            $purselist[$key]['create'] = date('m月d日 H:i', $value['create']);
            $purselist[$key]['jine'] = floatval($value['jine']);
            $purselist[$key]['yue'] = floatval($value['yue']);
            if($value['type'] == '收入') {
                $purselist[$key]['type'] = '<span class="green">收入</span>';
            }else if($value['type'] == '支出') {
                $purselist[$key]['type'] = '<span class="red">支出</span>';
            }
            //日期字符串
            $dateString = date('m月d日', $value['create']);
            if(!inArray($dateString, $purseDateArr)) {
                $purseDateArr[] = $dateString;
                $newPurselist[] = [
                    'date'=>$dateString,
                    'list'=>[$purselist[$key]]
                ];
            }else {
                foreach ($newPurselist as $k=>$v) {
                    if($v['date'] === $dateString) {
                        $newPurselist[$k]['list'][] = $purselist[$key];
                        continue;
                    }
                }
            }
        }
        unset($purseDateArr);
        return json_encode([
            'purses'=>$newPurselist ? $newPurselist : array(),
            'length'=>count($purselist),
            'pagesize'=>$length
        ]);
    }
	
	
	
}