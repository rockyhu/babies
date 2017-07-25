<?php
namespace Home\Model;
use Think\Model;

/**
 * 订单模型
 * @author rockyhu
 *
 */
class OrderModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array(
	    array('create','time',self::MODEL_INSERT,'function')
	);
	
	/**
	 * 获取个人订单列表
	 * @param string $userid 用户id
	 */
	public function getUserOrderList($order_state, $userid) {
	    $map['userid'] = $userid;
        switch ($order_state) {
            case 1:
                $map['order_state'] = '待付款';
                break;
            case 2:
                $map['order_state'] = '待发货';
                break;
            case 3:
                $map['order_state'] = '待收货';
                break;
            case 4:
                $map['order_state'] = '待退款';
                break;
            case 5:
                $map['order_state'] = '已完成';
                break;
            case 6:
                $map['order_state'] = '已关闭';
                break;
            default:
                $map['order_state'] = array('neq', '已关闭');
                break;
        }
		$list = $this
            ->field('id,ordersn_general,order_state,expresssn,iscomment,products,price,dispatchprice,userid')
            ->where($map)
            ->order(array('create'=>'DESC'))
            ->select();
        $neworderlist = [];//保存输出订单信息
        //获取所有的产品让利等级列表
        $genlisArr = D('Genlis')->getProductGenlisNamelist();
        foreach ($list as $key=>$value) {
            $products = unserialize(stripcslashes($value['products']));
            $flag = true;//是否已经存在总订单的标记,true表示不存在,false表示存在
            foreach ($neworderlist as $k=>$v) {
                if($value['ordersn_general'] == $v['ordersn_general']) {
                    $flag = false;
                    //说明该总订单已经存在,那么只需要将$value订单信息的商品添加到总订单商品数组中去即可,同时更新总订单的实付款金额
                    foreach ($products as $k1=>$v1) {
                        $neworderlist[$k]['num'] += $v1['num'];
                        //$v1['thumb'] = $v1['thumb'] ? C('SITE_URL').substr($v1['thumb'],1) : '';
                        if($v1['thumb'] && strpos($v1['thumb'], 'http://') === false) {
                            $v1['thumb'] = C('SITE_URL').substr($v1['thumb'],1);
                        }
                        $v1['genlisname'] = $genlisArr[$v1['genlisid']];
                        $neworderlist[$k]['products'][] = $v1;
                    }
                    $neworderlist[$k]['total'] += $value['price']+$value['dispatchprice'];
                }
            }
            //第一次
            if($flag) {
                $num = 0;
                foreach ($products as $k1=>$v1) {
                    $num += $v1['num'];
                    if($v1['thumb'] && strpos($v1['thumb'], 'http://') === false) {
                        $products[$k1]['thumb'] = C('SITE_URL').substr($v1['thumb'],1);
                    }
                    $products[$k1]['genlisname'] = $genlisArr[$v1['genlisid']];
                }
                $neworderlist[] = [
                    'id'=>$value['id'],
                    'order_state'=>$value['order_state'],
                    'iscomment'=>$value['iscomment'],
                    'ordersn_general'=>$value['ordersn_general'],
                    'products'=>$products,//需要处理
                    'expresssn'=>$value['expresssn'],
                    'num'=>$num,//需要处理
                    'total'=>$value['price']+$value['dispatchprice'],
                    'detailurl'=>U('Order/detail',array('ordersn_general'=>$value['ordersn_general'])),
                    'payurl'=>C('SITE_URL').'?c=Payment&a=orderpay&ordersn_general='.$value['ordersn_general']//订单付款地址
                ];
            }
		}
		return $neworderlist;
	}

    /**
     * 创建用户订单
     * @param array $ids 购物车id数组或产品id数字字符串
     * @param array $frominfoArr 备注数组
     * @param $addressid 收货地址id
     * @param $userid 会员id
     */
	public function createUserOrder($ids, $shopidArr = [], $frominfoArr, $addressid, $userid) {
	    //收货地址信息获取
        $addressinfo = D('Address')->getOneUserAddress($addressid);
        if(is_array($ids)) {//购物车创建订单
            $cartidsArr = $ids;
            //购物车商品
            $cartids = implode(',', $cartidsArr);
            $cartlist = M('Cart')
                ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
                ->field('a.id,a.products,a.price,a.productid,a.shopid,a.userid,b.shopname,b.ispinkage,b.totalprice')
                ->where("a.id IN ($cartids) AND a.userid='{$userid}'")
                ->order(array('a.create' => 'ASC'))
                ->select();
            if ($cartlist) {
                $total = 0;
                $orderlist = [];//商家订单数组
                foreach ($cartlist as $key => $value) {
                    $flag = true;
                    //为商家添加商品信息
                    $products = unserialize(stripcslashes($value['products']));
                    $total += $value['price'] + $products['dispatchprice'];
                    foreach ($orderlist as $k1 => $v1) {
                        if ($value['shopid'] == $v1['shopid']) {
                            $flag = false;
                            $orderlist[$k1]['products'][] = $products;
                            //累加邮费
                            $orderlist[$k1]['dispatchprice'] += $products['dispatchprice'];
                            //累加总价格
                            $orderlist[$k1]['price'] += $value['price'];
                            //累加商品数量
                            $orderlist[$k1]['num'] += $products['num'];
                            continue;
                        }
                    }
                    if ($flag) {
                        $value['products'] = $products;
                        $orderlist[] = [
                            'shopid' => $value['shopid'],
                            'shopname' => $value['shopname'],
                            'products' => [$value['products']],
                            'dispatchprice' => $products['dispatchprice'],
                            'num' => $products['num'],
                            'price' => $value['price'],
                            'ispinkage'=>$value['ispinkage'],
                            'totalprice'=>$value['totalprice']
                        ];
                    }
                }
            }
            $newOrderArray = [];
            $ordertime = time();
            $ordertimeArr = [];//保存订单创建时间
            $ordersn_general = getOrderNum($userid);
            foreach ($orderlist as $key => $value) {
                $newOrderArray[] = [
                    'ordersn' => getOrderNum($userid),
                    'ordersn_general' => $ordersn_general,
                    'price' => $value['price'],
                    'dispatchprice' => ($value['ispinkage'] && $value['price']>=$value['totalprice']) ? 0 : $value['dispatchprice'],
                    'products' => serialize($value['products']),
                    'address' => serialize($addressinfo),
                    'shopid' => $value['shopid'],
                    'userid' => $userid,
                    'frominfo' => $frominfoArr[$key],
                    'create' => $ordertime + $key * 60
                ];
                $ordertimeArr[] = $ordertime + $key * 60;
            }
            $flag = !empty($newOrderArray) && $this->addAll($newOrderArray);
            if ($flag) {
                //清空指定会员的购物车
                D('Cart')->deleteCart($cartids, $userid);
                //返回订单id组合
                $ordertimes = implode(',', $ordertimeArr);
                $orderlist = $this->field('id')->where("userid='{$userid}' AND `create` IN({$ordertimes})")->select();
                $orderidsArr = [];
                foreach ($orderlist as $k => $v) {
                    $orderidsArr[] = $v['id'];
                }
                return implode('_', $orderidsArr);
            }
        }else {//产品直接创建
            //获取产品信息
            $productinfo = M('Product')
                ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
                ->field('a.id,a.name,a.thumb,a.marketprice,a.productprice,a.genlisid,a.isreturntwo,a.dispatchprice,a.shopid,a.total,a.maxbuy,a.nid,b.ispinkage,b.totalprice')
                ->where("a.id='{$ids}'")->find();
            if($productinfo) {
                $productinfo['num'] = 1;
                $productinfo['thumb'] = $productinfo['thumb'] ? json_decode($productinfo['thumb'])->source : '';
                $ordersn_general = getOrderNum($userid);
                $orderid = $this->add([
                    'ordersn' => $ordersn_general,
                    'ordersn_general' => $ordersn_general,
                    'price' => $productinfo['marketprice'],
                    'dispatchprice' => ($productinfo['ispinkage'] && $productinfo['marketprice']>=$productinfo['totalprice']) ? 0 : $productinfo['dispatchprice'],
                    'products' => serialize([$productinfo]),
                    'address' => serialize($addressinfo),
                    'shopid' => $productinfo['shopid'],
                    'userid' => $userid,
                    'frominfo' => $frominfoArr,
                    'ishexiao' => $productinfo['nid'] == 231 ? 1 : 0,//标注订单是否是核销订单,如果所在的栏目为核销专属(231)那么就设置为核销
                    'create' => time()
                ]);
                return $orderid;
            }
        }
        return false;
	}

    /**
     * 获取待支付订单id集合
     * @param $ids 订单id集合
     * @param $userid 会员id
     */
	public function getOrderPayinfoWithId($ids, $userid) {
	    if(empty($ids)) return ;
        $idsStr = $ids;
        $ids = str_replace('_',',', $ids);
	    $orderpaylist = $this
            ->field('id,ordersn_general,products,price,dispatchprice')
            ->where("userid = '{$userid}' AND id IN ($ids)")
            ->order(array('create'=>'ASC'))
            ->select();
        $ordersn_general = '';
        $totalprice = 0;
        foreach ($orderpaylist as $key=>$value) {
            if($ordersn_general == '') $ordersn_general = $value['ordersn_general'];
            $totalprice += ($value['price']+$value['dispatchprice']);
        }
        //获取会员账户信息
        $usertotalinfo = D('Total')->getUserTotal($userid);
        if($totalprice<=$usertotalinfo['gouwubi']) {
            //消费积分支付即可
            $payinfo = '<div class="order_main clearfix">'.
                '<div class="line"><div class="label">消费积分余额支付</div>'.
                '<div class="info" style="border-bottom:none;">'.
                '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$totalprice.'元</div></div>'.
                '</div>'.
                '</div>'.
                '</div>';
            $payway = 'jfpay';
        }elseif ($totalprice<=($usertotalinfo['gouwubi']+$usertotalinfo['epurse'])) {
            //消费积分+钱包余额支付即可
            $yue = numberFormat('-', $totalprice, $usertotalinfo['gouwubi']);
            if($usertotalinfo['gouwubi']>0) {
                $gouwubiinfo = '<div class="line"><div class="label">消费积分余额支付</div>'.
                    '<div class="info">'.
                    '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$usertotalinfo['gouwubi'].'元</div></div>'.
                    '</div>'.
                    '</div>';
            }
            $payinfo = '<div class="order_main clearfix">'.
                $gouwubiinfo.
                '<div class="line"><div class="label">钱包余额支付</div>'.
                '<div class="info" style="border-bottom:none;">'.
                '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$yue.'元</div></div>'.
                '</div>'.
                '</div>'.
                '</div>';
            $payway = 'jfpay';
        }else {
            //消费积分+钱包余额+微信支付支付即可
            $yue = numberFormat('-', $totalprice, $usertotalinfo['gouwubi']+$usertotalinfo['epurse']);
            if($usertotalinfo['gouwubi']>0) {
                $gouwubiinfo = '<div class="line"><div class="label">消费积分余额支付</div>'.
                    '<div class="info">'.
                    '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$usertotalinfo['gouwubi'].'元</div></div>'.
                    '</div>'.
                    '</div>';
            }
            if($usertotalinfo['epurse']>0) {
                $epurseinfo = '<div class="line"><div class="label">钱包余额支付</div>'.
                    '<div class="info">'.
                    '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$usertotalinfo['epurse'].'元</div></div>'.
                    '</div>'.
                    '</div>';
            }
            $payinfo = '<div class="order_main clearfix">'.
                $gouwubiinfo.
                $epurseinfo.
                '<div class="line"><div class="label">微信应支付</div>'.
                '<div class="info" style="border-bottom:none;">'.
                '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$yue.'元</div></div>'.
                '</div>'.
                '</div>'.
                '</div>';
            $payway = 'wxpay';
            $wxprice = $yue;
        }
        return [
            'ids'=>$idsStr,
            'ordersn_general'=>$ordersn_general,
            'payinfo'=>$payinfo,
            'payway'=>$payway,
            'totalprice'=>number_format($totalprice, 2),
            'wxprice'=>$wxprice ? $wxprice : 0
        ];
    }

    /**
     * 根据综合订单编号支付
     * @param $ordersn_general 综合订单编号
     * @param $userid 会员id
     */
    public function getOrderPayinfoWithOrdersn($ordersn_general, $userid) {
        $orderpaylist = $this
            ->field('id,ordersn_general,products,price,dispatchprice')
            ->where("userid = '{$userid}' AND ordersn_general='{$ordersn_general}'")
            ->order(array('create'=>'ASC'))
            ->select();
        $totalprice = 0;
        $idsArr = [];
        foreach ($orderpaylist as $key=>$value) {
            $idsArr[] = $value['id'];
            $totalprice += ($value['price']+$value['dispatchprice']);
        }
        //获取会员账户信息
        $usertotalinfo = D('Total')->getUserTotal($userid);
        if($totalprice<=$usertotalinfo['gouwubi']) {
            //消费积分支付即可
            $payinfo = '<div class="order_main clearfix">'.
                '<div class="line"><div class="label">消费积分余额支付</div>'.
                    '<div class="info" style="border-bottom:none;">'.
                        '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$totalprice.'元</div></div>'.
                    '</div>'.
                '</div>'.
            '</div>';
            $payway = 'jfpay';
        }elseif ($totalprice<=($usertotalinfo['gouwubi']+$usertotalinfo['epurse'])) {
            //消费积分+钱包余额支付即可
            $yue = numberFormat('-', $totalprice, $usertotalinfo['gouwubi']);
            if($usertotalinfo['gouwubi']>0) {
                $gouwubiinfo = '<div class="line"><div class="label">消费积分余额支付</div>'.
                    '<div class="info">'.
                    '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$usertotalinfo['gouwubi'].'元</div></div>'.
                    '</div>'.
                    '</div>';
            }
            $payinfo = '<div class="order_main clearfix">'.
                $gouwubiinfo.
                '<div class="line"><div class="label">钱包余额支付</div>'.
                    '<div class="info" style="border-bottom:none;">'.
                        '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$yue.'元</div></div>'.
                    '</div>'.
                '</div>'.
            '</div>';
            $payway = 'jfpay';
        }else {
            //消费积分+钱包余额+微信支付支付即可
            $yue = numberFormat('-', $totalprice, $usertotalinfo['gouwubi']+$usertotalinfo['epurse']);
            if($usertotalinfo['gouwubi']>0) {
                $gouwubiinfo = '<div class="line"><div class="label">消费积分余额支付</div>'.
                    '<div class="info">'.
                        '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$usertotalinfo['gouwubi'].'元</div></div>'.
                    '</div>'.
                '</div>';
            }
            if($usertotalinfo['epurse']>0) {
                $epurseinfo = '<div class="line"><div class="label">钱包余额支付</div>'.
                    '<div class="info">'.
                        '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$usertotalinfo['epurse'].'元</div></div>'.
                    '</div>'.
                '</div>';
            }
            $payinfo = '<div class="order_main clearfix">'.
                $gouwubiinfo.
                $epurseinfo.
                '<div class="line"><div class="label">微信应支付</div>'.
                    '<div class="info" style="border-bottom:none;">'.
                        '<div class="inner"><div style="color:#dd2727;font-weight: bold;">￥'.$yue.'元</div></div>'.
                    '</div>'.
                '</div>'.
            '</div>';
            $payway = 'wxpay';
        }
        return [
            'ids'=>implode('_', $idsArr),
            'ordersn_general'=>$ordersn_general,
            'payinfo'=>$payinfo,
            'payway'=>$payway,
            'totalprice'=>number_format($totalprice, 2)
        ];
    }

    /**
     * 取消订单即关闭订单
     * @param $ordersn_general 综合订单编号,可能有多个订单
     * @param $reason 取消订单的原因
     * @param $userid 会员id
     * @return bool
     */
    public function cancelOrder($ordersn_general, $reason, $userid) {
        return $this->where("ordersn_general='{$ordersn_general}' AND userid='{$userid}'")->setField(array(
            'reason'=>$reason,
            'order_state'=>'已关闭',
            'finishtime'=>time()
        ));
    }

    /**
     * 删除订单
     * @param $ordersn_general 综合订单编号,可能有多个订单
     * @param $userid 会员id
     * @return mixed
     */
    public function delOrder($ordersn_general, $userid) {
        return $this->where("ordersn_general='{$ordersn_general}' AND userid='{$userid}'")->delete();
    }

    /**
     * 确认收货
     * @param $ordersn_general 综合订单编号,可能有多个订单
     * @param $userid 会员id
     */
    public function completeOrder($ordersn_general, $userid) {
        $now_time = NOW_TIME;
        $state = $this->where("ordersn_general='{$ordersn_general}' AND userid='{$userid}'")->setField(array(
            'order_state'=>'已完成',
            'finishtime'=>$now_time
        ));
        if($state>0) {
            //发放订单分销佣金
            $orderlist = $this->field('id,price,products,shopid,create,delivertime')
                ->where("ordersn_general='{$ordersn_general}' AND userid='{$userid}'")
                ->select();
            $productname = [];
            $create = 0;
            $delivertime = 0;
            foreach ($orderlist as $key=>$value) {
                //1-3折专区的不需要增值共享
                if($value['shopid'] != 120) {
                    $this->yongjinForReseller($value['id']);
                }
                //商品
                $products = unserialize(stripcslashes($value['products']));
                foreach ($products as $k=>$v) {
                    $productname[] = $v['name'];
                }
                //订单创建成功
                $create = $value['create'];
                $delivertime = $value['delivertime'];
            }
            //微信推送消息
            $userinfo = M('User')->field('openid')->where("id='{$userid}'")->find();
            // 引入微信模板插件$userinfo['openid']
            vendor('WechatTemplate.WechatTemplate', '', '.class.php');
            $wechatTemplate = new \WechatTemplate();
            $wechatTemplate->sendTemplateMessage(urldecode(json_encode([
                "touser"=>$userinfo['openid'],
                "template_id"=>autoGetWXTemplateId('Order004'),
                "url"=>C('SITE_URL')."User/order/order_state/5.html",//U("User/order", array('order_state'=>5)),
                "topcolor"=>"#7B68EE",
                "data"=>[
                    "first"=>[
                        "value"=>urlencode("亲,您在爱尚缘消费共享商城的宝贝已确认收货!\\n"),
                        "color"=>"#743A3A"
                    ],
                    "keyword1"=>[
                        "value"=>urlencode($ordersn_general),
                        "color"=>"#C4C400"
                    ],
                    "keyword2"=>[
                        "value"=>urlencode(implode(',', $productname)),
                        "color"=>"#0000FF"
                    ],
                    "keyword3"=>[
                        "value"=>urlencode(date('Y年m月d日 H:i:s', $create)),
                        "color"=>"#0000FF"
                    ],
                    "keyword4"=>[
                        "value"=>urlencode(date('Y年m月d日 H:i:s', $delivertime)),
                        "color"=>"#0000FF"
                    ],
                    "keyword5"=>[
                        "value"=>urlencode(date('Y年m月d日 H:i:s', $now_time)),
                        "color"=>"#0000FF"
                    ],
                    "remark"=>[
                        "value"=>urlencode("\\n若您有疑问,请联系官方客服,客服电话为:0755-21002719!"),
                        "color"=>"#008000"
                    ]
                ]
            ])));
        }
        return $state;
    }

	/**
	 * 设置订单状态
	 * @param string $orderid     订单id
	 * @param string $userid      用户id
	 * @param string $order_state 订单状态，eg:待收货，已完成
	 */
	public function setOrderState($orderid, $order_state, $userid) {
		$map = array(
			'id'=>$orderid,
			'userid'=>$userid,
			'order_state'=>'待付款'
		);
		return $this->where($map)->setField(array(
		    'order_state'=>$order_state,
		    'paytime'=>NOW_TIME
		));
	}

    /**
     * 订单支付 - 积分支付
     * @param $payway 支付方式,jfpay or wxpay
     * @param $orderids 支付订单id集合
     * @param $userid 用户id
     */
    public function setOrderPay($payway = 'jfpay', $orderids, $userid) {
        $orderids = str_replace('_', ',', $orderids);//将_替换为,
        $orderpaylist = $this
            ->field('id,ordersn_general,products,price,dispatchprice')
            ->where("userid = '{$userid}' AND id IN ($orderids)")
            ->order(array('create'=>'ASC'))
            ->select();
        $ordersn_general = '';//综合订单编号
        $totalprice = 0;//支付总金额
        foreach ($orderpaylist as $key=>$value) {
            if($ordersn_general == '') $ordersn_general = $value['ordersn_general'];
            $totalprice += ($value['price']+$value['dispatchprice']);
            //更新产品的库存,加产品的销量
            $products = unserialize(stripcslashes($value['products']));
            foreach ($products as $k=>$v) {
                D('Product')->updateProductSales($v['id'], $v['num']);
            }
        }
        //获取会员账户信息
        $usertotalinfo = D('Total')->getUserTotal($userid);
        if($payway == 'jfpay') {
            if($totalprice<=$usertotalinfo['gouwubi']) {
                //消费积分支付即可
                D('Total')->where("userid='{$userid}'")->setDec('gouwubi', $totalprice);
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$totalprice,
                    'yue'=>numberFormat('-', $usertotalinfo['gouwubi'], $totalprice),
                    'tradeType'=>'购物消费',
                    'type'=>'支出',
                    'kind'=>'消费积分账户',
                    'info'=>'完全订单支付('.$ordersn_general.')',
                    'userid'=>$userid,
                    'create'=>NOW_TIME
                ));
                $paywayinfo = '消费积分支付'.$totalprice.'元';
                $paytype = 2 ;
            }elseif ($totalprice<=($usertotalinfo['gouwubi']+$usertotalinfo['epurse'])) {
                //消费积分+钱包余额支付即可
                if($usertotalinfo['gouwubi']>0) {
                    //消费积分支付一部分
                    D('Total')->where("userid='{$userid}'")->setDec('gouwubi', $usertotalinfo['gouwubi']);
                    //添加消费记录
                    D('Purse')->addPurse(array(
                        'jine'=>$usertotalinfo['gouwubi'],
                        'yue'=>numberFormat('-', $usertotalinfo['gouwubi'], $usertotalinfo['gouwubi']),
                        'tradeType'=>'购物消费',
                        'type'=>'支出',
                        'kind'=>'消费积分账户',
                        'info'=>'订单支付('.$ordersn_general.')',
                        'userid'=>$userid,
                        'create'=>NOW_TIME
                    ));
                }
                //钱包余额支付一部分
                $yue = numberFormat('-', $totalprice, $usertotalinfo['gouwubi']);
                D('Total')->where("userid='{$userid}'")->setDec('epurse', $yue);
                //添加消费记录
                D('Purse')->addPurse(array(
                    'jine'=>$yue,
                    'yue'=>numberFormat('-', $usertotalinfo['epurse'], $yue),
                    'tradeType'=>'购物消费',
                    'type'=>'支出',
                    'kind'=>'钱包余额账户',
                    'info'=>'订单支付('.$ordersn_general.')',
                    'userid'=>$userid,
                    'create'=>time()
                ));
                $paywayinfo = $usertotalinfo['gouwubi']>0 ? '消费积分支付'.$usertotalinfo['gouwubi'].'元<br>钱包余额支付'.$yue.'元' : '钱包余额支付'.$yue.'元';
                $paytype = $usertotalinfo['gouwubi']>0 ? 4 : 2 ;
            }
            //更新订单信息
            $state = M('Order')->where("id IN ($orderids)")->setField(array(
                'paytype'=>$paytype,
                'paywayinfo'=>$paywayinfo,
                'order_state'=>'待发货',
                'paytime'=>time()
            ));
            return $state>0 ? $state : 0;
        }
    }

    /**
     * 订单支付 - 微信支付
     * @param $ordersn_general 综合订单编号
     */
    public function setOrderPayByWx($ordersn_general, $transaction_id) {
        $orderpaylist = $this
            ->field('id,ordersn_general,products,price,dispatchprice,userid')
            ->where("ordersn_general = '{$ordersn_general}'")
            ->order(array('create'=>'ASC'))
            ->select();
        $ordersn_general = '';//综合订单编号
        $totalprice = 0;//支付总金额
        $userid = 0;
        foreach ($orderpaylist as $key=>$value) {
            if($userid == 0) $userid = $value['userid'];
            if($ordersn_general == '') $ordersn_general = $value['ordersn_general'];
            $totalprice += ($value['price']+$value['dispatchprice']);
            //更新产品的库存,加产品的销量
            $products = unserialize(stripcslashes($value['products']));
            foreach ($products as $k=>$v) {
                D('Product')->updateProductSales($v['id'], $v['num']);
            }
        }
        //获取会员账户信息
        $usertotalinfo = D('Total')->getUserTotal($userid);
        //消费积分+钱包余额支付即可
        if($usertotalinfo['gouwubi']>0) {
            //消费积分支付一部分
            D('Total')->where("userid='{$userid}'")->setDec('gouwubi', $usertotalinfo['gouwubi']);
            //添加消费记录
            D('Purse')->addPurse(array(
                'jine'=>$usertotalinfo['gouwubi'],
                'yue'=>numberFormat('-', $usertotalinfo['gouwubi'], $usertotalinfo['gouwubi']),
                'tradeType'=>'购物消费',
                'type'=>'支出',
                'kind'=>'消费积分账户',
                'info'=>'订单支付('.$ordersn_general.')',
                'userid'=>$userid,
                'create'=>NOW_TIME
            ));
        }
        if($usertotalinfo['epurse']>0) {
            //钱包余额支付一部分
            D('Total')->where("userid='{$userid}'")->setDec('epurse', $usertotalinfo['epurse']);
            //添加消费记录
            D('Purse')->addPurse(array(
                'jine' => $usertotalinfo['epurse'],
                'yue' => numberFormat('-', $usertotalinfo['epurse'], $usertotalinfo['epurse']),
                'tradeType' => '购物消费',
                'type' => '支出',
                'kind' => '钱包余额账户',
                'info' => '订单支付(' . $ordersn_general . ')',
                'userid' => $userid,
                'create' => NOW_TIME
            ));
        }
        //微信支付余额部分
        $wxyuepay = numberFormat('-', $totalprice, numberFormat('+', $usertotalinfo['epurse'], $usertotalinfo['gouwubi']));
        //添加消费记录
        D('Purse')->addPurse(array(
            'jine' => $wxyuepay,
            'yue' => '-',
            'tradeType' => '购物消费',
            'type' => '支出',
            'kind' => '微信钱包账户',
            'info' => '订单支付(' . $ordersn_general . ')',
            'userid' => $userid,
            'create' => NOW_TIME
        ));
        if($usertotalinfo['epurse']>0 && $usertotalinfo['gouwubi']>0) {
            $paywayinfo = '消费积分支付'.$usertotalinfo['gouwubi'].'元<br>钱包余额支付'.$usertotalinfo['epurse'].'元<br>微信支付'.$wxyuepay.'元';
            $paytype = 7;
        }else if($usertotalinfo['epurse']>0  && $usertotalinfo['gouwubi']==0) {
            $paywayinfo = '钱包余额支付'.$usertotalinfo['epurse'].'元<br>微信支付'.$wxyuepay.'元';
            $paytype = 6;
        }else if($usertotalinfo['epurse']==0  && $usertotalinfo['gouwubi']>0) {
            $paywayinfo = '消费积分支付'.$usertotalinfo['gouwubi'].'元<br>微信支付'.$wxyuepay.'元';
            $paytype = 5;
        }else {
            $paywayinfo = '微信支付'.$wxyuepay.'元';
            $paytype = 3;
        }
        //更新订单信息
        $state = M('Order')->where("ordersn_general='{$ordersn_general}'")->setField(array(
            'paytype'=>$paytype,
            'paywayinfo'=>$paywayinfo,
            'order_state'=>'待发货',
            'transaction_id'=>$transaction_id,
            'paytime'=>time()
        ));
        return $state>0 ? $state : 0;
    }

    /**
     * 获取单个订单信息
     * @param $orderid 订单id
     * @param $userid 会员id
     */
    public function getOneOrder($orderid, $userid) {
        $orderpaylist = $this
            ->field('id,ordersn_general,products,price,dispatchprice')
            ->where("userid = '{$userid}' AND id='{$orderid}'")
            ->order(array('create'=>'ASC'))
            ->select();
        $totalprice = 0;
        $idsArr = [];
        foreach ($orderpaylist as $key=>$value) {
            $idsArr[] = $value['id'];
            $totalprice += ($value['price']+$value['dispatchprice']);
        }
    }

    /**
     * 获取个人订单列表
     * @param string $userid 用户id
     */
    public function getMerchantUserOrderList($order_state, $userid) {
        $map['userid'] = $userid;
        switch ($order_state) {
            case 1:
                $map['order_state'] = '待发货';
                break;
            case 2:
                $map['order_state'] = '待收货';
                break;
            case 3:
                $map['order_state'] = '已完成';
                break;
            default:
                $map['order_state'] = array('neq', '已关闭');
                break;
        }
        $list = $this
            ->field('id,ordersn_general,order_state,expresssn,iscomment,products,price,dispatchprice,userid')
            ->where($map)
            ->order(array('create'=>'DESC'))
            ->select();
        $neworderlist = [];//保存输出订单信息
        foreach ($list as $key=>$value) {
            $products = unserialize(stripcslashes($value['products']));
            $flag = true;//是否已经存在总订单的标记,true表示不存在,false表示存在
            foreach ($neworderlist as $k=>$v) {
                if($value['ordersn_general'] == $v['ordersn_general']) {
                    $flag = false;
                    //说明该总订单已经存在,那么只需要将$value订单信息的商品添加到总订单商品数组中去即可,同时更新总订单的实付款金额
                    foreach ($products as $k1=>$v1) {
                        $neworderlist[$k]['num'] += $v1['num'];
                        //$v1['thumb'] = $v1['thumb'] ? C('SITE_URL').substr($v1['thumb'],1) : '';
                        if($v1['thumb'] && strpos($v1['thumb'], 'http://') === false) {
                            $products[$k1]['thumb'] = C('SITE_URL').substr($v1['thumb'],1);
                        }
                        $neworderlist[$k]['products'][] = $v1;
                    }
                    $neworderlist[$k]['total'] += $value['price']+$value['dispatchprice'];
                }
            }
            if($flag) {
                $num = 0;
                foreach ($products as $k1=>$v1) {
                    $num += $v1['num'];
                    $products[$k1]['thumb'] = $v1['thumb'] ? C('SITE_URL').substr($v1['thumb'],1) : '';
                }
                $neworderlist[] = [
                    'id'=>$value['id'],
                    'order_state'=>$value['order_state'],
                    'iscomment'=>$value['iscomment'],
                    'ordersn_general'=>$value['ordersn_general'],
                    'products'=>$products,//需要处理
                    'expresssn'=>$value['expresssn'],
                    'num'=>$num,//需要处理
                    'total'=>$value['price']+$value['dispatchprice'],
                    'url'=>C('SITE_URL').'?c=Payment&a=orderpay&ordersn_general='.$value['ordersn_general']//订单付款地址
                ];
            }
        }
        return $neworderlist;
    }

    /**
     * 获取商家订单列表
     * @param string $userid 商家用户id
     */
    public function getShopUserOrderList($order_state, $userid) {
        $map['b.userid'] = $userid;
        switch ($order_state) {
            case 1:
                $map['a.order_state'] = '待发货';
                break;
            case 2:
                $map['a.order_state'] = '待收货';
                break;
            case 3:
                $map['a.order_state'] = '已完成';
                break;
            default:
                $map['a.order_state'] = array('neq', '已关闭');
                break;
        }
        $list = $this
            ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
            ->field('a.id,a.ordersn_general,a.order_state,a.expresssn,a.iscomment,a.products,a.price,a.dispatchprice,a.userid')
            ->where($map)
            ->order(array('a.create'=>'DESC'))
            ->select();
        $neworderlist = [];//保存输出订单信息
        foreach ($list as $key=>$value) {
            $products = unserialize(stripcslashes($value['products']));
            $flag = true;//是否已经存在总订单的标记,true表示不存在,false表示存在
            foreach ($neworderlist as $k=>$v) {
                if($value['ordersn_general'] == $v['ordersn_general']) {
                    $flag = false;
                    //说明该总订单已经存在,那么只需要将$value订单信息的商品添加到总订单商品数组中去即可,同时更新总订单的实付款金额
                    foreach ($products as $k1=>$v1) {
                        $neworderlist[$k]['num'] += $v1['num'];
                        //$v1['thumb'] = $v1['thumb'] ? C('SITE_URL').substr($v1['thumb'],1) : '';
                        if($v1['thumb'] && strpos($v1['thumb'], 'http://') === false) {
                            $products[$k1]['thumb'] = C('SITE_URL').substr($v1['thumb'],1);
                        }
                        $neworderlist[$k]['products'][] = $v1;
                    }
                    $neworderlist[$k]['total'] += $value['price']+$value['dispatchprice'];
                }
            }
            if($flag) {
                $num = 0;
                foreach ($products as $k1=>$v1) {
                    $num += $v1['num'];
                    $products[$k1]['thumb'] = $v1['thumb'] ? C('SITE_URL').substr($v1['thumb'],1) : '';
                }
                $neworderlist[] = [
                    'id'=>$value['id'],
                    'order_state'=>$value['order_state'],
                    'iscomment'=>$value['iscomment'],
                    'ordersn_general'=>$value['ordersn_general'],
                    'products'=>$products,//需要处理
                    'expresssn'=>$value['expresssn'],
                    'num'=>$num,//需要处理
                    'total'=>$value['price']+$value['dispatchprice'],
                    'url'=>C('SITE_URL').'?c=Payment&a=orderpay&ordersn_general='.$value['ordersn_general']//订单付款地址
                ];
            }
        }
        return $neworderlist;
    }

    /**
     * 确认收货时,发放和该订单有关的分销佣金以及发放商家的佣金
     */
    public function yongjinForReseller($orderid) {
        //获取分销订单,之后需要将待收货换成已完成
        $thisOrder = $this
            ->join(array(
                'a LEFT JOIN __SHOP__ b ON a.shopid=b.id',
                'LEFT JOIN __TOTAL__ c ON b.id=c.userid',
                'LEFT JOIN __USER__ d ON a.userid=d.id'))
            ->field('a.id,a.ordersn,a.price,a.dispatchprice,a.order_state,a.products,a.userid,b.userid as shopuserid,c.shopsales,d.agentlevel')
            ->where("a.id='{$orderid}' AND a.order_state='已完成'")
            ->find();
        //print_r($thisOrder);
        /**
         * Array
        (
        [id] => 117
        [ordersn] => SL2017030758071651211
        [price] => 15690.00
        [dispatchprice] => 0.00
        [order_state] => 已完成
        [products] => a:1:{i:0;a:12:{s:2:"id";s:4:"1056";s:4:"name";s:24:"当臻黄秋葵牡蛎肽";s:5:"thumb";s:64:"./attachment/images/2/2016/12/b4G2i5ao5IHIkH767hg46fh8A7158h.jpg";s:11:"marketprice";s:7:"1569.00";s:12:"productprice";s:4:"0.00";s:13:"dispatchprice";s:4:"0.00";s:6:"shopid";s:1:"8";s:8:"genlisid";s:1:"3";s:11:"isreturntwo";s:1:"0";s:5:"total";s:3:"188";s:6:"maxbuy";s:1:"0";s:3:"num";s:2:"10";}}
        [userid] => 1651
        [shopuserid] => 9
        [shopsales] => 0
        [agentlevel] => 1
        )
         */
        //exit();
        //根据当前订单会员id向上获取两级分销商
        if($thisOrder) {

            $systeminfo = D('System')->getSystem();

            //产品信息
            $products = unserialize(stripcslashes($thisOrder['products']));
            //print_r($products);
            /**
             * Array
            ([0] => Array
            (
            [id] => 1056
            [name] => 当臻黄秋葵牡蛎肽
            [thumb] => ./attachment/images/2/2016/12/b4G2i5ao5IHIkH767hg46fh8A7158h.jpg
            [marketprice] => 1569.00
            [productprice] => 0.00
            [dispatchprice] => 0.00
            [shopid] => 8
            [genlisid] => 3
            [isreturntwo] => 0
            [total] => 188
            [maxbuy] => 0
            [num] => 10
            ))
             */
            //exit();
            //获取商家让利比例
            $genlislist = D('Genlis')->getProductGenlislist();
            //print_r($genlislist);Array([1] => 6[2] => 16[3] => 28)
            //exit();
            //获取商家增值比例
            $genlisArr = D('Genlis')->getProductGenlisForShop();
            //print_r($genlisArr);
            /**
             * Array
            (
            [1] => Array
            (
                [1] => 1.16
                [2] => 1.36
                [3] => 1.56
            )
            [2] => Array
            (
                [1] => 1.16
                [2] => 1.36
                [3] => 1.56
            )
            [3] => Array
            (
                [1] => 1.16
                [2] => 1.36
                [3] => 1.56
            )
            )
             */
            //exit();
            //获取消费者增值比例
            $agentArr = D('Agent')->getUserAgentList($thisOrder['agentlevel']);
            //print_r($agentArr);
            /**
             *
            Array
            (
            [1] => 0.24
            [2] => 0.4
            [3] => 0.99
            )
             */
            //exit();
            //获取产品让利等级对应招商员等级商家销售分成比例
            $mergenlisArr = D('Genlis')->getProductMerGenlis();
            //print_r($mergenlisArr);
            /**
             *
            Array
            (
            [1] => Array
            (
            [merchantfee1] => 0.0022
            [merchantfee2] => 0.11
            )
            [2] => Array
            (
            [merchantfee1] => 0.0057
            [merchantfee2] => 0.29
            )
            [3] => Array
            (
            [merchantfee1] => 0.01
            [merchantfee2] => 0.5
            )
            )
             */
            //exit();
            $storehuokuan = 0;//商家货款累计
            $shopvpsbi = 0;//商家增值积分累计
            $vpsbi = 0;//消费者增值积分累计
            $mersales = 0;//招商员拿推荐商家销售额的百分比
            $mersalesfenli = 0;//招商员拿推荐商家销售额的百分比
            foreach ($products as $k=>$v) {
                //产品佣金处理
                if($v['genlisid']>0) {
                    $producttotalprice = $v['marketprice']*$v['num'];
                    //商家增值比例
                    $shopvpsfee = $this->getShopVPSWithsales($v['genlisid'], $thisOrder['shopsales'], $genlisArr);
                    //商家货款
                    $storehuokuan += $v['isreturntwo'] == 0 ?
                        $producttotalprice*((100 - $genlislist[$v['genlisid']])/100) :
                        $producttotalprice*((100 - $genlislist[$v['genlisid']]*2)/100);
                    //商家增值积分
                    $shopvpsbi += $v['isreturntwo'] == 0 ?
                        $producttotalprice*($genlislist[$v['genlisid']]/100)*$shopvpsfee :
                        $producttotalprice*($genlislist[$v['genlisid']]*2/100)*$shopvpsfee;
                    //消费者增值积分
                    $vpsbi += $v['isreturntwo'] == 0 ?
                        $producttotalprice*$agentArr[$v['genlisid']] :
                        $producttotalprice*$agentArr[$v['genlisid']]*2;
                    //招商员拿推荐商家销售额的百分比
                    $mersales += $producttotalprice*$mergenlisArr[$v['genlisid']]['merchantfee1'];
                    $mersalesfenli += $mersales*$mergenlisArr[$v['genlisid']]['merchantfee2'];
                }
            }
//            echo '产品累计价格:'.$producttotalprice.'<br/>';//15690
//            echo '商家增值比例:'.$shopvpsfee.'<br/>';//1.16
//            echo '商家货款:'.$storehuokuan.'<br/>';//11296.8
//            echo '商家增值积分:'.$shopvpsbi.'<br/>';//5096.112
//            echo '消费者增值积分:'.$vpsbi.'<br/>';//15533.1
//            exit();

            //1.累计消费者消费额即累计消费者增值积分
            M('Total')->where("userid='{$thisOrder['userid']}'")->setField(array(
                'vpsbi'=>array('exp', 'vpsbi+'.$vpsbi),
                'spending'=>array('exp', 'spending+'.$thisOrder['price'])
            ));
            //消费者增值积分明细
            $vpsbi>0 && $purseArr[] = [
                'jine' => $vpsbi,
                'yue' => M('Total')->where("userid='{$thisOrder['userid']}'")->getField('vpsbi'),
                'tradeType' => '积分增值',
                'type' => '收入',
                'kind' => '增值积分账户',
                'info' => '线上消费(' . $thisOrder['ordersn'] . ')',
                'userid' => $thisOrder['userid'],
                'create' => NOW_TIME
            ];
            //消费者消费额明细
            $purseArr[] = [
                'jine' => $thisOrder['price'],
                'yue' => M('Total')->where("userid='{$thisOrder['userid']}'")->getField('spending'),
                'tradeType' => '购物消费',
                'type' => '收入',
                'kind' => '消费额账户',
                'info' => '线上消费(' . $thisOrder['ordersn'] . ')',
                'userid' => $thisOrder['userid'],
                'create' => NOW_TIME
            ];

            //2.给商家结算货款并累计销售额及累计增值积分
            M('Total')->where("userid='{$thisOrder['shopuserid']}'")->setField(array(
                'shopbi' => array('exp', 'shopbi+' . $storehuokuan),
                'shopvpsbi' => array('exp', 'shopvpsbi+'.$shopvpsbi),
                'shopsales' => array('exp', 'shopsales+'.$thisOrder['price'])
            ));
            //添加商家货款明细
            $storehuokuan>0 && $purseArr[] = [
                'jine' => $storehuokuan,
                'yue' => M('Total')->where("userid='{$thisOrder['shopuserid']}'")->getField('shopbi'),
                'tradeType' => '商家货款',
                'type' => '收入',
                'kind' => '商家货款账户',
                'info' => '线上消费(' . $thisOrder['ordersn'] . ')',
                'userid' => $thisOrder['shopuserid'],
                'create' => NOW_TIME
            ];
            //添加商家增值积分明细
            $shopvpsbi>0 && $purseArr[] = [
                'jine' => $shopvpsbi,
                'yue' => M('Total')->where("userid='{$thisOrder['shopuserid']}'")->getField('shopvpsbi'),
                'tradeType' => '积分增值',
                'type' => '收入',
                'kind' => '商家增值积分账户',
                'info' => '线上消费(' . $thisOrder['ordersn'] . ')',
                'userid' => $thisOrder['shopuserid'],
                'create' => NOW_TIME
            ];
            //添加商家销售额累计明细
            $purseArr[] = [
                'jine' => $thisOrder['price'],
                'yue' => numberFormat('+', $thisOrder['shopsales'], $thisOrder['price']),
                'tradeType' => '商家销售额',
                'type' => '收入',
                'kind' => '商家销售额账户',
                'info' => '线上消费(' . $thisOrder['ordersn'] . ')',
                'userid' => $thisOrder['shopuserid'],
                'create' => NOW_TIME
            ];
            //批量添加
            !empty($purseArr) && M('Purse')->addAll($purseArr);

            //3.发放招商员销售额分成
            if($mersales>0) {
                $merrefereeinfo = M('User')
                    ->join(array('a LEFT JOIN __USER__ b ON a.shopreferee=b.id'))
                    ->field('a.shopreferee,b.merchantreferee')
                    ->where("a.id='{$thisOrder['shopuserid']}' AND a.isshop=1")
                    ->find();

                if ($merrefereeinfo['shopreferee'] > 0) {
                    if($mersales>0) {
                        $merepurse = $mersales * $systeminfo['epursefee'];
                        $mergouwubi = $mersales * $systeminfo['gouwubifee'];
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
                            'info' => '推荐商家销售额分成激励(' . $thisOrder['ordersn'] . ')',
                            'userid' => $merrefereeinfo['shopreferee'],
                            'create' => NOW_TIME
                        ];
                        $merepurse > 0 && $merpurseArr[] = [
                            'jine' => $merepurse,
                            'yue' => M('Total')->where("userid='{$merrefereeinfo['shopreferee']}'")->getField('epurse'),
                            'tradeType' => '现金奖励',
                            'type' => '收入',
                            'kind' => '钱包余额账户',
                            'info' => '推荐商家销售额分成激励(' . $thisOrder['ordersn'] . ')',
                            'userid' => $merrefereeinfo['shopreferee'],
                            'create' => NOW_TIME
                        ];
                        $mergouwubi > 0 && $merpurseArr[] = [
                            'jine' => $mergouwubi,
                            'yue' => M('Total')->where("userid='{$merrefereeinfo['shopreferee']}'")->getField('gouwubi'),
                            'tradeType' => '现金奖励',
                            'type' => '收入',
                            'kind' => '消费积分账户',
                            'info' => '推荐商家销售额分成激励(' . $thisOrder['ordersn'] . ')',
                            'userid' => $merrefereeinfo['shopreferee'],
                            'create' => NOW_TIME
                        ];
                    }
                    if ($merrefereeinfo['merchantreferee'] > 0) {
                        if($mersalesfenli>0) {
                            $merfenliepurse = $mersalesfenli * $systeminfo['epursefee'];
                            $merfenligouwubi = $mersalesfenli * $systeminfo['gouwubifee'];
                            M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->setField(array(
                                'epurse' => array('exp', 'epurse+' . $merfenliepurse),
                                'merchant_yongjin' => array('exp', 'merchant_yongjin+' . $mersalesfenli),
                                'gouwubi' => array('exp', 'gouwubi+' . $merfenligouwubi)
                            ));
                            //添加财务记录
                            $mersalesfenli > 0 && $merpurseArr[] = [
                                'jine' => $mersalesfenli,
                                'yue' => M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->getField('merchant_yongjin'),
                                'tradeType' => '招商佣金',
                                'type' => '收入',
                                'kind' => '招商佣金账户',
                                'info' => '招商员推荐招商员,招商员推荐商家销售额分成激励(' . $thisOrder['ordersn'] . ')',
                                'userid' => $merrefereeinfo['merchantreferee'],
                                'create' => NOW_TIME
                            ];
                            $merfenliepurse > 0 && $merpurseArr[] = [
                                'jine' => $merfenliepurse,
                                'yue' => M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->getField('epurse'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '钱包余额账户',
                                'info' => '招商员推荐招商员,招商员推荐商家销售额分成激励(' . $thisOrder['ordersn'] . ')',
                                'userid' => $merrefereeinfo['merchantreferee'],
                                'create' => NOW_TIME
                            ];
                            $merfenligouwubi > 0 && $merpurseArr[] = [
                                'jine' => $merfenligouwubi,
                                'yue' => M('Total')->where("userid='{$merrefereeinfo['merchantreferee']}'")->getField('gouwubi'),
                                'tradeType' => '现金奖励',
                                'type' => '收入',
                                'kind' => '消费积分账户',
                                'info' => '招商员推荐招商员,招商员推荐商家销售额分成激励(' . $thisOrder['ordersn'] . ')',
                                'userid' => $merrefereeinfo['merchantreferee'],
                                'create' => NOW_TIME
                            ];
                        }
                    }
                    !empty($merpurseArr) && M('Purse')->addAll($merpurseArr);
                }
            }

            //4.发放分销佣金
            //保存分销商id,同时保存当前分销商等级对应的产品让利等级的分销比例
            /**
            Array (
              [0] => Array ( [userid] => 19 [floor] => 1 [level] => Array ( [1] => 0.1 [2] => 0.29 [3] => 0.5 ) )
              [1] => Array ( [userid] => 11 [floor] => 2 [level] => Array ( [1] => 0.18 [2] => 0.43 [3] => 0.8 ) )
              )
             */
            $resellerUseridArr = D('User')->getResellerUseridArr($thisOrder['userid']);
            //print_r($resellerUseridArr);
            /**
             *
            Array
            (
            [0] => Array
            (
            [userid] => 1618
            [floor] => 1
            [level] => Array
            (
                [1] => 0.1
                [2] => 0.29
                [3] => 0.5
            )
            )
            )
             */
            //exit();
            //产品信息
            $products = unserialize(stripcslashes($thisOrder['products']));
            //分销商循环
            foreach ($resellerUseridArr as $key=>$value) {
                $yongjin = 0;//累计佣金
                foreach ($products as $k1=>$v1) {
                    //产品佣金处理
                    if($v1['genlisid']>0) {
                        $producttotalprice = $v1['marketprice']*$v1['num'];
                        $yongjin += number_format($producttotalprice*$value['level'][$v1['genlisid']]/100, 3);
                    }
                }
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
                    $purseArr1 = [
                        [
                            'jine' => $yongjin,
                            'yue' => M('Total')->where("userid='{$value['userid']}'")->getField('reseller_yongjin'),
                            'tradeType' => '分销佣金',
                            'type' => '收入',
                            'kind' => '分销佣金账户',
                            'info' => '线上购物('.$thisOrder['ordersn'].')<br>'.($value['floor'] == 1 ? '一级分销佣金' : '二级分销佣金'),
                            'userid' => $value['userid'],
                            'create' => NOW_TIME
                        ],
                        [
                            'jine' => $epurse,
                            'yue' => M('Total')->where("userid='{$value['userid']}'")->getField('epurse'),
                            'tradeType' => '现金奖励',
                            'type' => '收入',
                            'kind' => '钱包余额账户',
                            'info' => '线上购物('.$thisOrder['ordersn'].')<br>'.($value['floor'] == 1 ? '一级分销佣金' : '二级分销佣金'),
                            'userid' => $value['userid'],
                            'create' => NOW_TIME
                        ],
                        [
                            'jine' => $gouwubi,
                            'yue' => M('Total')->where("userid='{$value['userid']}'")->getField('gouwubi'),
                            'tradeType' => '现金奖励',
                            'type' => '收入',
                            'kind' => '消费积分账户',
                            'info' => '线上购物('.$thisOrder['ordersn'].')<br>'.($value['floor'] == 1 ? '一级分销佣金' : '二级分销佣金'),
                            'userid' => $value['userid'],
                            'create' => NOW_TIME
                        ]
                    ];
                    M('Purse')->addAll($purseArr1);
                }
            }
        }
    }

    /**
     * 根据产品让利等级id和商家销售额获取商家的增值比例
     * @param $genlisid 产品让利等级id
     * @param $shopsales 商家销售额
     */
    public function getShopVPSWithsales($genlisid, $shopsales, $genlisArr = array()) {
        if(!is_array($genlisArr)) return;
        $vpsfeeArr = $genlisArr[$genlisid];
        $vpsfee = 0;//增值比例
        if($shopsales <= 1000000) {
            $vpsfee = $vpsfeeArr[1];
        }else if(1000000 < $shopsales && $shopsales <= 2000000) {
            $vpsfee = $vpsfeeArr[2];
        }else if($shopsales > 2000000) {
            $vpsfee = $vpsfeeArr[3];
        }
        return $vpsfee;
    }

    /**
     * 清空30分钟未支付订单
     */
    public function clearNopayOrder() {
        $orderlist = $this->field('id,create')->where("order_state='待付款' OR (order_state='已关闭' AND paytype=0)")->select();
        if($orderlist) {
            $ids = [];
            foreach ($orderlist as $key=>$value) {
                if(time() - $value['create'] >= 30*60) {
                    $ids[] = $value['id'];
                }
            }
            if($ids) {
                $idsstr = implode(',', $ids);
                $this->where("id IN ($idsstr)")->delete();
            }
        }
    }

    /**
     * 7天自动确认收货
     */
    public function autoiscompleteorder() {
        $orderlist = $this->field('id,userid,shopid')->where("order_state='待收货' AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) >= DATE(FROM_UNIXTIME(delivertime))")->order('id ASC')->select();
        foreach ($orderlist as $key=>$value) {
            $this->where("id='{$value['id']}'")->setField(array(
                'order_state'=>'已完成',
                'finishtime'=>NOW_TIME
            ));
            if($value['shopid'] != 120) {
                $this->yongjinForReseller($value['id']);
            }
        }
    }

    /**
     * 获取集合订单详情
     * @param $ordersn_general 总订单编号，可能包括多个订单集合
     * @param $userid 用户id
     */
    public function getOneOrderDetail($ordersn_general, $userid) {
        $orderlist = $this
            ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
            ->field('a.id,a.ordersn,a.ordersn_general,a.products,a.price,a.dispatchprice,a.userid,a.shopid,a.order_state,a.create,b.shopname')
            ->where("a.ordersn_general = '{$ordersn_general}' AND a.userid='{$userid}'")
            ->order(array('a.create'=>'ASC'))
            ->select();
        foreach ($orderlist as $key=>$value) {
            $orderlist[$key]['totalprice'] = number_format($value['price']+$value['dispatchprice'], 2);
            //更新产品的库存,加产品的销量
            $products = unserialize(stripcslashes($value['products']));
            foreach($products as $k1=>$v1) {
                if($v1['thumb'] && strpos($v1['thumb'], 'http://') === false) {
                    $products[$k1]['thumb'] = C('SITE_URL').substr($v1['thumb'],1);
                }
            }
            $orderlist[$key]['products'] = $products;
            $orderlist[$key]['create'] = date('Y-m-d H:i:s', $value['create']);
            $orderlist[$key]['payurl'] = C('SITE_URL').'?c=Payment&a=orderpay&ordersn_general='.$value['ordersn_general'];
        }
        return $orderlist;
    }
	
}