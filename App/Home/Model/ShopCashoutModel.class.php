<?php
namespace Home\Model;
use Think\Model;

/**
 * 商家货款申请提现模型
 * @author rockyhu
 *
 */
class ShopCashoutModel extends Model{
	
	/**
	 * 申请提现
	 * @param string $amount 提现金额
     * @param string $cashoutfee 提现时的手续费
	 * @param string $userid 用户id
	 * @return number
	 */
	public function withdrawdo($amount, $cashoutfee, $shopid, $userid, $time) {
		$data = array(
			'amount'=>$amount,
            'cashoutfee'=>$cashoutfee,
            'shopid'=>$shopid,
			'userid'=>$userid,
			'create'=>$time
		);
		if($this->create($data)) {
			$cashoutid = $this->add();
			return $cashoutid ? $cashoutid : 0;
		}else {
			return $this->getError();
		}
	}

    /**
     * 获取用户提现列表
     * @param $userid 用户id
     * @return mixed
     */
	public function getShopCashoutList($userid, $status = 0) {
        if($status == 0) {
            $list = $this->field('id,amount,cashoutfee,shopid,userid,state,create')->where("userid='{$userid}'")->select();
        }else if($status == 1) {
            $list = $this->field('id,amount,cashoutfee,shopid,userid,state,create')->where("userid='{$userid}' AND state=0")->select();
        }else if($status == 2){
            $list = $this->field('id,amount,cashoutfee,shopid,userid,state,create')->where("userid='{$userid}' AND state=1")->select();
        }
        foreach ($list as $key=>$value) {
			$list[$key]['create'] = date('Y-m-d H:i');
			//状态处理
            switch ($value['state']) {
                case 0://待审核
                    $list[$key]['state'] = '<span class="red">待审核</span>';
                    break;
                case 1://已打款
                    $list[$key]['state'] = '<span class="green">已打款</span>';
                    break;
            }
			//手续费和到账金额处理
			$list[$key]['fee'] = $value['amount']*$value['cashoutfee'];
			$list[$key]['shiji'] = $value['amount'] - $list[$key]['fee'];
		}
		//print_r($list);
		return $list;
	}

    /**
     * 取消没有处理的提现申请
     * @param $cashoutid 提现记录id
     * @param $userid 用户id
     */
	public function cancelCashout($cashoutid, $userid) {
        /**
         * 取消提现申请
         * 1.先获取提现的记录,满足条件,并且是待处理状态;
         * 2.奖金回滚操作,添加回滚的记录等等
         * 3.设置提现记录的状态为已取消(2)
         */
        $oneCashout = $this->field('amount,cashoutfee,create')->where("id='{$cashoutid}' AND state=0 AND userid='{$userid}'")->find();
        if($oneCashout) {
            M('Total')->where("userid='{$userid}'")->setInc('shopbi', $oneCashout['amount']);
            //更新提现的状态 - purse记录里面
            M('Purse')->where("kind='商家货款账户' AND userid='{$userid}' AND `create`='{$oneCashout['create']}'")->setField('beizhu', '已取消');
            return $this->where("id='{$cashoutid}'")->setField(array('state'=>2, 'dotime'=>NOW_TIME));
        }
    }
	
	
}