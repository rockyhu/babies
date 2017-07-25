<?php
namespace Home\Model;
use Think\Model;

class TotalModel extends Model{
	
	/**
	 * 创建会员钱包
	 * @param string $userid 会员id
	 * @return Ambigous
	 */
	public function addUserTotal($userid) {
		$data = array(
			'userid'=>$userid,
			'create'=>NOW_TIME
		);
		if($this->create($data)) {
			$totalid = $this->add();
			return $totalid ? $totalid : 0;
		}else{
			return $this->getError();
		}
	}
	
	/**
	 * 获取会员的电子钱包 - used
	 * @param unknown $userid 用户id
	 * @param string $type 需要获取的电子钱包数据类型，'all'表示获取累计佣金
	 * @return 
	 */
	public function getUserTotal($userid, $type='all') {
		$map['userid'] = $userid;
		switch ($type) {
			case 'all'://'all'表示获取累计佣金
				$oneUserTotal = numberToFloatval($this->field('id,epurse,sharebi,gouwubi,vpsbi,shopbi,fundbi')->where($map)->find());
                return $oneUserTotal;
				break;
			case 'epurse'://余额账户，可以购物，可以提现
				return numberToFloatval($this->where($map)->getField('epurse'));
				break;
            case 'sharebi'://共享积分,可回购至余额
                return numberToFloatval($this->where($map)->getField('sharebi'));
                break;
			case 'gouwubi'://购物积分账户余额，可以用来购物，但不能提现
				return numberToFloatval($this->where($map)->getField('gouwubi'));
				break;
            case 'vpsbi'://增值积分账户余额，不能购物,也不能提现
                return numberToFloatval($this->where($map)->getField('vpsbi'));
                break;
            case 'shopbi'://商家货款,可提现至余额
                return numberToFloatval($this->where($map)->getField('shopbi'));
                break;
			case 'fundbi'://公益账户余额
				return numberToFloatval($this->where($map)->getField('fundbi'));
				break;
		}
	}
	
	
	
}