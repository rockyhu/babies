<?php
namespace Home\Model;
use Think\Model;

/**
 * 商品模型
 * @author rockyhu
 *
 */
class ProductModel extends Model{

    /**
     * 搜索商品
     * @param $keyword 关键字
     * @return mixed
     */
	public function getSearchProductWithKeyword($keyword) {
        $searchlist = $this->field('id,name')->where("status=1 AND name LIKE '%{$keyword}%'")->order('sort asc')->select();
        foreach ($searchlist as $key=>$value) {
            $searchlist[$key]['url'] = U('product/detail', array('id'=>$value['id']));
        }
        return $searchlist;
    }

    /**
     * @param $key 商品属性字段,isnew表示新品,isrecommand表示推荐,ishot表示热销,istime表示限时秒杀,isdiscount表示促销,issendfree表示免邮
     * @param $pnid 主分类id
     * @param $nid 子分类id
     * @param $order 排序字段,sales表示销量,marketprice表示价格,score表示评价
     * @param $by 排序方式,asc表示升序,desc表示降序
     * @param $limit 筛选多少条数据
     */
    public function getProductList($key, $item, $pnid = 0, $nid = 0, $order = 'sales', $by = 'desc', $page, $limit) {
        $pnid && $map['pnid'] = $pnid;
        $nid && $map['nid'] = $nid;
        //商品属性字段筛选
        switch ($key) {
            case 'isnew':
            case 'isrecommand':
            case 'ishot':
            case 'istime':
            case 'isdiscount':
            case 'issendfree':
                $map[$key] = 1;
                $pagename = '全部商品';
                break;
            case 'genlis'://星级筛选产品
                $map['genlisid'] = $item;
                $pagename = $item == 3 ? '三星产品区' : ($item == 2 ? '二星产品区' : '一星产品区');
                break;
            case 'shopid'://商家筛选产品
                $map['shopid'] = $item;
                $userinfo = M('Shop')->join(array('a LEFT JOIN __USER__ b ON a.userid=b.id'))->field('b.realname,b.nickname')->where("a.id='{$item}'")->find();
                $pagename = ($userinfo['nickname'] ? $userinfo['nickname'] : $userinfo['realname']).'的小店';
                break;
            default:
                $pagename = '全部商品';
                break;
        }
        //上架的商品
        $map['status'] = 1;
        //排序
        $ordermap['genlisid'] = 'DESC';
        $ordermap[$order] = $by;
        $list = $this->field('id,name,thumb,marketprice,sales,genlisid,isreturntwo')->where($map)->order($ordermap)->limit(($page-1)*$limit, $limit)->select();
        foreach ($list as $k=>$value) {
            $list[$k]['url'] = U('product/detail', array('id'=>$value['id']));
            //$list[$k]['thumb'] = $value['thumb'] ? C('SITE_URL').substr(json_decode($value['thumb'])->source,1) : '';
            $thumb = json_decode($value['thumb'])->source;
            if(strpos($thumb, 'http://') !== false) {
                $list[$k]['thumb'] = $thumb;
            }else {
                $list[$k]['thumb'] = C('SITE_URL').substr($thumb,1);
            }
            //产品星级判断
            switch ($value['genlisid']) {
                case 1://一星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star"></i><i class="ion-android-star"></i>';
                    break;
                case 2://二星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star"></i>';
                    break;
                case 3://三星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i>';
                    break;
            }
            if($value['isreturntwo']) {
                $list[$k]['isreturntwohtml'] = '<span class="isreturntwo">双倍增值</span>';
            }
        }
        return [
            'total'=>$this->where($map)->count(),
            'pagename'=>$pagename,
            'list'=>$list
        ];
    }

