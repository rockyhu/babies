<?php
namespace Home\Model;
use Think\Model;

/**
 * 申请提现模型
 * @author rockyhu
 *
 */
class CashoutModel extends Model{
	
	/**
	 * 申请提现
	 * @param string $amount 提现金额
     * @param string $cashoutfee 提现时的手续费
	 * @param string $userid 用户id
	 * @return number
	 */
	public function withdrawdo($amount, $cashoutfee, $userid) {
		$data = array(
			'amount'=>$amount,
            'cashoutfee'=>$cashoutfee,
			'userid'=>$userid,
			'create'=>NOW_TIME
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
	public function getUserCashoutList($userid) {
        $list = $this->field('id,amount,cashoutfee,userid,state,create')->where("userid='{$userid}'")->select();
        $Bank = M('Bank');
		foreach ($list as $key=>$value) {
			$list[$key]['create'] = date('Y/m/d H:i:s');
			//状态处理
            switch ($value['state']) {
                case 0://待处理
                    $list[$key]['statetext'] = '<span class="badge bg-red-gradient">待处理</span>';
                    break;
                case 1://已通过
                    $list[$key]['statetext'] = '<span class="badge bg-green-gradient">已通过</span>';
                    break;
                case 2://已取消
                    $list[$key]['statetext'] = '<span class="badge bg-yellow-gradient">已取消</span>';
                    break;
            }
            //银行处理
            //银行卡处理
            $list[$key]['bankinfo'] = $Bank->field('cardno,cardname,bankname,bankaddress')->where("userid='{$userid}'")->find();
			//手续费和到账金额处理
			$list[$key]['fee'] = $value['amount']*$value['cashoutfee'];
			$list[$key]['shiji'] = $value['amount'] - $list[$key]['fee'];
            //删除按钮处理
            if($value['state'] == 0) {
                $list[$key]['del_btn'] = '<a href="javascript:void(0);" class="btn btn-xs btn-primary del-btn" data-id="'.$value['id'].'" title="取消提现申请"><i class="ion-ios-close-outline"></i></a>';
            }
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
            M('Total')->where("userid='{$userid}'")->setInc('epurse', $oneCashout['amount']);
            //更新提现的状态 - purse记录里面
            M('Purse')->where("kind='申请提现' AND userid='{$userid}' AND `create`='{$oneCashout['create']}'")->setField('beizhu', '已取消');
            return $this->where("id='{$cashoutid}'")->setField(array('state'=>2, 'dotime'=>NOW_TIME));
        }
    }
	
	
}