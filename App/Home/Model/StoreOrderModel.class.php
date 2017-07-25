<?php
namespace Home\Model;
use Think\Model;

/**
 * 线下实体店铺订单模型
 * @author rockyhu
 *
 */
class StoreOrderModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array(
	    array('create','time',self::MODEL_INSERT,'function')
	);

    /**
     * 添加线下实体店铺订单
     * @param $shopid 商家id
     * @param $storeid 店铺id
     * @param $price 订单金额
     * @param $gouwubi 购物积分支付
     * @param $epurse 钱包余额支付
     * @param $wxpay 微信支付
     * @param $red 红包支付金额
     * @param $userid 当前会员id
     */
	public function createUserStoreOrder($shopid, $storeid, $price, $gouwubi, $epurse, $wxpay, $red, $userid) {
	    //获取商家店铺信息
        $storeinfo = M('ShopStore')
            ->field('id,name,address,phone,thumb,userid,shopid,genlisid')
            ->where("id='{$storeid}'")->find();
        if($storeinfo) {
            //店铺缩略图处理
            $storeinfo['thumb'] = $storeinfo['thumb'] ? json_decode($storeinfo['thumb'])->source : '';
            //订单数据
            $data = [
                'ordersn' => getSignOrderNum($userid),
                'price' => $price,
                'gouwubi' => $gouwubi,
                'epurse' => $epurse,
                'wxpay' => $wxpay,
                'red' => $red,
                'stores' => serialize($storeinfo),
                'shopid' => $shopid,
                'storeid'=>$storeid,
                'genlisid'=>$storeinfo['genlisid'],
                'userid' => $userid,
                'create' => time()
            ];
            $storeorderid = $this->add($data);
            if($storeorderid>0) {
                return [
                    'orderid'=>$storeorderid,
                    'ordersn'=>$data['ordersn'],
                    'storename'=>$storeinfo['name'],
                    'price'=>$price,
                    'gouwubi' => $gouwubi,
                    'epurse' => $epurse,
                    'wxpay' => $wxpay,
                    'red' => $red
                ];
            }else {
                return 0;
            }
        }
	}

    /**
     * 创建线下实体支付订单并完成支付
     * @param $shopid 商家id
     * @param $storeid 店铺id
     * @param $price 实付金额
     * @param $gouwubi 购物积分支付金额
     * @param $epurse 钱包余额支付金额
     * @param $wxpay 微信支付金额
     * @param $userid 会员id
     */
    public function setShopStorePay($shopid, $storeid, $price, $gouwubi, $epurse, $wxpay, $red, $userid) {
        $storeorderinfo = $this->createUserStoreOrder($shopid, $storeid, $price, $gouwubi, $epurse, $wxpay, $red, $userid);
        if($storeorderinfo) {
            M('Total')->where("userid='{$userid}'")->setField(array(
                'gouwubi'=>array('exp', 'gouwubi-'.$gouwubi),
                'epurse'=>array('exp', 'epurse-'.$epurse)
            ));
            $usertotalinfo = M('Total')->field('epurse,gouwubi')->where("userid='{$userid}'")->find();
            if($gouwubi>0) {
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$gouwubi,
                    'yue'=>$usertotalinfo['gouwubi'],
                    'tradeType'=>'线下消费',
                    'type'=>'支出',
                    'kind'=>'消费积分账户',
                    'info'=>'订单支付('.$storeorderinfo['ordersn'].')',
                    'userid'=>$userid,
                    'create'=>NOW_TIME
                ));
            }
            if($epurse>0) {
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$epurse,
                    'yue'=>$usertotalinfo['epurse'],
                    'tradeType'=>'线下消费',
                    'type'=>'支出',
                    'kind'=>'钱包余额账户',
                    'info'=>'订单支付('.$storeorderinfo['ordersn'].')',
                    'userid'=>$userid,
                    'create'=>NOW_TIME
                ));
            }
            if($red>0) {
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$red,
                    'yue'=>'-',
                    'tradeType'=>'线下消费',
                    'type'=>'支出',
                    'kind'=>'随机红包',
                    'info'=>'订单支付('.$storeorderinfo['ordersn'].')',
                    'userid'=>$userid,
                    'create'=>NOW_TIME
                ));
            }
            //更新线下订单信息
            $this->updateOneStoreOrder($storeorderinfo['ordersn']);
            return 1;
        }else {
            return 0;
        }
    }

    /**
     * 取消支付删除支付订单
     * @param $shopid 商家id
     * @param $storeid 店铺id
     * @param $price 支付金额
     * @param $userid 当前支付的会员id
     */
	public function removeOneStoreOrder($shopid, $storeid, $userid) {
        $this->where("shopid='{$shopid}' AND storeid='{$storeid}' AND userid='{$userid}' AND order_state='待付款'")->delete();
        return 1;
    }

    /**
     * 微信支付
     */
    public function setShopStorePayByWx($ordersn, $transaction_id) {
        $storeorderinfo = $this
            ->field('gouwubi,epurse,wxpay,red,userid,ordersn')
            ->where("ordersn='{$ordersn}'")
            ->find();
        if($storeorderinfo) {
            if($storeorderinfo['gouwubi']+$storeorderinfo['epurse']>0) {
                M('Total')->where("userid='{$storeorderinfo['userid']}'")->setField(array(
                    'gouwubi'=>array('exp', 'gouwubi-'.$storeorderinfo['gouwubi']),
                    'epurse'=>array('exp', 'epurse-'.$storeorderinfo['epurse'])
                ));
            }
            $usertotalinfo = M('Total')->field('epurse,gouwubi')->where("userid='{$storeorderinfo['userid']}'")->find();
            if($storeorderinfo['gouwubi']>0) {
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$storeorderinfo['gouwubi'],
                    'yue'=>$usertotalinfo['gouwubi'],
                    'tradeType'=>'线下消费',
                    'type'=>'支出',
                    'kind'=>'消费积分账户',
                    'info'=>'订单支付('.$storeorderinfo['ordersn'].')',
                    'userid'=>$storeorderinfo['userid'],
                    'create'=>NOW_TIME
                ));
            }
            if($storeorderinfo['epurse']>0) {
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$storeorderinfo['epurse'],
                    'yue'=>$usertotalinfo['epurse'],
                    'tradeType'=>'线下消费',
                    'type'=>'支出',
                    'kind'=>'钱包余额账户',
                    'info'=>'订单支付('.$storeorderinfo['ordersn'].')',
                    'userid'=>$storeorderinfo['userid'],
                    'create'=>NOW_TIME
                ));
            }
            if($storeorderinfo['wxpay']>0) {
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$storeorderinfo['wxpay'],
                    'yue'=>'-',
                    'tradeType'=>'线下消费',
                    'type'=>'支出',
                    'kind'=>'微信钱包账户',
                    'info'=>'订单支付('.$storeorderinfo['ordersn'].')',
                    'userid'=>$storeorderinfo['userid'],
                    'create'=>NOW_TIME
                ));
            }
            if($storeorderinfo['red']>0) {
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$storeorderinfo['red'],
                    'yue'=>'-',
                    'tradeType'=>'线下消费',
                    'type'=>'支出',
                    'kind'=>'随机红包',
                    'info'=>'订单支付('.$storeorderinfo['ordersn'].')',
                    'userid'=>$storeorderinfo['userid'],
                    'create'=>NOW_TIME
                ));
            }
            $this->updateOneStoreOrder($ordersn, $transaction_id);
        }
    }

    /**
     * 更新线下订单状态
     * @param $ordersn 订单编号
     */
    public function updateOneStoreOrder($ordersn, $transaction_id = '') {
        $state = $this->where("ordersn='{$ordersn}'")->setField(array(
            'order_state'=>'已完成',
            'transaction_id'=>$transaction_id,
            'paytype'=>1,
            'paytime'=>time()
        ));
        if($state>0) {
            //更新店铺的平均消费
            $storeinfo = $this
                ->join(array(
                    'a LEFT JOIN __GENLIS__ b ON a.genlisid=b.id',
                    'LEFT JOIN __USER__ e ON a.userid=e.id',
                    'LEFT JOIN __SHOP_STORE__ c ON a.storeid=c.id',
                    'LEFT JOIN __TOTAL__ d ON c.userid=d.userid',
                    'LEFT JOIN __TOTAL__ f ON a.userid=f.userid'
                ))
                ->field('
                    a.genlisid,a.storeid,a.price,a.gouwubi,a.epurse,a.wxpay,a.userid as cuserid,
                    e.nickname,e.realname,e.agentlevel,
                    b.genlisfee,b.shoptotalallreturnfee1,b.shoptotalallreturnfee2,b.shoptotalallreturnfee3,
                    c.userid as suserid,
                    d.shopsales,d.shopvpsbi,d.shopbi,
                    f.spending,f.vpsbi
                    ')
                ->where("a.ordersn='{$ordersn}'")
                ->find();
            //print_r($storeinfo);
            /**
             *
            Array
            (
                [genlisid] => 3
                [storeid] => 4
                [price] => 150.00
                [gouwubi] => 0.00
                [epurse] => 0.00
                [wxpay] => 150.00
                [cuserid] => 2208
                [nickname] => 阿龙
                [realname] =>
                [agentlevel] => 1
                [genlisfee] => 28
                [shoptotalallreturnfee1] => 116
                [shoptotalallreturnfee2] => 136
                [shoptotalallreturnfee3] => 156
                [suserid] => 1189
                [shopsales] => 1065.000
                [shopvpsbi] => 345.920
                [shopbi] => 455.416
                [spending] => 0.000
                [vpsbi] => 0.000
            )
             */
            //exit();
            if($storeinfo) {

                $systeminfo = D('System')->getSystem();

                //商家增值比例
                if($storeinfo['shopsales'] <= 1000000) {
                    $shopvpsfee = $storeinfo['shoptotalallreturnfee1']/100;
                }else if(1000000 < $storeinfo['shopsales'] && $storeinfo['shopsales'] <= 2000000) {
                    $shopvpsfee = $storeinfo['shoptotalallreturnfee2']/100;
                }else if($storeinfo['shopsales'] > 2000000) {
                    $shopvpsfee = $storeinfo['shoptotalallreturnfee3']/100;
                }

                //1.给商家打款及统计商家增值积分
                $storehuokuan = $storeinfo['price']*((100 - $storeinfo['genlisfee'])/100);
                //商家增值积分
                $shopvpsbi = bcadd($storeinfo['price']*($storeinfo['genlisfee']/100)*$shopvpsfee, 0, 3);
                D('Total')->where("userid='{$storeinfo['suserid']}'")->setField(array(
                    'shopbi'=>array('exp', 'shopbi+'.$storehuokuan),
                    'shopsales'=>array('exp', 'shopsales+'.$storeinfo['price']),
                    'shopvpsbi'=>array('exp', 'shopvpsbi+'.$shopvpsbi)
                ));
                //添加商家销售额累计明细
                $storehuokuan>0 && $purseArr[] = [
                    'jine' => $storehuokuan,
                    'yue' => numberFormat('+', $storeinfo['shopbi'], $storehuokuan),
                    'tradeType' => '商家货款',
                    'type' => '收入',
                    'kind' => '商家货款账户',
                    'info' => ($storeinfo['realname'] ? $storeinfo['realname'] : $storeinfo['nickname']).'线下店铺消费('.$ordersn.')',
                    'userid' => $storeinfo['suserid'],
                    'create' => NOW_TIME
                ];
                if($storehuokuan>0) {
                    $phone = M('User')->where("id='{$storeinfo['suserid']}'")->getField('phone');
                    if(isPhone($phone)) {
                        //eg:胡世金于11月16日于您的实体店铺消费5000元,72%的商家货款3600元已到您商家中心的货款账户,请注意查收。
                        $date = date('m月d日 H:i', time());
                        requestManageResponseCode($phone, autoGetTemplateId('huokuan'), array(
                            'realname'=>$storeinfo['nickname'] ? $storeinfo['nickname'] : $storeinfo['realname'],
                            'create'=>$date,
                            'amount'=>$storeinfo['price'],
                            'fee'=>(100 - $storeinfo['genlisfee']).'%',
                            'shiji'=>$storehuokuan
                        ));
                    }
                }
                //添加商家增值积分明细
                $shopvpsbi>0 && $purseArr[] = [
                    'jine' => $shopvpsbi,
                    'yue' => numberFormat('+', $storeinfo['shopvpsbi'], $shopvpsbi),
                    'tradeType' => '积分增值',
                    'type' => '收入',
                    'kind' => '商家增值积分账户',
                    'info' => ($storeinfo['realname'] ? $storeinfo['realname'] : $storeinfo['nickname']).'线下店铺消费('.$ordersn.')',
                    'userid' => $storeinfo['suserid'],
                    'create' => NOW_TIME
                ];
                //添加商家销售额累计明细
                $purseArr[] = [
                    'jine' => $storeinfo['price'],
                    'yue' => numberFormat('+', $storeinfo['shopsales'], $storeinfo['price']),
                    'tradeType' => '商家销售额',
                    'type' => '收入',
                    'kind' => '商家销售额账户',
                    'info' => ($storeinfo['realname'] ? $storeinfo['realname'] : $storeinfo['nickname']).'线下店铺消费('.$ordersn.')',
                    'userid' => $storeinfo['suserid'],
                    'create' => NOW_TIME
                ];

                !empty($purseArr) && M('Purse')->addAll($purseArr);

                //设置均价+销量累加
                $orderlist = $this->field('price')->where("storeid='{$storeinfo['storeid']}'")->select();
                $total = 0;
                foreach ($orderlist as $key=>$value) {
                    $total += $value['price'];
                }
                $spends = round($total/count($orderlist), 2);
                M('ShopStore')->where("id='{$storeinfo['storeid']}'")->setField(array(
                    'spends'=>$spends,
                    'sales'=>array('exp','sales+1')
                ));

                //2.统计消费者的增值积分和消费额
                //获取消费者增值比例
                $agentArr = D('Agent')->getUserAgentList($storeinfo['agentlevel']);

                //增值积分
                $vpsbi = bcadd($storeinfo['price']*$agentArr[$storeinfo['genlisid']], 0, 3);

                D('Total')->where("userid='{$storeinfo['cuserid']}'")->setField(array(
                    'spending'=>array('exp', 'spending+'.$storeinfo['price']),
                    'vpsbi'=>array('exp', 'vpsbi+'.$vpsbi)
                ));
                //添加消费者消费额累计明细
                $cpurseArr[] = [
                    'jine' => $storeinfo['price'],
                    'yue' => numberFormat('+', $storeinfo['spending'], $storeinfo['price']),
                    'tradeType' => '购物消费',
                    'type' => '收入',
                    'kind' => '消费额账户',
                    'info' => ($storeinfo['realname'] ? $storeinfo['realname'] : $storeinfo['nickname']).'线下店铺消费('.$ordersn.')',
                    'userid' => $storeinfo['cuserid'],
                    'create' => NOW_TIME
                ];
                //添加消费者增值积分明细
                $vpsbi>0 && $cpurseArr[] = [
                    'jine' => $vpsbi,
                    'yue' => numberFormat('+', $storeinfo['vpsbi'], $vpsbi),
                    'tradeType' => '积分增值',
                    'type' => '收入',
                    'kind' => '增值积分账户',
                    'info' => ($storeinfo['realname'] ? $storeinfo['realname'] : $storeinfo['nickname']).'线下店铺消费('.$ordersn.')',
                    'userid' => $storeinfo['cuserid'],
                    'create' => NOW_TIME
                ];

                !empty($cpurseArr) && M('Purse')->addAll($cpurseArr);

                //统计消费者的推荐人的分销奖励
                $resellerUseridArr = D('User')->getResellerUseridArr($storeinfo['cuserid']);

                //分销商循环
                $purseArr1 = [];
                foreach ($resellerUseridArr as $key=>$value) {
                    //累计佣金
                    $yongjin = bcadd($storeinfo['price']*$value['level'][$storeinfo['genlisid']]/100, 0, 3);
                    if($yongjin>0) {
                        $epurse = $yongjin*$systeminfo['epursefee'];
                        $gouwubi = $yongjin*$systeminfo['gouwubifee'];
                        //发放佣金
                        M('Total')->where("userid='{$value['userid']}'")->setField(array(
                            'epurse'=>array('exp', 'epurse+'.$epurse),
                            'reseller_yongjin'=>array('exp', 'reseller_yongjin+'.$yongjin),
                            'gouwubi'=>array('exp', 'gouwubi+'.$gouwubi)
                        ));
                        //添加财务记录
                        $purseArr1[] = [
                            'jine' => $yongjin,
                            'yue' => M('Total')->where("userid='{$value['userid']}'")->getField('reseller_yongjin'),
                            'tradeType' => '分销佣金',
                            'type' => '收入',
                            'kind' => '分销佣金账户',
                            'info' => '线下店铺消费('.$ordersn.')<br>'.($value['floor'] == 1 ? '一级分销佣金' : '二级分销佣金'),
                            'userid' => $value['userid'],
                            'create' => NOW_TIME
                        ];
                        $purseArr1[] = [
                            'jine' => $epurse,
                            'yue' => M('Total')->where("userid='{$value['userid']}'")->getField('epurse'),
                            'tradeType' => '现金奖励',
                            'type' => '收入',
                            'kind' => '钱包余额账户',
                            'info' => '线下店铺消费('.$ordersn.')<br>'.($value['floor'] == 1 ? '一级分销佣金' : '二级分销佣金'),
                            'userid' => $value['userid'],
                            'create' => NOW_TIME
                        ];
                        $purseArr1[] = [
                            'jine' => $gouwubi,
                            'yue' => M('Total')->where("userid='{$value['userid']}'")->getField('gouwubi'),
                            'tradeType' => '现金奖励',
                            'type' => '收入',
                            'kind' => '消费积分账户',
                            'info' => '线下店铺消费('.$ordersn.')<br>'.($value['floor'] == 1 ? '一级分销佣金' : '二级分销佣金'),
                            'userid' => $value['userid'],
                            'create' => NOW_TIME
                        ];
                    }
                }
                !empty($purseArr1) && M('Purse')->addAll($purseArr1);

                //统计招商员的奖励
                //获取产品让利等级对应招商员等级商家销售分成比例
                $mergenlisArr = D('Genlis')->getProductMerGenlis();
                //招商员拿推荐商家销售额的百分比
                $mersales = bcadd($storeinfo['price']*$mergenlisArr[$storeinfo['genlisid']]['merchantfee1'], 0, 3);
                $mersalesfenli = bcadd($mersales*$mergenlisArr[$storeinfo['genlisid']]['merchantfee2'], 0, 3);
                if($mersales>0) {
                    $merrefereeinfo = M('User')
                        ->join(array('a LEFT JOIN __USER__ b ON a.shopreferee=b.id'))
                        ->field('a.shopreferee,b.merchantreferee')
                        ->where("a.id='{$storeinfo['suserid']}' AND a.isshop=1")
                        ->find();

                    if ($merrefereeinfo['shopreferee'] > 0) {
                        if($mersales>0) {
                            $merepurse = $mersales *$systeminfo['epursefee'];
                            $mergouwubi = $mersales *$systeminfo['gouwubifee'];
                            M('Total')->where("userid='{$merrefereeinfo['shopreferee']}'")->setField(array(
                                'epurse' => array('exp', 'epurse+' . $merepurse),
                                'merchant_yongjin' => array('exp', 'merchant_yongjin+' . $mersales),
                                'gouwubi' => array('exp', 'gouwubi+' . $mergouwubi)
                            ));
                            //添加财务记录
                            $mersales > 0 && $merpurseArr[] = [
                                'jine' => $mersales,
                                'yue' => M('Total')->where("userid='{$merrefereeinfo['shopreferee']}'")->getField('merchant_yongjin'),
                                'tradeType' => '招商佣金',
                                'type' => '收入',
                                'kind' => '招商佣金账户',
                                'info' => '推荐线下商家销售额分成激励(' . $ordersn . ')',
                                'userid' => $merrefereeinfo['shopreferee'],
                                'create' => NOW_TIME
                            ];
                            //添加财务记录
                            $merepurse > 0 && $merpurseArr[] = [
                                'jine' => $merepurse,
                                'yue' => M('Total')->where("userid='{$merrefereeinfo['shopreferee']}'")->getField('epurse'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '钱包余额账户',
                                'info' => '推荐线下商家销售额分成激励(' . $ordersn . ')',
                                'userid' => $merrefereeinfo['shopreferee'],
                                'create' => NOW_TIME
                            ];
                            $mergouwubi > 0 && $merpurseArr[] = [
                                'jine' => $mergouwubi,
                                'yue' => M('Total')->where("userid='{$merrefereeinfo['shopreferee']}'")->getField('gouwubi'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '消费积分账户',
                                'info' => '推荐线下商家销售额分成激励(' . $ordersn . ')',
                                'userid' => $merrefereeinfo['shopreferee'],
                                'create' => NOW_TIME
                            ];
                        }
                        if ($merrefereeinfo['merchantreferee'] > 0) {
                            if($mersalesfenli>0) {
                                $merfenliepurse = bcadd($mersalesfenli * $systeminfo['epursefee'], 0, 3);
                                $merfenligouwubi = bcadd($mersalesfenli * $systeminfo['gouwubifee'], 0, 3);
                                M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->setField(array(
                                    'epurse' => array('exp', 'epurse+' . $merfenliepurse),
                                    'merchant_yongjin' => array('exp', 'merchant_yongjin+' . $mersalesfenli),
                                    'gouwubi' => array('exp', 'gouwubi+' . $merfenligouwubi)
                                ));
                                //添加财务记录
                                $mersalesfenli>0 && $merpurseArr[] = [
                                    'jine' => $mersalesfenli,
                                    'yue' => M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->getField('merchant_yongjin'),
                                    'tradeType' => '招商佣金',
                                    'type' => '收入',
                                    'kind' => '招商佣金账户',
                                    'info' => '招商员推荐招商员,招商员推荐线下商家销售额分成激励(' . $ordersn . ')',
                                    'userid' => $merrefereeinfo['merchantreferee'],
                                    'create' => NOW_TIME
                                ];
                                $merfenliepurse>0 && $merpurseArr[] = [
                                    'jine' => $merfenliepurse,
                                    'yue' => M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->getField('epurse'),
                                    'tradeType' => '招商佣金',
                                    'type' => '收入',
                                    'kind' => '钱包余额账户',
                                    'info' => '招商员推荐招商员,招商员推荐线下商家销售额分成激励(' . $ordersn . ')',
                                    'userid' => $merrefereeinfo['merchantreferee'],
                                    'create' => NOW_TIME
                                ];
                                $merfenligouwubi>0 && $merpurseArr[] = [
                                    'jine' => $merfenligouwubi,
                                    'yue' => M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->getField('gouwubi'),
                                    'tradeType' => '招商佣金',
                                    'type' => '收入',
                                    'kind' => '消费积分账户',
                                    'info' => '招商员推荐招商员,招商员推荐线下商家销售额分成激励(' . $ordersn . ')',
                                    'userid' => $merrefereeinfo['merchantreferee'],
                                    'create' => NOW_TIME
                                ];
                            }
                        }

                        !empty($merpurseArr) && M('Purse')->addAll($merpurseArr);
                    }
                }
            }
        }
        return $state;
    }

    /**
     * 获取线下实体店的订单列表
     * @param $userid 线下实体店管理员id
     */
    public function getShopUserStoreOrderList($userid) {
        $list = $this
            ->join(array(
                "a LEFT JOIN __SHOP_STORE__ b ON a.storeid=b.id",
                "LEFT JOIN __USER__ c ON a.userid=c.id"
            ))
            ->field('a.price,a.userid,ordersn,a.paytime,a.order_state,c.realname,c.nickname,c.avatar,c.phone')
            ->where("b.userid='{$userid}' AND a.order_state='已完成'")
            ->order('a.paytime DESC')->select();
        foreach ($list as $key=>$value) {
            $list[$key]['avatar'] = $value['avatar'] ? $value['avatar'] : C('TMPL_PARSE_STRING')['__IMG__'].'/photo-mr.jpg';
            $list[$key]['nickname'] = $value['nickname'] ? $value['nickname'] : $value['realname'];
            $list[$key]['paytime'] = date('Y-m-d H:i', $value['paytime']);
        }
        return $list;
    }

    /**
     * 获取会员线下订单列表
     * @param $userid 会员id
     */
    public function getUserStoreOrderList($userid) {
        $list = $this
            ->join(array('a LEFT JOIN __GENLIS__ b ON a.genlisid=b.id'))
            ->field('a.id,a.ordersn,a.price,a.gouwubi,a.epurse,a.wxpay,a.stores,a.order_state,a.paytype,a.shopid,a.storeid,a.genlisid,a.paytime,b.genlisname')
            ->where("userid='{$userid}' AND order_state='已完成'")
            ->order(array('paytime'=>'DESC'))
            ->select();
        foreach ($list as $key=>$value) {
            //支付时间
            $list[$key]['paytime'] = date('Y/m/d H:i:s',$value['paytime']);

            //商品信息
            $stores = unserialize(stripcslashes($value['stores']));
            $stores['thumb'] = $stores['thumb'] ? C('SITE_URL').substr($stores['thumb'], 2) : '';
            $list[$key]['stores'] = $stores;
        }
        return $list;
    }

    /**
     * 5分钟定期清除未支付订单
     */
    public function clearNopayOnlineOrder() {
        $orderlist = $this
            ->field('id,ordersn,create')
            ->where("order_state='待付款' AND paytype=0")
            ->order(array('create'=>'ASC'))
            ->select();
        if($orderlist) {
            $ids = [];
            foreach ($orderlist as $key=>$value) {
                if(time() - $value['create'] >= 5*60) {
                    $ids[] = $value['id'];
                }
            }
            if($ids) {
                $idsstr = implode(',', $ids);
                $map['id'] = array('in', $idsstr);
                $this->where($map)->delete();
            }
        }
    }
	
}