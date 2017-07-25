<?php
namespace Home\Model;
use Think\Model;

class SettleModel extends Model{

    /**
     * 结算当天的相关的共享积分
     */
	public function setDaySettle() {
	    //获取结算日当天的日期时间戳
	    $today = strtotime(date('Y-m-d'));
        $today = strtotime('2017-03-10');
        $date = date('md');
        $settleinfo = $this->field('id,genlis1sales,genlis2sales,genlis3sales')->where("day='{$today}' AND status='待结算'")->find();
        //print_r($settleinfo);//Array ( [genlis1sales] => 50000.00 [genlis2sales] => 100000.00 [genlis3sales] => 150000.00 )

        if($settleinfo) {
            //更新结算状态
            $this->where("id='{$settleinfo['id']}'")->setField(array(
                'status'=>'已结算'
            ));
            //获取产品让利等级激励比例
            $genlisArr = D('Genlis')->getProductGenlisJili();
            //Array ( [user] => Array ( [1] => 0.0214 [2] => 0.0571 [3] => 0.1 ) [shop] => Array ( [1] => 0.0086 [2] => 0.0229 [3] => 0.04 ) )

            //1.消费者激励
            //消费者激励总额
            $userjilitotal = $settleinfo['genlis1sales']*$genlisArr['user'][1] + $settleinfo['genlis2sales']*$genlisArr['user'][2] + $settleinfo['genlis3sales']*$genlisArr['user'][3];
            //echo $userjilitotal;//21780

            $userlist = M('Total')
                ->join(array(
                    'a LEFT JOIN __USER__ b ON a.userid=b.id',
                    'LEFT JOIN __RESELLER__ c ON c.userid=b.referee',
                    'LEFT JOIN __RESELLER_LEVEL__ d ON c.level=d.id'
                ))
                ->field('a.vpsbi,a.sharebi,a.epurse,a.gouwubi,a.userid,b.referee,d.fxallreturnfee')
                ->where("a.vpsbi>0 AND a.sharebi<a.vpsbi")
                ->select();

            $usertotalfenshu = 0;//消费者累计总份数
            $newuserArr = [];//消费者数组
            foreach ($userlist as $key=>$value) {
                //消费者所持份数 = 剩余增值积分/100;
                $userfenshu = floor(numberFormat('-', $value['vpsbi'], $value['sharebi'])/100);
                if($userfenshu>0) {
                    $usertotalfenshu += $userfenshu;
                    $newuserArr[$value['userid']] = [
                        'fenshu'=>$userfenshu,
                        'sharebi'=>$value['sharebi'],
                        'epurse'=>$value['epurse'],
                        'gouwubi'=>$value['gouwubi'],
                        'referee'=>$value['referee'],//消费者推荐人
                        'fxallreturnfee'=>$value['fxallreturnfee']/100//分销商等级对应分销利分利比例
                    ];
                }
            }
            /**
             * 注意:
             * 运行的时候需要将<修改为>
             */
            if($usertotalfenshu>0 && !empty($newuserArr)) {
                //消费者每一份的金额
                //$oneuserfee = $userjilitotal/$usertotalfenshu;
                $allUserLogArray = [];//财务明细数组
                foreach ($newuserArr as $userid=>$value) {
                    $sharefee = bcadd($value['fenshu']*($userjilitotal/$usertotalfenshu), 0, 3);
                    $newepurse = bcadd($sharefee*0.7, 0, 3);
                    $newgouwubi = bcadd($sharefee*0.3, 0, 3);

                    //累计分红到余额
                    M('Total')->where("userid='{$userid}'")->setField(array(
                        'epurse'=>array('exp', 'epurse+'.$newepurse),
                        'gouwubi'=>array('exp', 'gouwubi+'.$newgouwubi),
                        'sharebi'=>array('exp', 'sharebi+'.$sharefee)
                    ));
                    //分红记录数组
                    $allUserLogArray[] = [
                        'jine'=>$sharefee,
                        'yue'=>numberFormat('+', $value['sharebi'], $sharefee),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'共享积分账户',
                        'info'=>'消费者增值'.$date,
                        'userid'=>$userid,
                        'create'=>time()
                    ];
                    $allUserLogArray[] = [
                        'jine'=>$newepurse,
                        'yue'=>numberFormat('+', $value['epurse'], $newepurse),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'钱包余额账户',
                        'info'=>'消费者增值'.$date,
                        'userid'=>$userid,
                        'create'=>time()
                    ];
                    $allUserLogArray[] = [
                        'jine'=>$newgouwubi,
                        'yue'=>numberFormat('+', $value['gouwubi'], $newgouwubi),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'消费积分账户',
                        'info'=>'消费者增值'.$date,
                        'userid'=>$userid,
                        'create'=>time()
                    ];
                    //分销利分利
                    if($value['referee']>0 && $sharefee>0) {
                        //分销利分利金额
                        $fenlifee = bcadd($sharefee*$value['fxallreturnfee'], 0, 3);
                        if($fenlifee>0) {
                            $refereeepurse = bcadd($fenlifee * 0.7, 0, 3);
                            $refereegouwubi = bcadd($fenlifee * 0.3, 0, 3);

                            M('Total')->where("userid='{$value['referee']}'")->setField(array(
                                'epurse' => array('exp', 'epurse+' . $refereeepurse),
                                'gouwubi' => array('exp', 'gouwubi+' . $refereegouwubi)
                            ));
                            $refereeepurse > 0 && $allUserLogArray[] = [
                                'jine' => $refereeepurse,
                                'yue' => numberFormat('+', $value['epurse'], $refereeepurse),
                                'tradeType' => '积分增值',
                                'type' => '收入',
                                'kind' => '钱包余额账户',
                                'info' => '分销商推荐消费者共享分利' . $date,
                                'userid' => $value['referee'],
                                'create' => time()
                            ];
                            $refereegouwubi > 0 && $allUserLogArray[] = [
                                'jine' => $refereegouwubi,
                                'yue' => numberFormat('+', $value['gouwubi'], $refereegouwubi),
                                'tradeType' => '积分增值',
                                'type' => '收入',
                                'kind' => '消费积分账户',
                                'info' => '分销商推荐消费者共享分利' . $date,
                                'userid' => $value['referee'],
                                'create' => time()
                            ];
                        }
                    }
                }
                //添加分红记录
                !empty($allUserLogArray) && M('Purse')->addAll($allUserLogArray);
            }

            //2.商家激励
            //商家激励总额
            $shopjilitotal = $settleinfo['genlis1sales']*$genlisArr['shop'][1] + $settleinfo['genlis2sales']*$genlisArr['shop'][2] + $settleinfo['genlis3sales']*$genlisArr['shop'][3];

            $shoplist = M('Total')
                ->join(array(
                    'a LEFT JOIN __USER__ b ON a.userid=b.id',
                    'LEFT JOIN __MERCHANT__ c ON c.userid=b.shopreferee',
                    'LEFT JOIN __MERCHANT_LEVEL__ d ON c.level=d.id',
                    'LEFT JOIN __TOTAL__ e ON b.shopreferee=e.userid'
                ))
                ->field('a.shopvpsbi,a.shopsharebi,a.epurse,a.gouwubi,a.userid,b.shopreferee,d.zsallreturnfee,e.epurse as merepurse,e.gouwubi as mergouwubi')
                ->where("a.shopvpsbi>0 AND a.shopsharebi<a.shopvpsbi AND b.isshop=1")
                ->select();
            $shoptotalfenshu = 0;//商家累计总份数
            $newshopArr = [];//商家数组
            foreach ($shoplist as $key=>$value) {
                //商家所持份数 = 商家剩余增值积分/100;
                $shopfenshu = floor(numberFormat('-', $value['shopvpsbi'], $value['shopsharebi'])/100);
                if($shopfenshu>0) {
                    $shoptotalfenshu += $shopfenshu;
                    $newshopArr[$value['userid']] = [
                        'fenshu'=>$shopfenshu,
                        'shopsharebi'=>$value['shopsharebi'],
                        'epurse'=>$value['epurse'],
                        'gouwubi'=>$value['gouwubi'],
                        'shopreferee'=>$value['shopreferee'],//商家的招商员
                        'zsallreturnfee'=>$value['zsallreturnfee']/100//招商员等级对应的招商利分利比例
                    ];
                }
            }
            if($shoptotalfenshu<0 && !empty($newshopArr)) {
                $allShopLogArray = [];//财务明细数组
                foreach ($newshopArr as $uid=>$value) {
                    $shareshopfee = bcadd($value['fenshu']*($shopjilitotal/$shoptotalfenshu), 0, 3);
                    $newshopepurse = bcadd($shareshopfee*0.7, 0, 3);
                    $newshopgouwubi = bcadd($shareshopfee*0.3, 0, 3);
                    //累计分红到余额
                    M('Total')->where("userid='{$uid}'")->setField(array(
                        'epurse'=>array('exp', 'epurse+'.$newshopepurse),
                        'gouwubi'=>array('exp', 'gouwubi+'.$newshopgouwubi),
                        'sharebi'=>array('exp', 'sharebi+'.$shareshopfee)
                    ));
                    //分红记录数组
                    $allShopLogArray[] = [
                        'jine'=>$shareshopfee,
                        'yue'=>numberFormat('+', $value['sharebi'], $shareshopfee),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'共享积分账户',
                        'info'=>'商家增值'.$date,
                        'userid'=>$uid,
                        'create'=>time()
                    ];
                    $allShopLogArray[] = [
                        'jine'=>$newshopepurse,
                        'yue'=>numberFormat('+', $value['epurse'], $newshopepurse),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'钱包余额账户',
                        'info'=>'商家增值'.$date,
                        'userid'=>$uid,
                        'create'=>time()
                    ];
                    $allShopLogArray[] = [
                        'jine'=>$newshopgouwubi,
                        'yue'=>numberFormat('+', $value['gouwubi'], $newshopgouwubi),
                        'tradeType'=>'积分增值',
                        'type'=>'收入',
                        'kind'=>'消费积分账户',
                        'info'=>'商家增值'.$date,
                        'userid'=>$uid,
                        'create'=>time()
                    ];

                    //招商员利分利
                    if($value['shopreferee']>0 && $shareshopfee>0) {
                        $mer_fenlifee = bcadd($shareshopfee*$value['zsallreturnfee'], 0, 3);
                        if($mer_fenlifee>0) {
                            $mer_epurse = bcadd($mer_fenlifee * 0.7, 0, 3);
                            $mer_gouwubi = bcadd($mer_fenlifee * 0.3, 0, 3);
                            //累计分红到余额
                            M('Total')->where("userid='{$value['shopreferee']}'")->setField(array(
                                'epurse' => array('exp', 'epurse+' . $mer_epurse),
                                'gouwubi' => array('exp', 'gouwubi+' . $mer_gouwubi)
                            ));
                            $allShopLogArray[] = [
                                'jine' => $mer_epurse,
                                'yue' => numberFormat('+', $value['merepurse'], $mer_epurse),
                                'tradeType' => '积分增值',
                                'type' => '收入',
                                'kind' => '钱包余额账户',
                                'info' => '招商员推荐商家共享分利' . $date,
                                'userid' => $value['shopreferee'],
                                'create' => time()
                            ];
                            $allShopLogArray[] = [
                                'jine' => $mer_gouwubi,
                                'yue' => numberFormat('+', $value['mergouwubi'], $mer_gouwubi),
                                'tradeType' => '积分增值',
                                'type' => '收入',
                                'kind' => '消费积分账户',
                                'info' => '招商员推荐商家共享分利' . $date,
                                'userid' => $value['shopreferee'],
                                'create' => time()
                            ];
                        }
                    }
                }
                //添加分红记录
                !empty($allShopLogArray) && M('Purse')->addAll($allShopLogArray);
            }
        }
    }
	
	
}