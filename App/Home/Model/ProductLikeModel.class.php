<?php
namespace Home\Model;
use Think\Model;

/**
 * 商品收藏模型
 * @author rockyhu
 *
 */
class ProductLikeModel extends Model{

    /**
     * 获取用户收藏列表
     * @param $userid 用户id
     */
    public function getUserProductLikeList($userid) {
        $likeProductList = $this
            ->join(array('a LEFT JOIN __PRODUCT__ b ON a.productid=b.id'))
            ->field('a.id,a.productid,a.userid,b.name,b.marketprice,b.productprice,b.thumb,b.status')
            ->where("a.userid='{$userid}'")
            ->order(array('a.create'=>'DESC'))
            ->select();
        foreach ($likeProductList as $key=>$value) {
            $thumb = json_decode($value['thumb'])->source;
            if(strpos($thumb, 'http://') !== false) {
                $likeProductList[$key]['thumb'] = $thumb;
            }else {
                $likeProductList[$key]['thumb'] = C('SITE_URL').substr($thumb,1);
            }
        }
        return $likeProductList;
    }

    /**
     * 删除用户收藏
     * @param $productlikeid 收藏id集合
     * @param $userid 用户id
     */
    public function removeUserProductLike($productlikeids, $userid) {
        $map = array(
            'id'=>array('in', $productlikeids),
            'userid'=>$userid
        );
        return $this->where($map)->delete();
    }
	
	
}