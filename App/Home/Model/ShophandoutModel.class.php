<?php
namespace Home\Model;
use Think\Model;

/**
 * 二次购物提成结算记录模型
 * @author rockyhu
 *
 */
class ShophandoutModel extends Model{
    
    private $shopagaincount = 0;//二次购物的第四代之后计数
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();
	
	/**
	 * 1.添加二次购物提成结算记录
	 * @param string $total 二次购物提成红包总金额
	 * @param string $month 月份
	 * @param string $create 结算时间
	 */
	public function addMonthSecondaryShopHandout($month = '') {
	    $data = array(
	        'month'=>!empty($month) ? $month : strtotime(date('Y-m', strtotime('-1 month'))),//上个月的时间戳
	        'create'=>NOW_TIME
	    );
	    return $this->add($data);
	}
	
	/**
	 * 2.检验当月的二次购物提成结算记录是否已经添加，已结算返回false,没结算返回true
	 */
	public function checkMonthSecondaryShopHandout($month = '') {
	    $lastmonth = $month ? $month : strtotime(date('Y-m', strtotime('-1 month')));
	    $count = $this->where("`month`='{$lastmonth}'")->count();
	    return $count>0 ? false : true;
	}
	
	/**
	 * 3.计算上一个月的二次购物提成红包
	 * 步骤：
	 * 1.获取二次购物已付款的订单，然后获取该订单的相关用户信息
	 * 2.根据订单用户获取其推荐人的相关信息，
	 * 3.第四代至第14代都需要消费500元才有资格享受，二次购物红包分红。
	 * {
	 *     ①根据当前会员获取当前会员的推荐人A，不存在退出（第一代）；
	 *     ②根据推荐人A获取推荐人A的推荐人B，不存在退出（第二代）；
	 *     ③根据推荐人B获取推荐人B的推荐人C，不存在退出（第三代）；
	 *     ④根据推荐人C获取4-9代的推荐人，不存在退出（4-9代，最后一个是H）；
	 *     ⑤根据推荐人H获取10-14代推荐人，不存在退出（10-14代）。
	 * }
	 */
	public function setMonthSecondaryShopHandout() {
	    $monthtime = strtotime(date('Y-m', strtotime('-1 month')));//昨天时间戳
	    if(!!$this->checkMonthSecondaryShopHandout($monthtime)) {
	        //1.获取二次购物提成奖励比例
	        $System = D('System');
	        $systemConfig = $System->getSystem();
	        //$systemconfig['shopmallfee']
	        //2.获取已支付的二次购物订单
	        $Order = M('Order');//运行时，应该修改成1，非常重要，下面的条件，求上一个月的月份，测试用这个月
	        $orderlist = $Order->field('id,price,userid')->where("order_state<>'待付款' AND type='二次购物' AND period_diff(date_format(now(),'%Y%m'),FROM_UNIXTIME(paytime,'%Y%m'))=1")->select();
	        $User = D('User');
	        $Total = D('Total');
	        foreach ($orderlist as $key=>$value) {
	            $orderprice = $value['price'];//订单金额
	            $currentUsername = $User->where("id='{$value['userid']}'")->getField('username');
	            //第1-3代结算
	            $User->beforeShopAgain($value['userid'], $orderprice, $currentUsername);
	            //第4-14代结算
	            $fourrefereeuserid = $this->getFourRefereeuserid($value['userid']);
	            if($fourrefereeuserid>0) {
	               $this->SecondaryShopHandoutDoing($orderprice, $fourrefereeuserid, $currentUsername, $systemConfig);
	            }
	        }
	        //5.添加二次购物月结提成记录
	        $this->addMonthSecondaryShopHandout($monthtime);
	        echo 'SecondaryShopHandout added ok~';
	    } else {
	        echo 'SecondaryShopHandout added~';
	    }
	}
	
	/**
	 * 3.计算上一个月的二次购物红包
	 * 步骤：
	 * 1.获取二次购物已付款的订单，然后获取该订单的相关用户信息
	 * 2.根据订单用户获取其推荐人的相关信息，
	 * 3.第四代至第14代都需要消费500元才有资格享受，二次购物红包分红。
	 * {
	 *     ①根据当前会员获取当前会员的推荐人A，不存在退出（第一代）； - 已秒结算
	 *     ②根据推荐人A获取推荐人A的推荐人B，不存在退出（第二代）； - 已秒结算
	 *     ③根据推荐人B获取推荐人B的推荐人C，不存在退出（第三代）； - 已秒结算
	 *     ④根据推荐人C获取4-8代的推荐人，不存在退出（4-8代，最后一个是H）；
	 *     ⑤根据推荐人H获取9-14代推荐人，不存在退出（9-14代）。
	 * }
	 */
	public function setSecondaryShopHandoutforfour() {
	    $monthtime = strtotime(date('Y-m', strtotime('-1 month')));//上一个月的时间戳
	    if(!!$this->checkMonthSecondaryShopHandout($monthtime)) {
	        //1.获取二次购物提成奖励比例
	        $System = D('System');
	        $systemConfig = $System->getSystem();
	        //2.获取已支付的二次购物订单
	        $Order = M('Order');
	        $orderlist = $Order->field('id,price,userid')->where("order_state<>'待付款' AND type='二次购物' AND period_diff(date_format(now(),'%Y%m'),FROM_UNIXTIME(paytime,'%Y%m'))=1")->select();
	        $User = D('User');
	        $Total = D('Total');
	        foreach ($orderlist as $key=>$value) {
	            $orderprice = $value['price'];//订单金额
	            if($orderprice > 0) {
	                $fourrefereeuserid = $this->getFourRefereeuserid($value['userid']);
	                if($fourrefereeuserid>0) {
	                    $shopmallUsername = $User->where("id='{$value['userid']}'")->getField('username');
	                    $this->SecondaryShopHandoutDoing($orderprice, $value['userid'], $shopmallUsername, $systemConfig);
	                }
	            }
	        }
	        //5.添加二次购物月结提成记录
	        $this->addMonthSecondaryShopHandout($monthtime);
	        echo 'SecondaryShopHandout added ok~';
	    } else {
	        echo 'SecondaryShopHandout added~';
	    }
	}
	