    /**
     * @param $key 商品属性字段,isnew表示新品,isrecommand表示推荐,ishot表示热销,istime表示限时秒杀,isdiscount表示促销,issendfree表示免邮
     * @param $pnid 主分类id
     * @param $nid 子分类id
     * @param $order 排序字段,sales表示销量,marketprice表示价格,score表示评价
     * @param $by 排序方式,asc表示升序,desc表示降序
     * @param $limit 筛选多少条数据
     */
    public function getShopProductList($userid, $key, $pnid = 0, $nid = 0, $order = 'sales', $by = 'desc') {
        $pnid && $map['a.pnid'] = $pnid;
        $nid && $map['a.nid'] = $nid;
        //商品属性字段筛选
        switch ($key) {
            case 'isnew':
            case 'isrecommand':
            case 'ishot':
            case 'istime':
            case 'isdiscount':
            case 'issendfree':
                $map['a.'.$key] = 1;
                break;
        }
        //上架的商品
        $map['a.status'] = 1;
        $map['c.id'] = $userid;
        //排序
        $ordermap['a.'.$order] = $by;
        $list = $this
            ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id','LEFT JOIN __USER__ c ON b.userid=c.id'))
            ->field('a.id,a.name,a.thumb,a.marketprice,a.sales,a.genlisid,a.isreturntwo')
            ->where($map)
            ->order($ordermap)
            ->select();
        foreach ($list as $k=>$value) {
            $list[$k]['url'] = U('product/detail', array('id'=>$value['id']));
            //$list[$k]['thumb'] = $value['thumb'] ? C('SITE_URL').substr(json_decode($value['thumb'])->source,1) : '';
            $thumb = json_decode($value['thumb'])->source;
            if(strpos($thumb, 'http://') !== false) {
                $list[$k]['thumb'] = $thumb;
            }else {
                $list[$k]['thumb'] = C('SITE_URL').substr($thumb,1);
            }
            //产品星级判断
            switch ($value['genlisid']) {
                case 1://一星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star"></i><i class="ion-android-star"></i>';
                    break;
                case 2://二星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star"></i>';
                    break;
                case 3://三星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i>';
                    break;
            }
            //产品是否参与双倍返还
            if($value['isreturntwo']) {
                $list[$k]['isreturntwohtml'] = '<span class="isreturntwo">双倍增值</span>';
            }
        }
        return [
            'total'=>count($list),
            'list'=>$list
        ];
    }

