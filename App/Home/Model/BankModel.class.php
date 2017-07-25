<?php
namespace Home\Model;
use Think\Model;

/**
 * 银行卡模型
 * @author rockyhu
 *
 */
class BankModel extends Model{
	
	/**
	 * 绑定银行卡
	 * @param string $cardno 银行卡卡号
	 * @param string $bankname 开户银行
	 * @param string $cardname 户名
	 * @param string $bankaddress 开户行地址
	 * @param string $userid 用户id
	 */
	public function addUserBank($cardno, $bankname, $cardname, $bankaddress, $userid) {
		$data = array(
			'cardno'=>$cardno,
			'bankname'=>$bankname,
			'cardname'=>$cardname,
			'bankaddress'=>$bankaddress,
			'userid'=>$userid,
			'create'=>NOW_TIME
		);
		if($this->create($data)) {
			$bankid = $this->add();
			return $bankid ? $bankid : 0;
		}else {
			return $this->getError();
		}
	}
	
	/**
	 * 获取银行卡信息
	 * @param string $userid 用户id
	 * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function getOneUserBank($userid) {
		$map['userid'] = $userid;
		return $this->field('id,cardno,bankname,cardname,bankaddress')->where($map)->find();
	}
	
	
}