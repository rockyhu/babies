<?php
namespace Home\Model;
use Think\Model;

/**
 * 店铺模型
 * @author rockyhu
 *
 */
class ShopStoreModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();

    /**
     * 获取店铺列表
     */
    public function getShopStoreList($order = 'create', $by = 'desc') {
        //排序
        $ordermap[$order] = $by;
        $list = $this
            ->field('id,name,address,genlisid,phone,thumb,images,stars,spends,content,sales,userid,shopid,create')
            ->where("status='审核通过'")
            ->order($ordermap)
            ->select();
        //echo $this->getLastSql();
        foreach ($list as $key=>$value) {
            $list[$key]['url'] = U('Store/detail', array('id'=>$value['id']));
            $list[$key]['thumb'] = $value['thumb'] ? C('SITE_URL').substr(json_decode($value['thumb'])->source,1) : '';
            $value['stars'] = $value['stars'] ? $value['stars'] : 5;
            //综合评分
            $list[$key]['starhtml'] = getStarHtml($value['stars']);
            //销售量
            $list[$key]['count'] = $value['sales'];
            if($value['genlisid'] == 3) {
                $list[$key]['genlis'] = '三星商家';
            }else if($value['genlisid'] == 2) {
                $list[$key]['genlis'] = '二星商家';
            }else {
                $list[$key]['genlis'] = '一星商家';
            }
        }
        return $list;
    }

    /**
     * 获取店铺的详情
     * @param $id 店铺id
     */
    public function getOneShopStoreDetail($id) {
        $oneShopStore = $this->field('id,name,address,location,genlisid,phone,thumb,images,stars,spends,content,userid,shopid,create')->where("id='{$id}'")->find();
        if($oneShopStore) {
            //缩略图
            $oneShopStore['thumb'] = $oneShopStore['thumb'] ? C('SITE_URL').substr(json_decode($oneShopStore['thumb'])->source,1) : '';
            //图片集合
            if($oneShopStore['images']) {
                $images = json_decode($oneShopStore['images']);
                $imagesArr = [];
                foreach ($images as $k=>$img) {
                    $imagesArr[] = C('SITE_URL').substr($img->source, 1);
                }
            }else {
                $imagesArr = $oneShopStore['thumb'] ? [$oneShopStore['thumb']] : [];
            }
            $oneShopStore['images'] = $imagesArr;
            $oneShopStore['imagescount'] = count($imagesArr);
            $oneShopStore['content'] = htmlspecialchars_decode($oneShopStore['content']);
            //买单链接
            $oneShopStore['payurl'] = C('SITE_URL').'?c=Payment&a=autoStorePay&id='.$id;
            //评分HTML
            $oneShopStore['stars'] = $oneShopStore['stars'] ? $oneShopStore['stars'] : 5;
            $oneShopStore['starhtml'] = getStarHtml($oneShopStore['stars']);
            //人均
            $oneShopStore['spends'] = $oneShopStore['spends'] > 0 ? $oneShopStore['spends'] : '56.00';
            if($oneShopStore['genlisid'] ==3) {
                $oneShopStore['genlis'] = '三星商家';
            }else if($oneShopStore['genlisid'] ==2) {
                $oneShopStore['genlis'] = '二星商家';
            }else {
                $oneShopStore['genlis'] = '一星商家';
            }
        }
        //print_r($oneShopStore);
        //exit();
        return $oneShopStore;
    }

    /**
     * 获取店铺详情 - 支付页面
     * @param $id 店铺id
     * @return mixed
     */
    public function getOneShopStoreBuy($id, $userid) {
        $oneShopStore = $this->field('id,name,shopid')->where("id='{$id}'")->find();
        $redtotal = M('StoreOrder')->where("order_state='已完成'")->sum('red');
        return [
            'shopinfo'=>$oneShopStore,
            'userinfo'=>M('Total')->field('epurse,gouwubi')->where("userid='{$userid}'")->find(),
            'rednum'=>$redtotal>0 ? bcsub(2000*10000, $redtotal, 0) : '200000000'
        ];
    }

    /**
     * 获取店铺详情 - 评论页面
     * @param $id 店铺id
     * @return mixed
     */
    public function getOneShopStoreComment($storeid, $shopid, $userid) {
        $oneShopStore = $this->field('id,name,shopid')->where("id='{$storeid}'")->find();
        if($oneShopStore) {
            //获取最后支付的订单
            $storeorderinfo = M('StoreOrder')->field('id')->where("shopid='{$shopid}' AND storeid='{$storeid}' AND userid='{$userid}' AND order_state='已完成'")->order(array('create'=>'desc'))->find();
            $oneShopStore['orderid'] = $storeorderinfo['id'];
            $followed = M('User')->where("id='{$userid}'")->getField('followed');
            //判断当前会员是否已关注,已经关注的话,就直接跳转到详情页面,否则需要跳转到关注页面
            $oneShopStore['followedurl'] = $followed == 1 ? U('Store/detail', array('id'=>$oneShopStore['id'])) : U('Store/followed');
        }
        return $oneShopStore;
    }

    /**
     * 获取店铺的图片集合
     * @param $id 店铺id
     */
    public function getOneShopStoreImagelist($id) {
        $oneShopStore = $this->field('id,name,thumb,images')->where("id='{$id}'")->find();
        if($oneShopStore) {
            //图片集合
            if($oneShopStore['images']) {
                $images = json_decode($oneShopStore['images']);
                $imagesArr = [];
                foreach ($images as $k=>$img) {
                    $imagesArr[] = C('SITE_URL').substr($img->source, 2);
                }
            }else {
                $imagesArr = $oneShopStore['thumb'] ? [$oneShopStore['thumb']] : [];
            }
            $oneShopStore['images'] = $imagesArr;

        }
        return $oneShopStore;
    }

    /**
     * 获取实体店的定位
     * @param $storeid 实体店id
     */
    public function getOneShopStoreInfo($storeid) {
        return $this->field('id,name,address,location')->where("id='{$storeid}'")->find();
    }

    /**
     * 随机生成店铺红包
     */
    public function ShopStorexRandomRed($shopid, $storeid, $price, $userid) {
        $genlisid = $this->where("id='{$storeid}'")->getField('genlisid');
        if($genlisid == 1) {
            $room = ['min'=>$price, 'max'=>$price*2];
        }else if($genlisid == 2) {
            $room = ['min'=>$price, 'max'=>$price*4];
        }else if($genlisid == 3) {
            $room = ['min'=>$price, 'max'=>$price*8];
        }
        $rednum = mt_rand($room['min'], $room['max']);
        $rednum = bcdiv($rednum, 100, 2);
        session('rednum',[$userid=>$rednum]);//保存session红包额度
        //print_r(session('rednum')[$userid]);
        return $rednum;
    }

    /**
     * 搜索线下商家
     */
    public function getSearchStoreWithKeyword($keyword) {
        $searchlist = $this->field('id,name')->where("status='审核通过' AND name LIKE '%{$keyword}%'")->order('sales asc')->select();
        foreach ($searchlist as $key=>$value) {
            $searchlist[$key]['url'] = U('store/detail', array('id'=>$value['id']));
        }
        return $searchlist;
    }

}