    /**
     * 获取产品id
     * @param $productid 产品id
     * @param $userid 用户id
     * @return mixed
     */
    public function getOneProduct($productid, $userid) {
        $oneProduct = $this
            ->join(array(
                'a LEFT JOIN __SHOP__ b ON a.shopid=b.id',
                'LEFT JOIN __USER__ c ON b.userid=c.id',
                'LEFT JOIN __PRODUCT_LIKE__ d ON a.id=d.productid AND d.userid='.$userid
            ))
            ->field('
                a.id,a.name,a.thumb,a.images,a.marketprice,a.dispatchprice,a.genlisid,a.productprice,a.isreturntwo,a.total,a.sales,a.nid,a.shopid,
                b.ispinkage,b.totalprice,
                c.realname,c.nickname,
                IF(d.id,1,0) as islike
                ')
            ->where("a.id='{$productid}'")
            ->find();
        if($oneProduct) {
            //真实姓名
            $oneProduct['nickname'] = $oneProduct['nickname'] ? $oneProduct['nickname'] : $oneProduct['realname'];
            $oneProduct['shopproductcount'] = $this->where("shopid='{$oneProduct['shopid']}'")->count();
            //缩略图处理
            $thumb = json_decode($oneProduct['thumb'])->source;
            if(strpos($thumb, 'http://') !== false) {
                $oneProduct['thumb'] = $thumb;
            }else {
                $oneProduct['thumb'] = C('SITE_URL').substr($thumb,1);
            }
            if($oneProduct['images'] && !is_null($oneProduct['images']) && $oneProduct['images'] != 'null') {
                $images = json_decode($oneProduct['images']);
                $imagesArr = [];
                foreach ($images as $k=>$img) {
                    if(strpos($img->source, 'http://') !== false) {
                        $imagesArr[] = $img->source;
                    }else {
                        $imagesArr[] = C('SITE_URL').substr($img->source, 1);
                    }
                }
            }else {
                $imagesArr = $oneProduct['thumb'] ? [$oneProduct['thumb']] : [];
            }
            $oneProduct['images'] = $imagesArr;
            //产品星级判断
            switch ($oneProduct['genlisid']) {
                case 1://一星产品
                    $oneProduct['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star"></i><i class="ion-android-star"></i>';
                    break;
                case 2://二星产品
                    $oneProduct['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star"></i>';
                    break;
                case 3://三星产品
                    $oneProduct['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i>';
                    break;
            }
            //产品是否参与双倍返还
            if($oneProduct['isreturntwo']) {
                $oneProduct['isreturntwohtml'] = '<span class="isreturntwo">双倍增值</span>';
            }
            //包邮
            if(empty(intval($oneProduct['dispatchprice']))) {
                $oneProduct['ispinkagehtml'] = '<span><i class="ion-android-drafts"></i> <span style="color:#f15353">包邮</span></span>';
            }else {
                //99包邮
                if($oneProduct['ispinkage']) {
                    $oneProduct['ispinkagehtml'] = '<span><i class="ion-android-drafts"></i> 单笔订单 满 <span style="color:#f15353">'.$oneProduct['totalprice'].'</span> 元包邮</span>';
                }
            }
        }
        //print_r($oneProduct);
        return $oneProduct;
    }

    /**
     * 获取商品的详情
     * @param $productid 商品id
     */
    public function getOneProductContent($productid) {
        $oneProduct = $this->field('content')->where("id='{$productid}'")->find();
        if($oneProduct) {
            $oneProduct['content'] = htmlspecialchars_decode($oneProduct['content']);
            $oneProduct['content'] = preg_replace('/class=\".+?\"/i', '', $oneProduct['content']);
            $oneProduct['content'] = preg_replace('/img.+?src=/i', 'img src="'.C('TMPL_PARSE_STRING')['__IMG__'].'/loading.png" class="img-loading animated bounceInUp" data-original=', $oneProduct['content']);
            $oneProduct['content'] = str_replace('/Uploads/', C('SITE_URL').'Uploads/', $oneProduct['content']);
        }
        return $oneProduct['content'];
    }

    /**
     * 获取所有推荐的产品
     */
    public function getRecommendProductList() {
        $list = $this
            ->field('id,name,thumb,marketprice,sales')
            ->where("isrecommand=1 AND status=1")->order("sort ASC")->select();
        foreach ($list as $k=>$value) {
            $list[$k]['url'] = U('product/detail', array('id'=>$value['id']));
            $thumb = json_decode($value['thumb'])->source;
            if(strpos($thumb, 'http://') !== false) {
                $list[$k]['thumb'] = $thumb;
            }else {
                $list[$k]['thumb'] = C('SITE_URL').substr($thumb,1);
            }
        }
        return $list;
    }

    /**
     * 获取指定页码的推荐产品
     * @param $page 当前页码
     */
    public function getRecommendPageProductList($page, $length = 10) {
        $list = $this
            ->join(array('a LEFT JOIN __SHOP__ b ON a.shopid=b.id'))
            ->field('a.id,a.name,a.thumb,a.marketprice,a.dispatchprice,a.sales,a.genlisid,a.isreturntwo,b.ispinkage,b.totalprice')
            ->where("a.isrecommand=1 AND a.status=1")
            ->order(array('a.genlisid'=>'DESC','a.sort'=>'DESC','a.sales'=>'DESC'))
            ->limit(intval(($page-1)*$length), intval($length))
            ->select();
        foreach ($list as $k=>$value) {
            $list[$k]['url'] = U('product/detail', array('id'=>$value['id']));
            //$list[$k]['thumb'] = $value['thumb'] ? C('SITE_URL').substr(json_decode($value['thumb'])->source, 2) : '';
            $thumb = json_decode($value['thumb'])->source;
            if(strpos($thumb, 'http://') !== false) {
                $list[$k]['thumb'] = $thumb;
            }else {
                $list[$k]['thumb'] = C('SITE_URL').substr($thumb,1);
            }
            //产品星级判断
            switch ($value['genlisid']) {
                case 0://不存在星级
                    $list[$k]['starhtml'] = '';
                    break;
                case 1://一星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star"></i><i class="ion-android-star"></i>';
                    break;
                case 2://二星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star"></i>';
                    break;
                case 3://三星产品
                    $list[$k]['starhtml'] = '<i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i><i class="ion-android-star star-check"></i>';
                    break;
            }
            //产品是否参与双倍返还
            if($value['isreturntwo']) {
                $list[$k]['isreturntwohtml'] = '<span class="isreturntwo">双倍增值</span>';
            }else {
                $list[$k]['isreturntwohtml'] = '';
            }
            if(empty(intval($value['dispatchprice']))) {
                $list[$k]['ispinkage'] = '<b class="ispinkage"><span>商家包邮</span></b>';
            }else {
                if($value['ispinkage'] == 1) {
                    $list[$k]['ispinkage'] = '<b class="ispinkage"><span>'.intval($value['totalprice']).'包邮</span></b>';
                }else {
                    $list[$k]['ispinkage'] = '';
                }
            }
        }
        return json_encode([
            'products'=>$list ? $list : array(),
            'pagesize'=>$length
        ]);
    }

    /**
     * 产品减库存,加销量
     * @param $productid 产品id
     * @param $num 购买数量
     */
    public function updateProductSales($productid, $num) {
        return $this->where("id='{$productid}'")->setField(array(
            'total'=>array('exp', 'total-'.$num),
            'sales'=>array('exp', 'sales+'.$num),
        ));
    }

    /**
     * 添加用户产品收藏
     * @param $userid 用户id
     * @param $productid 产品id
     * @param $islike 目前是否收藏
     */
    public function setProductIslike($userid, $productid, $islike = 0) {
        if($islike) {
            //删除收藏记录
            return M('ProductLike')->where("userid='{$userid}' AND productid='{$productid}'")->delete();
        }else {
            return M('ProductLike')->add([
                'userid'=>$userid,
                'productid'=>$productid,
                'create'=>time()
            ]);
        }
    }
	
	
}