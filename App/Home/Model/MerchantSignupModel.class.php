<?php
namespace Home\Model;
use Think\Model;

/**
 * 招商员支付订单模型
 * @author rockyhu
 *
 */
class MerchantSignupModel extends Model{

    /**
     * 添加招商员支付订单
     * @param $userid
     * @return int|mixed
     */
    public function addMerchantSignup($merchantphone, $phone, $realname, $wexin, $userid) {
        $refereeid = M('User')->where("phone='{$merchantphone}'")->getField('id');
        //先更新会员资料信息
        M('User')->where("id='{$userid}'")->setField(array(
            'merchantreferee'=>$refereeid,
            'phone'=>$phone,
            'realname'=>$realname,
            'weixin'=>$wexin
        ));
        // 创建支付订单之前先清空之前没有报名成功的
        if ($userid) {
            $map['userid'] = $userid;
            $map['signup_state'] = '待付款';
            $this->where($map)->delete();
        }
        $signup_sn = getSignOrderNum($userid);
        $signupid = $this->add([
            'userid'=>$userid,
            'price'=>'1200',
            'signup_sn'=>$signup_sn,
            'create'=>time()
        ]);
        if($signupid>0) {
            return $this->field('price,signup_sn,userid')->where("id='{$signupid}'")->find();
        }else {
            return 0;
        }
    }

