<?php
namespace Home\Model;
use Think\Model;

class CartModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array(
	    array('create','time',self::MODEL_INSERT,'function')
	);
	
	//获取用户购物车列表
	public function getUserCartList($userid) {
        $map['a.userid'] = $userid;
        $Cart = $this
            ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
            ->field('a.id,a.products,a.price,a.productid,a.shopid,a.userid,b.shopname')
                        ->where($map)
                        ->order(array('a.create'=>'ASC'))
                        ->select();
        if($Cart) {
            $num = 0;
            $total = 0;
            foreach ($Cart as $key=>$value){
                //产品信息处理
                if(!empty($value['products'])){
                    $products = unserialize(stripcslashes($value['products']));
                    if($products) {
                        if($products['thumb'] && strpos($products['thumb'], 'http://') === false) {
                            $products['thumb'] = C('SITE_URL').substr($products['thumb'], 2);
                        }
                        $num += $products['num'];
                    }
                    $Cart[$key]['products'] = $products;
                    $total += $Cart[$key]['price'];
                }
            }
        }
	    return [
            'list'=>$Cart,
            'num'=>$num,
            'total'=>number_format($total, 2)
        ];
	}

    //获取用户购物车能产生订单的列表{即为一个商家会创建一个订单,一个订单里面可能会存在多个商品}
    /**
     * 创建商家订单,创建多少个订单以商家数未标准
     * @param $cartids 购物车id集合,如1_2_3
     * @param $userid 会员id
     * @return array
     */
    public function getUserCartOrderList($cartids, $userid) {
        $map['a.userid'] = $userid;
        $map['a.id'] = array('in', str_replace('_', ',', $cartids));
        $Cart = $this
            ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
            ->field('a.id,a.products,a.price,a.productid,a.shopid,a.userid,b.shopname,b.ispinkage,b.totalprice')
            ->where($map)
            ->order(array('a.create'=>'ASC'))
            ->select();
        if($Cart) {
            $total = 0;//累计总价格
            $orderlist = [];//商家订单数组
            foreach ($Cart as $k=>$v) {
                $flag = true;
                //为商家添加商品信息
                $products = unserialize(stripcslashes($v['products']));
                if($products['thumb'] && strpos($products['thumb'], 'http://') === false) {
                    $products['thumb'] = C('SITE_URL').substr($products['thumb'],1);
                }
                $products['cartid'] = $v['id'];
                foreach ($orderlist as $k1=>$v1) {
                    if($v['shopid'] == $v1['shopid']) {
                        $flag = false;
                        $orderlist[$k1]['products'][] = $products;
                        $orderlist[$k1]['cartids'][] = $v['id'];
                        //累加邮费
                        $orderlist[$k1]['dispatchprice'] += $products['dispatchprice'];
                        //累加总价格
                        $orderlist[$k1]['price'] += $v['price'];
                        //累加商品数量
                        $orderlist[$k1]['num'] += $products['num'];
                        continue;
                    }
                }
                if($flag) {
                    $v['products'] = $products;
                    $orderlist[] = [
                        'shopid'=>$v['shopid'],
                        'shopname'=>$v['shopname'],
                        'products'=>[$v['products']],
                        'dispatchprice'=>$products['dispatchprice'],
                        'num'=>$products['num'],
                        'price'=>$v['price'],
                        'ispinkage'=>$v['ispinkage'],
                        'totalprice'=>$v['totalprice']
                    ];
                }
            }
            //重新筛选统计订单总金额和邮费,考虑是否包邮的情况
            foreach ($orderlist as $key=>$value) {
                if($value['ispinkage'] && $value['price']>=$value['totalprice']) {
                    $orderlist[$key]['total'] += $value['price'];
                    $orderlist[$key]['dispinkagehtml'] = '<span class="ispinkage">单笔订单满'.$v['totalprice'].'包邮</span>';
                    $orderlist[$key]['ispinkline'] = 'underline';
                }else {
                    $orderlist[$key]['total'] += $value['price']+$value['dispatchprice'];
                }
                $total += $orderlist[$key]['total'];
            }
        }
        return [
            'list'=>$orderlist,
            'total'=>number_format($total, 2)
        ];
    }

    /**
     * 通过产品id获取确认订单信息
     * @param $productid 产品id
     * @param $userid 会员id
     */
    public function getUserCartOrderListWithProductid($productid, $userid) {
        $oneProduct = M('Product')
            ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
            ->field('a.id,a.name,a.thumb,a.marketprice,a.productprice,a.dispatchprice,a.shopid,a.genlisid,a.isreturntwo,a.total,a.maxbuy,b.shopname,b.ispinkage,b.totalprice')
            ->where("a.id='{$productid}'")
            ->find();
        $total = 0;
        $orderlist = [];//商家订单数组
        if($oneProduct) {
            if($oneProduct['thumb'] && strpos($oneProduct['thumb'], 'http://') === false) {
                $oneProduct['thumb'] = C('SITE_URL').substr(json_decode($oneProduct['thumb'])->source,1);
            }
            $oneProduct['num'] = 1;
            $orderlist[] = [
                'shopid'=>$oneProduct['shopid'],
                'shopname'=>$oneProduct['shopname'],
                'products'=>[$oneProduct],
                'dispatchprice'=>$oneProduct['dispatchprice'],
                'num'=>1,
                'price'=>number_format($oneProduct['marketprice'], 2),
                'total'=>number_format(($oneProduct['ispinkage'] && $oneProduct['marketprice']>=$oneProduct['totalprice']) ? $oneProduct['marketprice'] : $oneProduct['marketprice']+$oneProduct['dispatchprice'], 2),
                'dispinkagehtml'=>($oneProduct['ispinkage'] && $oneProduct['marketprice']>=$oneProduct['totalprice']) ? '<span class="ispinkage">单笔订单满'.$oneProduct['totalprice'].'包邮</span>' : '',
                'ispinkline'=>($oneProduct['ispinkage'] && $oneProduct['marketprice']>=$oneProduct['totalprice']) ? 'underline' : ''
            ];
            $total = ($oneProduct['ispinkage'] && $oneProduct['marketprice']>=$oneProduct['totalprice']) ? $oneProduct['marketprice'] : $oneProduct['marketprice']+$oneProduct['dispatchprice'];
        }
        return [
            'list'=>$orderlist,
            'total'=>number_format($total, 2)
        ];
    }

    /**
     * 创建购物车
     * @param $productid 商品id
     * @param $num 商品购买数量
     */
	public function addCart($productid, $num, $userid) {
        $map = [
            'productid'=>$productid,
            'userid'=>$userid
        ];
        $oneCart = $this->field('id,products')->where($map)->find();
        if(!is_array($oneCart)){//不存在
            $productinfo = M('Product')
                ->field('id,name,thumb,marketprice,productprice,dispatchprice,shopid,genlisid,isreturntwo,total,maxbuy')
                ->where("id='{$productid}'")->find();
            if($productinfo) {
                $productinfo['num'] = $num;
                $productinfo['thumb'] = $productinfo['thumb'] ? json_decode($productinfo['thumb'])->source : '';
//                if($productinfo['thumb'] && strpos($productinfo['thumb'], 'http://') === false) {
//                    $productinfo['thumb'] = C('SITE_URL').substr(json_decode($productinfo['thumb'])->source,1);
//                }
            }
            $data = array(
                'products'=>serialize($productinfo),//序列化产品信息
                'price'=>$productinfo['marketprice']*$num,
                'userid'=>$userid,
                'shopid'=>$productinfo['shopid'],
                'genlisid'=>$productinfo['genlisid'],
                'isreturntwo'=>$productinfo['isreturntwo'],
                'productid'=>$productid
            );
            if($this->create($data)){
                $cartid = $this->add();
                return $cartid ? $cartid : 0;
            }else{
                return $this->getError();
            }
        }else {
            //存在的时候更新购物车产品的数量
            $products = unserialize(stripcslashes($oneCart['products']));
            $products['num'] = $products['num']+$num;
            $products = serialize($products);
            $m['id'] = $oneCart['id'];
            $this->where($m)->setField('products',$products);
            return $oneCart['id'];
        }
    }
	
	//更新购物车
	public function updateCart($cartid, $num) {
	    //要先通过$cid查找出当前的购物车，然后更改购物车产品的购买数量
	    $map['id'] = $cartid;
	    $oneCart = $this->field('id,products,userid')->where($map)->find();
	    if($oneCart){
	        $newCart = unserialize(stripcslashes($oneCart['products']));
	        $newCart['num'] = $num;
            $price = $newCart['marketprice']*$num;
	        return $this->where($map)->setField(array(
	            'products'=>serialize($newCart),
                'price'=>$price
            ));
	    }
	    return 0;
	}
	
	//删除购物车，清空购物车
	public function deleteCart($cartids, $userid){
	    $map['userid'] = $userid;
	    return $this->where($map)->delete($cartids);
	}

    /**
     * 删除会员购物车商品
     * @param $cartids 购物车id
     * @param $userid 会员id
     * @return mixed
     */
	public function delCart($cartids, $userid) {
		$map = array(
			'id'=>array('in', implode(',', $cartids)),
			'userid'=>$userid
		);
		return $this->where($map)->delete();
	}

    /**
     * 获取会员购物车产品数量
     * @param $userid 会员id
     * @return array
     */
	public function getCartNum($userid) {
        $map['userid'] = $userid;
        $Cart = $this->field('id,products,price,userid')->where($map)->order(array('create'=>'ASC'))->select();
        if($Cart) {
            $num = 0;
            foreach ($Cart as $key=>$value){
                //产品信息处理
                if(!empty($value['products'])){
                    $products = unserialize(stripcslashes($value['products']));
                    if($products) {
                        $num += $products['num'];
                    }
                }
            }
        }
        return $num;
    }
	
}