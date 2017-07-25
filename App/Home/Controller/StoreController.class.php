<?php
namespace Home\Controller;

class StoreController extends HomeController{

    /**
     * 店铺列表
     */
    public function index() {
        if($this->userLogined()) {
            $this->assign('storelist', D('ShopStore')->getShopStoreList(
                !empty(I('get.order')) ? I('get.order') : 'sales',
                !empty(I('get.order')) ? I('get.by') : 'desc')
            );
            $this->display();
        }
    }

    /**
     * 商家详情
     */
    public function detail() {
        //当前的店铺id
        $storeid = I('get.id');
        //获取到当前店铺的管理员
        $shopid = M('ShopStore')->where("id='{$storeid}'")->getField('shopid');
        $userid = M('Shop')->where("id='{$shopid}'")->getField('userid');
        if($this->userLogined($userid)) {
            if(session('user_auth.id') == $userid) {
                $this->assign('show', 'hide');
            }else {
                $this->assign('show', 'show');
            }
            $this->assign('oneshopstore', D('ShopStore')->getOneShopStoreDetail($storeid));
            //获取商家店铺评论
            $this->assign('commentlist', D('StoreComment')->getStoreCommentList($storeid));
            $this->display();
        }
    }

    /**
     * 商家地图定位
     */
    public function map() {
        $this->assign('oneshopstore', D('ShopStore')->getOneShopStoreInfo(I('get.id')));
        $this->display();
    }

    /**
     * 商家相册
     */
    public function album() {
        $this->assign('imagelist', D('ShopStore')->getOneShopStoreImagelist(I('get.id')));
        $this->display();
    }

    /**
     * 买单
     */
    public function buy() {
        if($this->userLogined()) {
            $this->assign('oneshopstore', D('ShopStore')->getOneShopStoreBuy(I('get.id')));
            $this->display();
        }
    }

    /**
     * 随机生成红包金额
     */
    public function xRandomRed() {
        if(IS_AJAX) {
            echo D('ShopStore')->ShopStorexRandomRed(I('post.shopid'), I('post.storeid'), I('post.price'), session('user_auth.id'));
        }else {
            $this->error('非法操作');
        }
    }

    public function test() {
        echo D('ShopStore')->ShopStorexRandomRed(70, 7, 1000, 1618);
    }

    /**
     * 删除线下支付订单
     */
    public function removeStoreOrder() {
        if(IS_AJAX) {
            echo D('StoreOrder')->removeOneStoreOrder(I('post.shopid'), I('post.storeid'), session('user_auth.id'));
        }else {
            $this->error('非法操作');
        }
    }

    /**
     * 线下店铺支付
     */
    public function setShopStorePay() {
        if(IS_AJAX) {
            $red = I('post.red');
            //红包金额必须与生成时的保持一致
            if(!empty($red) && session('rednum')[session('user_auth.id')] == I('post.red') || empty($red)) {
                echo D('StoreOrder')->setShopStorePay(I('post.shopid'), I('post.storeid'), I('post.price'), I('post.gouwubi'), I('post.epurse'), I('post.wxpay'), I('post.red'), session('user_auth.id'));
            }else {
                $this->error('非法操作');
            }
        }else {
            $this->error('非法操作');
        }
    }

    /**
     * 添加评论的页面
     */
    public function addcomment() {
        if($this->userLogined()) {
            $this->assign('oneStore', D('ShopStore')->getOneShopStoreComment(I('get.storeid'), I('get.shopid'), session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 添加评论
     */
    public function addStoreComment() {
        if(IS_AJAX) {
            echo D('StoreComment')->addStoreComment(I('post.orderid'), I('post.shopid'), I('post.storeid'), I('post.star1'), I('post.star2'), I('post.info'), I('post.images'), session('user_auth.id'));
        }else {
            $this->error('非法操作');
        }
    }

    public function qrcode($storeid) {
        $qrimg = './Uploads/store/'.md5($storeid).'.png';
        if(!file_exists(mb_convert_encoding($qrimg , 'gbk' , 'utf-8'))) {
            $url = C('SITE_URL').substr(U('Store/detail', array('id'=>$storeid)), 1);
            $level = 3;
            $size = 10;
            Vendor('phpqrcode.phpqrcode');
            $errorCorrectionLevel =intval($level) ;//容错级别
            $matrixPointSize = intval($size);//生成图片大小
            //生成二维码图片
            $object = new \QRcode();
            $object->png($url, $qrimg, $errorCorrectionLevel, $matrixPointSize, 2);
        }
        echo '<img src="'.C('SITE_URL').substr($qrimg, 2).'" />';
    }

    /**
     * 进入关注关注公众号页面,点击即可关注
     */
    public function followed() {
        $this->display();
    }

    /**
     * 搜索实体商家
     */
    public function searchStoreWithKeyword() {
        if(IS_AJAX){
            $this->assign('searchResult', D('ShopStore')->getSearchStoreWithKeyword(I('get.keyword')));
            $this->assign('searchKey', '线下商家');
            $this->display('Category/searchProductResult');
        }else{
            $this->error('非法访问!');
        }
    }

}