    /**
     * 更新招商员的支付状态,并分配佣金
     * @param $signup_sn 支付订单编号
     * @param $transaction_id 微信支付订单号
     * @return bool
     */
    public function updateMerchantSignup($signup_sn, $transaction_id){
        $map['signup_sn'] = $signup_sn;
        $state = $this->where($map)->setField(array(
            'signup_state'=>'已付款',
            'transaction_id'=>$transaction_id,
            'paytime'=>time()
        ));
        if($state>0) {
            $oneMerchantSignupinfo = $this
                ->join(array('a LEFT JOIN __USER__ b ON a.userid=b.id'))
                ->field('a.price,a.userid,b.realname')
                ->where("a.signup_sn='{$signup_sn}'")
                ->find();
            //1.添加分销商会员信息记录
            $addstate = M('Merchant')->add([
                'level'=>1,//默认为第一个等级
                'userid'=>$oneMerchantSignupinfo['userid'],
                'create'=>time()
            ]);
            //更新会员表中是否是招商员的字段
            M('User')->where("id='{$oneMerchantSignupinfo['userid']}'")->setField('ismerchant', 1);
            if($addstate>0) {
                $systeminfo = D('System')->getSystem();
                //2.及时分配分销佣金
                //获取当前招商员的一级推荐人,二级推荐人,对其进行佣金分配
                $refereeinfo = M('User')
                    ->join(array('a LEFT JOIN __USER__ b ON a.merchantreferee=b.id'))
                    ->field('a.merchantreferee as onereferee,b.merchantreferee as tworeferee')
                    ->where("a.id='{$oneMerchantSignupinfo['userid']}'")
                    ->find();
                if($refereeinfo['onereferee']>0) {
                    //获取第一层推荐人的等级资格分配佣金比例
                    $zigeonefee = M('Merchant')
                        ->join(array('a LEFT JOIN __MERCHANT_LEVEL__ b ON a.level=b.id'))
                        ->where("a.userid='{$refereeinfo['onereferee']}'")
                        ->getField('b.firstfee');
                    if($zigeonefee>0) {
                        $oneZigeYongjin = $zigeonefee * $oneMerchantSignupinfo['price'] / 100;
                        $newoneepurse = $oneZigeYongjin*$systeminfo['epursefee'];
                        $newonegouwubi = $oneZigeYongjin*$systeminfo['gouwubifee'];
                        //第一层推荐人存在
                        M('Total')->where("userid='{$refereeinfo['onereferee']}'")->setField(array(
                            'epurse'=>array('exp', 'epurse+'.$newoneepurse),
                            'merchant_zigebi'=>array('exp', 'merchant_zigebi+'.$oneZigeYongjin),
                            'gouwubi'=>array('exp', 'gouwubi+'.$newonegouwubi)
                        ));
                        //添加财务记录
                        $newPurseArrone = [
                            [
                                'jine' => $oneZigeYongjin,
                                'yue' => M('Total')->where("userid='{$refereeinfo['onereferee']}'")->getField('merchant_zigebi'),
                                'tradeType' => '资格佣金',
                                'type' => '收入',
                                'kind' => '招商资格佣金账户',
                                'info' => '推荐一级招商员:' . $oneMerchantSignupinfo['realname'],
                                'userid' => $refereeinfo['onereferee'],
                                'create' => NOW_TIME
                            ],
                            [
                                'jine' => $newoneepurse,
                                'yue' => M('Total')->where("userid='{$refereeinfo['onereferee']}'")->getField('epurse'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '钱包余额账户',
                                'info' => '推荐一级招商员:' . $oneMerchantSignupinfo['realname'],
                                'userid' => $refereeinfo['onereferee'],
                                'create' => NOW_TIME
                            ],
                            [
                                'jine' => $newonegouwubi,
                                'yue' => M('Total')->where("userid='{$refereeinfo['onereferee']}'")->getField('gouwubi'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '消费积分账户',
                                'info' => '推荐一级招商员:' . $oneMerchantSignupinfo['realname'],
                                'userid' => $refereeinfo['onereferee'],
                                'create' => NOW_TIME
                            ]
                        ];
                        //添加财务记录
                        !empty($newPurseArrone) && M('Purse')->addAll($newPurseArrone);
                        if ($refereeinfo['tworeferee'] > 0) {
                            //第二层推荐人也存在
                            //获取第一层推荐人的等级资格分配佣金比例
                            $zigetwofee = M('Merchant')
                                ->join(array('a LEFT JOIN __MERCHANT_LEVEL__ b ON a.level=b.id'))
                                ->where("a.userid='{$refereeinfo['tworeferee']}'")
                                ->getField('b.secondfee');
                            if($zigetwofee>0) {
                                $twoZigeYongjin = $zigetwofee * $oneMerchantSignupinfo['price'] / 100;
                                $newtwoepurse = $twoZigeYongjin*$systeminfo['epursefee'];
                                $newtwogouwubi = $twoZigeYongjin*$systeminfo['gouwubifee'];
                                //第一层推荐人存在
                                M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->setField(array(
                                    'epurse'=>array('exp', 'epurse+'.$newtwoepurse),
                                    'merchant_zigebi'=>array('exp', 'merchant_zigebi+'.$twoZigeYongjin),
                                    'gouwubi'=>array('exp', 'gouwubi+'.$newtwogouwubi),
                                ));
                                //添加财务记录
                                $newPurseArrtwo = [
                                    [
                                        'jine' => $twoZigeYongjin,
                                        'yue' => M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->getField('merchant_zigebi'),
                                        'tradeType' => '资格佣金',
                                        'type' => '收入',
                                        'kind' => '招商资格佣金账户',
                                        'info' => '推荐二级招商员:' . $oneMerchantSignupinfo['realname'],
                                        'userid' => $refereeinfo['tworeferee'],
                                        'create' => NOW_TIME
                                    ],
                                    [
                                        'jine' => $newtwoepurse,
                                        'yue' => M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->getField('epurse'),
                                        'tradeType' => '现金奖励',
                                        'type' => '收入',
                                        'kind' => '钱包余额账户',
                                        'info' => '推荐二级招商员:' . $oneMerchantSignupinfo['realname'],
                                        'userid' => $refereeinfo['tworeferee'],
                                        'create' => NOW_TIME
                                    ],
                                    [
                                        'jine' => $newtwogouwubi,
                                        'yue' => M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->getField('gouwubi'),
                                        'tradeType' => '现金奖励',
                                        'type' => '收入',
                                        'kind' => '消费积分账户',
                                        'info' => '推荐二级招商员:' . $oneMerchantSignupinfo['realname'],
                                        'userid' => $refereeinfo['tworeferee'],
                                        'create' => NOW_TIME
                                    ],
                                ];
                                //添加财务记录
                                !empty($newPurseArrtwo) && M('Purse')->addAll($newPurseArrtwo);
                            }
                        }
                    }
                }
            }
        }
    }
	
	
}