	/**
	 * 执行二次购物分红
	 * @param string $orderprice 订单金额
	 * @param string $userid 第四代用户id
	 * @param string $systemConfig 系统配置
	 */
	public function SecondaryShopHandoutDoing($orderprice, $userid, $shopmallUsername, $systemConfig) {
	    $User = M('User');
	    $currentinfo = $User->field('referee')->where("id='{$userid}' AND agentlevel<>0")->find();
	    if(!empty($currentinfo)) {
	        $Total = D('Total');
	        $Purse = D('Purse');
	        $Award = D('Award');
	        $Order = D('Order');
	        
	        //红包比例
	        $hongbaolv = $this->shopagaincount<=3 ? $systemConfig['shopmallfee'][3] : $systemConfig['shopmallfee'][4];
	        
	        //计算当前的二次购物提成
	        $shoppmallprice = $orderprice*$hongbaolv/100;
	        
	        //统计当前用户是否有二次购物消费500元
	        $usershopmallprice = $Order->where("userid='{$userid}' AND order_state<>'待付款' AND type='二次购物' AND period_diff(date_format(now(),'%Y%m'),FROM_UNIXTIME(paytime,'%Y%m'))=1")->sum('price');
	        
	        if($shoppmallprice>0 && $usershopmallprice>=500) {
	            //层奖秒结算
	            //钱包累加，total,epurse
	            $shouyitotal = $Total->getUserTotal($userid, 'total');
	            $epurse = $Total->getUserTotal($userid, 'epurse');
	             
	            //先扣除购物积分
	            $shopping = $shoppmallprice*$systemConfig['gouwufee']/100;
	            $newepurse = numberFormat('-', $shoppmallprice, $shopping);
	            //当总收益超过$systemConfig['yuanzhu']['shouyitotal']时，奖金中扣除$systemConfig['yuanzhu']['yuanzhufee']/100作为援助资金
	            $yuanzhu = 0;
	            if($shouyitotal > $systemConfig['yuanzhu']['shouyitotal']) {
	                $yuanzhu = $newepurse*$systemConfig['yuanzhu']['yuanzhufee']/100;
	                $newepurse = numberFormat('-', $newepurse, $yuanzhu);
	                $Total->where("userid='{$userid}'")->setInc('yuanzhu', $yuanzhu);
	            }
	            //更新
	            $Total->where("userid='{$userid}'")->setInc('epurse', $newepurse);
	            $Total->where("userid='{$userid}'")->setInc('total', $shoppmallprice);
	            $Total->where("userid='{$userid}'")->setInc('shopping', $shopping);
	            //2.明细表
	            $Purse->addPurse(array(
	                'jine'=>$newepurse,
	                'yue'=>$epurse,
	                'kind'=>'佣金',
	                'beizhu'=>'二次购物红包',
	                'userid'=>$userid,
	                'create'=>NOW_TIME
	            ));
	            //3.奖励记录表
	            $Award->addAward(array(
	                'xiaofei'=>$shoppmallprice,
	                'shopping'=>$shopping,
	                'yuanzhu'=>$yuanzhu,
	                'userid'=>$userid,
	                'text'=>$shopmallUsername.'二次消费'.$orderprice.'元',
	                'create'=>NOW_TIME
	            ));
	            //累加
	            $this->shopagaincount++;
	        }
	        //递归调用，共14-3=11次,0,1,2,3,4,5,6,7,8,9,10
	        if($this->shopagaincount<11) {
	            $this->SecondaryShopHandoutDoing($orderprice, $currentinfo['referee'], $shopmallUsername, $systemConfig);
	        }else {
	            $this->shopagaincount = 0;//清空二次购物计数
	        }
	    }
	}
	
	/**
	 * 根据当前二次消费的用户id获取其第四代推荐人的用户id
	 * @param string $userid 用户id
	 */
	public function getFourRefereeuserid($userid) {
	    if(empty($userid)) return;
	    $User = M('User');
	    $one_refereeuserid = $User->where("id='{$userid}' AND agentlevel<>0")->getField('referee');
	    if($one_refereeuserid>0) {
	        $two_refereeuserid = $User->where("id='{$one_refereeuserid}' AND agentlevel<>0")->getField('referee');
	        if($two_refereeuserid>0) {
	            $three_refereeuserid = $User->where("id='{$two_refereeuserid}' AND agentlevel<>0")->getField('referee');
	        }
	    }
	    return $three_refereeuserid>0 ? $three_refereeuserid : 0;
	}
	
}