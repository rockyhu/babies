<?php
namespace Home\Model;
use Think\Model;

/**
 * 分销商支付订单模型
 * @author rockyhu
 *
 */
class ResellerSignupModel extends Model{

    /**
     * 添加分销商支付订单
     * @param $userid
     * @return int|mixed
     */
    public function addResellerSignup($phone, $realname, $wexin, $userid) {
        //先更新会员资料信息
        M('User')->where("id='{$userid}'")->setField(array(
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
            'price'=>'5',
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
     * 更新分销商的支付状态,并分配佣金
     * @param $signup_sn 支付订单编号
     * @param $transaction_id 微信支付订单号
     * @return bool
     */
    public function updateResellerSignup($signup_sn, $transaction_id){
        $map['signup_sn'] = $signup_sn;
        $state = $this->where($map)->setField(array(
            'signup_state'=>'已付款',
            'transaction_id'=>$transaction_id,
            'paytime'=>time()
        ));
        if($state>0) {
            $oneResellerSignupinfo = $this->join(array('a LEFT JOIN __USER__ b ON a.userid=b.id'))->field('a.price,a.userid,b.realname')->where("a.signup_sn='{$signup_sn}'")->find();
            //1.添加分销商会员信息记录
            $addstate = M('Reseller')->add([
                'level'=>1,//默认为第一个等级
                'userid'=>$oneResellerSignupinfo['userid'],
                'create'=>time()
            ]);
            //更新会员表中是否是分销商的字段
            M('User')->where("id='{$oneResellerSignupinfo['userid']}'")->setField('isreseller', 1);
            if($addstate>0) {
                $systeminfo = D('System')->getSystem();
                //2.及时分配分销佣金
                //获取当前分销商的一级推荐人,二级推荐人,对其进行佣金分配
                $refereeinfo = M('User')->join(array('a LEFT JOIN __USER__ b ON a.referee=b.id'))->field('a.referee as onereferee,b.referee as tworeferee')->where("a.id='{$oneResellerSignupinfo['userid']}'")->find();
                if($refereeinfo['onereferee']>0) {
                    //获取第一层推荐人的等级资格分配佣金比例
                    $zigeonefee = M('Reseller')->join(array('a LEFT JOIN __RESELLER_LEVEL__ b ON a.level=b.id'))->where("a.userid='{$refereeinfo['onereferee']}'")->getField('b.zigeone');
                    if($zigeonefee>0) {
                        $oneZigeYongjin = $zigeonefee * $oneResellerSignupinfo['price'] / 100;
                        $newoneepurse = $oneZigeYongjin*$systeminfo['epursefee'];
                        $newonegouwubi = $oneZigeYongjin*$systeminfo['gouwubifee'];
                        //第一层推荐人存在
                        M('Total')->where("userid='{$refereeinfo['onereferee']}'")->setField(array(
                            'epurse'=>array('exp', 'epurse+'.$newoneepurse),
                            'reseller_zigebi'=>array('exp', 'reseller_zigebi+'.$oneZigeYongjin),
                            'gouwubi'=>array('exp', 'gouwubi+'.$newonegouwubi),
                        ));
                        //添加财务记录
                        $newPurseArrOne = [
                            [
                                'jine' => $oneZigeYongjin,
                                'yue' => M('Total')->where("userid='{$refereeinfo['onereferee']}'")->getField('reseller_zigebi'),
                                'tradeType' => '资格佣金',
                                'type' => '收入',
                                'kind' => '分销资格佣金账户',
                                'info' => '推荐一级分销商:' . $oneResellerSignupinfo['realname'],
                                'userid' => $refereeinfo['onereferee'],
                                'create' => NOW_TIME
                            ],
                            [
                                'jine' => $newoneepurse,
                                'yue' => M('Total')->where("userid='{$refereeinfo['onereferee']}'")->getField('epurse'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '钱包余额账户',
                                'info' => '推荐一级分销商:' . $oneResellerSignupinfo['realname'],
                                'userid' => $refereeinfo['onereferee'],
                                'create' => NOW_TIME
                            ],
                            [
                                'jine' => $newonegouwubi,
                                'yue' => M('Total')->where("userid='{$refereeinfo['onereferee']}'")->getField('gouwubi'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '消费积分账户',
                                'info' => '推荐一级分销商:' . $oneResellerSignupinfo['realname'],
                                'userid' => $refereeinfo['onereferee'],
                                'create' => NOW_TIME
                            ],
                        ];
                        !empty($newPurseArrOne) && M('Purse')->addAll($newPurseArrOne);
                        if ($refereeinfo['tworeferee'] > 0) {
                            //第二层推荐人也存在
                            //获取第一层推荐人的等级资格分配佣金比例
                            $zigetwofee = M('Reseller')->join(array('a LEFT JOIN __RESELLER_LEVEL__ b ON a.level=b.id'))->where("a.userid='{$refereeinfo['tworeferee']}'")->getField('b.zigetwo');
                            if($zigetwofee>0) {
                                $twoZigeYongjin = $zigetwofee * $oneResellerSignupinfo['price'] / 100;
                                $newtwoepurse = $twoZigeYongjin*$systeminfo['epursefee'];
                                $newtwogouwubi = $twoZigeYongjin*$systeminfo['gouwubifee'];
                                //第二层推荐人也存在
                                M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->setField(array(
                                    'epurse'=>array('exp', 'epurse+'.$newtwoepurse),
                                    'reseller_zigebi'=>array('exp', 'reseller_zigebi+'.$twoZigeYongjin),
                                    'gouwubi'=>array('exp', 'gouwubi+'.$newtwogouwubi),
                                ));
                                //添加财务记录
                                $newPurseArrTwo = [
                                    [
                                        'jine' => $twoZigeYongjin,
                                        'yue' => M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->getField('reseller_zigebi'),
                                        'tradeType' => '资格佣金',
                                        'type' => '收入',
                                        'kind' => '分销资格佣金账户',
                                        'info' => '推荐二级分销商:' . $oneResellerSignupinfo['realname'],
                                        'userid' => $refereeinfo['tworeferee'],
                                        'create' => NOW_TIME
                                    ],
                                    [
                                        'jine' => $newtwoepurse,
                                        'yue' => M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->getField('epurse'),
                                        'tradeType' => '现金奖励',
                                        'type' => '收入',
                                        'kind' => '钱包余额账户',
                                        'info' => '推荐二级分销商:' . $oneResellerSignupinfo['realname'],
                                        'userid' => $refereeinfo['tworeferee'],
                                        'create' => NOW_TIME
                                    ],
                                    [
                                        'jine' => $newtwogouwubi,
                                        'yue' => M('Total')->where("userid='{$refereeinfo['tworeferee']}'")->getField('gouwubi'),
                                        'tradeType' => '现金奖励',
                                        'type' => '收入',
                                        'kind' => '消费积分账户',
                                        'info' => '推荐二级分销商:' . $oneResellerSignupinfo['realname'],
                                        'userid' => $refereeinfo['tworeferee'],
                                        'create' => NOW_TIME
                                    ],
                                ];
                                !empty($newPurseArrTwo) && M('Purse')->addAll($newPurseArrTwo);
                            }
                        }
                    }
                }
            }
        }
    }
	
	
}