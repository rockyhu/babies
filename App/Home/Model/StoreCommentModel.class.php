<?php
namespace Home\Model;
use Think\Model;

/**
 * 店铺评价模型
 * @author rockyhu
 *
 */
class StoreCommentModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();

    /**
     * 获取店铺的评价
     * @param $storeid 店铺id
     */
    public function getStoreCommentList($storeid) {
        $list = $this
            ->join(array('a LEFT JOIN __USER__ b ON a.userid=b.id'))
            ->field('a.id,a.info,a.images,a.star1,a.star2,a.userid,a.create,b.realname,b.nickname,b.avatar')
            ->where("a.storeid='{$storeid}'")
            ->order(array('a.create'=>'desc'))
            ->select();
        foreach ($list as $key=>$value) {
            //头像处理
            $list[$key]['avatar'] = $value['avatar'] ? $value['avatar'] : C('TMPL_PARSE_STRING')['__IMG__'].'/photo-mr.jpg';
            //昵称处理
            $list[$key]['realname'] = $value['realname'] ? $value['realname'] : $value['nickname'];
            //图片处理
            if($value['images']) {
                $images = json_decode($value['images']);
                $imagesArr = [];
                foreach ($images as $k=>$img) {
                    $imagesArr[] = C('SITE_URL').substr($img, 2);
                }
                $list[$key]['images'] = $imagesArr;
            }
            //日期处理
            $list[$key]['create'] = date('Y-m-d H:i', $value['create']);
            //综合评分处理
            $star = round(($value['star1']+$value['star2'])/2, 1);
            $list[$key]['star'] = $star;
            //评分html
            $list[$key]['starhtml'] = getStarHtml($star);
        }
        //print_r($list);
        return $list;
    }

    /**
     * 添加评分
     * @param $orderid 订单id
     * @param $shopid 商家id
     * @param $storeid 店铺id
     * @param $star1 店铺环境评分
     * @param $star2 店铺服务评分
     * @param $info 分享心得
     * @param $images 图片集合
     * @param $userid 会员id
     */
    public function addStoreComment($orderid, $shopid, $storeid, $star1, $star2, $info, $images, $userid) {
        $data = [
            'orderid'=>$orderid,
            'shopid'=>$shopid,
            'storeid'=>$storeid,
            'star1'=>$star1,
            'star2'=>$star2,
            'info'=>$info,
            'images'=>!empty($images) ? json_encode($images) : '',
            'userid'=>$userid,
            'create'=>time()
        ];
        $addState = $this->add($data);
        if($addState>0) {
            //综合评分
            $star = $this->getZongheStar($shopid, $storeid);
            M('ShopStore')->where("id='{$storeid}'")->setField('stars', $star);
        }
        return 1;
    }

    /**
     * 获取店铺综合评分
     * @param $shopid 商家id
     * @param $storeid 店铺id
     */
    public function getZongheStar($shopid, $storeid) {
        $storecommentlist = $this->field('star1,star2')->where("shopid='{$shopid}' AND storeid='{$storeid}'")->select();
        $star = 0;
        if($storecommentlist) {
            foreach ($storecommentlist as $key=>$value) {
                $star += $value['star1']+$value['star2'];
            }
            return $star/(count($storecommentlist)*2);
        }else {
            return $star;
        }
    }
	
}