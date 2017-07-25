<?php
namespace Home\Model;
use Think\Model;

/**
 * 会员在线充值模型
 * @author rockyhu
 *
 */
class UserRechargeModel extends Model{

    /**
     * 添加在线充值支付订单
     * @param $userid
     * @return int|mixed
     */
    public function createUserRecharge($amount, $userid) {
        // 创建支付订单之前先清空之前没有报名成功的
        if ($userid) {
            $map['userid'] = $userid;
            $map['signup_state'] = '待付款';
            $this->where($map)->delete();
        }
        $signupid = $this->add([
            'userid'=>$userid,
            'amount'=>$amount,
            'signup_sn'=>getSignOrderNum($userid),
            'create'=>time()
        ]);
        if($signupid>0) {
            return $this->field('amount,signup_sn,userid')->where("id='{$signupid}'")->find();
        }else {
            return 0;
        }
    }

    /**
     * 更新在线充值的支付状态
     * @param $signup_sn 充值订单编号
     * @param $transaction_id 微信支付订单号
     * @return bool
     */
    public function updateUserRecharge($signup_sn, $transaction_id){
        $map['signup_sn'] = $signup_sn;
        $state = $this->where($map)->setField(array(
            'signup_state'=>'已付款',
            'transaction_id'=>$transaction_id,
            'paytime'=>time()
        ));
        if($state > 0) {
            $rechargeinfo = $this
                ->join(array('a LEFT JOIN __USER__ b ON a.userid=b.id'))
                ->field('a.userid,a.amount,b.openid')->where("a.signup_sn='{$signup_sn}'")->find();
            if($rechargeinfo) {
                M('Total')->where("userid='{$rechargeinfo['userid']}'")->setField(array(
                    'epurse'=>array('exp', 'epurse+'.$rechargeinfo['amount'])
                ));
                //2.充值明细
                D('Purse')->addPurse(array(
                    'jine'=>$rechargeinfo['amount'],
                    'yue'=>M('Total')->where("userid='{$rechargeinfo['userid']}'")->getField('epurse'),
                    'tradeType'=>'微信充值',
                    'type'=>'收入',
                    'kind'=>'钱包余额账户',
                    'info'=>'在线充值',
                    'userid'=>$rechargeinfo['userid'],
                    'create'=>time()
                ));
                // 引入微信模板插件
                vendor('WechatTemplate.WechatTemplate', '', '.class.php');
                $wechatTemplate = new \WechatTemplate();
                $wechatTemplate->sendTemplateMessage(urldecode(json_encode([
                    "touser"=>$rechargeinfo['openid'],
                    "template_id"=>autoGetWXTemplateId('Re001'),
                    "url"=>C('SITE_URL').'User/rechargedetail.html',
                    "topcolor"=>"#7B68EE",
                    "data"=>[
                        "first"=>[
                            "value"=>urlencode("在线充值金额已到您的钱包账户余额，请注意查收。"),
                            "color"=>"#743A3A"
                        ],
                        "money"=>[
                            "value"=>urlencode($rechargeinfo['amount'].'元'),
                            "color"=>"#C4C400"
                        ],
                        "product"=>[
                            "value"=>urlencode("微信钱包充值"),
                            "color"=>"#0000FF"
                        ],
                        "remark"=>[
                            "value"=>urlencode("\\n若您有疑问,请联系官方客服,客服电话为:0755-21002719!"),
                            "color"=>"#008000"
                        ]
                    ]
                ])));
            }
        }
        return $state;
    }
	
	
}