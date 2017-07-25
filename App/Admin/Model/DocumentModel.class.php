<?php
namespace Admin\Model;
use Think\Model;

class DocumentModel extends Model{

    //自动验证
    protected $_validate = array();

    //自动完成
    protected $_auto = array(
        array('create','time',self::MODEL_INSERT,'function')
    );

    /**
     * ajax获取等级数据
     * @param string $draw datatables 获取Datatables发送的参数 必要, 这个值作者会直接返回给前台
     * @param string $order_column 排序的字段下标
     * @param string $order_dir 排序的方式，asc OR desc
     * @param string $search_value 搜索的条件
     * @param string $start 查找开始的地方
     * @param string $length 要查找数据的条数
     */
    public function ajaxlistDocument($draw, $search_value, $start, $length, $status, $shuxing, $pnid, $nid, $marketprice) {

        if(isNumeric($status) && $status != -1) $map['a.status'] = $status;
        if(!empty($shuxing)) $map['a.'.$shuxing] = 1;
        if(!empty($pnid) && $pnid != -1) $map['a.pnid'] = $pnid;
        if(!empty($nid) && $nid != -1) $map['a.nid'] = $nid;

        if(!empty($marketprice) && $marketprice != '默认排序') {
            $ordermap['a.marketprice'] = $marketprice;
        }else {
            $ordermap['a.create'] = 'DESC';
            $ordermap['a.sort'] = 'DESC';
        }

        //总记录数
        $recordsTotal = $this->table('__DOCUMENT__ a')->where($map)->count();

        //存在搜索条件
        if(strlen($search_value)>0){
            $map['_string'] = "a.name LIKE '%{$search_value}%'";
            $obj = $this
                ->join(array(
                    'a LEFT JOIN __DOCUMENT_NAV__ b ON a.nid=b.id',
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.pnid=c.id'))
                ->field('a.id,a.name,a.thumb,a.nid,a.status,a.create,a.isrecommand,a.isnew,a.ishot,a.isdiscount,a.issendfree,a.istime,a.isnodiscount,b.text,c.text as ntext')
                ->where($map)
                ->limit(intval($start), intval($length))
                ->order($ordermap)->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this
                ->join(array(
                    'a LEFT JOIN __DOCUMENT_NAV__ b ON a.nid=b.id',
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.pnid=c.id'))
                ->where($map)->count();
        }else{
            $obj = $this
                ->join(array(
                    'a LEFT JOIN __DOCUMENT_NAV__ b ON a.nid=b.id',
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.pnid=c.id'))
                ->field('a.id,a.name,a.thumb,a.nid,a.status,a.create,a.isrecommand,a.isnew,a.ishot,a.isdiscount,a.issendfree,a.istime,a.isnodiscount,b.text,c.text as ntext')
                ->where($map)
                ->limit(intval($start), intval($length))
                ->order($ordermap)->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this
                ->join(array(
                    'a LEFT JOIN __DOCUMENT_NAV__ b ON a.nid=b.id',
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.pnid=c.id'))
                ->where($map)->count();
        }

        $list = [];//返回数组
        foreach ($obj as $key=>$value) {
            //缩略图处理
            if($value['thumb']) {
                $thumbObj = json_decode($value['thumb']);
                if($thumbObj) {
                    if(strstr($thumbObj->source, 'http')) {
                        $obj[$key]['thumb'] = $thumbObj->source;
                    }else {
                        $obj[$key]['thumb'] = C('SITE_URL').substr($thumbObj->source, 2);
                    }
                }
            }
            //是否参与双返
            $isreturntwo = $value['isreturntwo'] ? ' <span class="text-info green">[参与双返]</span>' : '';
            //商品分类
            $value['ntext_html'] = $value['ntext'] ? '<span class="text-danger">['.$value['ntext'].']</span> <span class="text-info">['.$value['text'].']</span> <span class="text-info red">['.$value['genlisname'].']</span> '.$isreturntwo : '<span class="text-info">['.$value['text'].']</span> <span class="text-info red">['.$value['genlisname'].']</span> '.$isreturntwo;
            //商品属性
            $obj[$key]['shuxing_html'] = '<label class="label label-default cursor property '.($value['isnew'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="isnew" data-value="'.$value['isnew'].'">新品</label> - <label class="label label-default cursor property '.($value['ishot'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="ishot" data-value="'.$value['ishot'].'">热卖</label> - <label class="label label-default cursor property '.($value['isrecommand'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="isrecommand" data-value="'.$value['isrecommand'].'">推荐</label> - <label class="label label-default cursor property '.($value['isdiscount'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="isdiscount" data-value="'.$value['isdiscount'].'">促销</label> - <label class="label label-default cursor property '.($value['issendfree'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="issendfree" data-value="'.$value['issendfree'].'">包邮</label> - <label class="label label-default cursor property '.($value['istime'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="istime" data-value="'.$value['istime'].'">限时卖</label> - <label class="label label-default cursor property '.($value['isnodiscount'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="isnodiscount" data-value="'.$value['isnodiscount'].'">不参与折扣</label>';
            //是否参与订单全返
            $value['isreturn'] = $value['isreturn'] ? '<label class="label label-default">参与订单全返</label>' : '';
            //是否参与排列全返
            $value['isreturnqueue'] = $value['isreturnqueue'] ? '<label class="label label-default">参与排列全返</label>' : '';
            $obj[$key]['create'] = date('Y/m/d H:i',$value['create']);
            //商品状态
            $obj[$key]['status'] = $value['status'] ? '<label class="label label-info cursor product-status" data-status="'.$value['status'].'" data-id="'.$value['id'].'">上架</label>' : '<label class="label label-default cursor product-status" data-status="'.$value['status'].'" data-id="'.$value['id'].'">下架</label>';
            //是否是商家发布的商品
            if($value['shopname'] != '深圳市杏檀生物科技开发有限公司') {
                $value['check'] = $value['status'] ? '<br><span class="bg-green" style="margin-top:5px;display: inline-block">审核通过</span>' : '<br><span class="bg-red" style="margin-top:5px;display: inline-block">待审核</span>';
            }
            //权限处理
            $btn_html = '';
            if(in_array('Document/edit', session('pageNavDos'))) {
                $btn_html .= '<a href="'.U("Document/edit",array('id'=>$value['id'])).'" class="btn btn-xs btn-primary"><i class="ion-ios-compose-outline"></i></a> ';
            }
            if(in_array('Document/remove', session('pageNavDos'))) {
                $btn_html .= '<a href="javascript:void(0);" class="btn btn-xs btn-primary del-btn" data-id="'.$value['id'].'"><i class="ion-ios-close-outline"></i></a>';
            }

            $row = array(
                $key+1,
                '<img src="'.$obj[$key]['thumb'].'" style="width:40px;height:40px;padding:1px;border:1px solid #ccc;">',
                '<div style="text-align: left;"><span style="color: #000;text-decoration: underline">商家:'.$value['shopname'].'</span><br>'.$value['ntext_html'].'<br><span class="title">'.$value['name'].'</span><br><div style="padding-top:10px;">'.$obj[$key]['shuxing_html'].'</div><div style="padding-top:10px;">'.$value['isreturn'].' '.$value['isreturnqueue'].'</div></div>',
                $obj[$key]['marketprice'].'<br>'.$value['total'],
                $obj[$key]['sales'],
                $obj[$key]['status'].$value['check'],
                $obj[$key]['create'],
                $btn_html
            );
            array_push($list,$row);
        }

        //返回数据格式
        return json_encode(array(
            "draw"=>intval($draw),
            "recordsTotal"=>intval($recordsTotal),
            "recordsFiltered"=>intval($recordsFiltered),
            "data"=>$list
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取单个商品
     * @param number $id 商品id
     * @return string
     */
    public function getOneDocument($id) {
        $map['id'] = $id;
        $oneProduct = $this
            ->field('id,shopid,genlisid,sort,name,pnid,nid,type,isrecommand,isnew,ishot,isdiscount,issendfree,istime,isnodiscount,thumb,images,marketprice,productprice,costprice,total,maxbuy,sales,content,nocommission,hidecommission,status,isreturn,isreturntwo,isreturnqueue,return_appoint_amount,dispatchprice')
            ->where($map)
            ->find();
        if($oneProduct) {
            $oneProduct['content'] = htmlspecialchars_decode($oneProduct['content']);
        }
        return $oneProduct;
    }

    /**
     * 添加商品
     * @param $shopid 商家id
     * @param $sort 排序
     * @param $name 商品名称
     * @param $pnid 一级分类
     * @param $nid 二级分类
     * @param $type 商品类型
     * @param $isrecommand 是否推荐
     * @param $isnew 是否新品
     * @param $ishot 是否热卖
     * @param $isdiscount 是否促销
     * @param $issendfree 是否包邮
     * @param $istime 是否限时卖
     * @param $isnodiscount 是否不参与会员折扣
     * @param $thumb 缩略图
     * @param $images 其他图片集合
     * @param $marketprice 现价
     * @param $productprice 原价
     * @param $costprice 成本价
     * @param $total 库存
     * @param $maxbuy 单次最多购买量
     * @param $sales 已出售数
     * @param $content 商品描述
     * @param $nocommission 是否参与分销
     * @param $hidecommission 显示"我要分销"按钮
     * @param $isreturn 是否订单全返
     * @param $isreturntwo 是否订单双返
     * @param $isreturnqueue 是否排列全返
     * @param $return_appoint_amount 全返分红金额
     * @param $dispatchprice 运费设置
     * @param $status 商品状态
     * @return int|mixed|string
     */
    public function addDocument($shopid, $genlisid, $sort, $name, $pnid, $nid, $type, $isrecommand, $isnew, $ishot, $isdiscount, $issendfree, $istime, $isnodiscount, $thumb, $images = array(), $marketprice, $productprice, $costprice, $total, $maxbuy, $sales, $content, $nocommission, $hidecommission, $isreturn, $isreturntwo, $isreturnqueue, $return_appoint_amount, $dispatchprice, $status) {
        $data = array(
            'shopid'=>$shopid,
            'genlisid'=>$genlisid,
            'sort'=>$sort,
            'name'=>$name,
            'pnid'=>$pnid,
            'nid'=>$nid,
            'type'=>$type,
            'isrecommand'=>$isrecommand,
            'isnew'=>$isnew,
            'ishot'=>$ishot,
            'isdiscount'=>$isdiscount,
            'issendfree'=>$issendfree,
            'istime'=>$istime,
            'isnodiscount'=>$isnodiscount,
            'thumb'=>$thumb,
            'images'=>$images ? arrayJsonToArray($images) : '',
            'marketprice'=>$marketprice,
            'productprice'=>$productprice,
            'costprice'=>$costprice,
            'total'=>$total,
            'maxbuy'=>$maxbuy,
            'sales'=>$sales,
            'content'=>$content,
            'nocommission'=>$nocommission,
            'hidecommission'=>$hidecommission,
            'isreturn'=>$isreturn,
            'isreturntwo'=>$isreturntwo,
            'isreturnqueue'=>$isreturnqueue,
            'return_appoint_amount'=>$return_appoint_amount,
            'dispatchprice'=>$dispatchprice,
            'status'=>$status
        );
        if($this->create($data)){
            $productid = $this->add();
            return $productid ? $productid : 0;
        }else{
            return $this->getError();
        }
    }

    /**
     * 编辑商品
     * @param $id 商品id
     * @param $shopid 商家id
     * @param $sort 排序
     * @param $name 商品名称
     * @param $pnid 一级分类
     * @param $nid 二级分类
     * @param $type 商品类型
     * @param $isrecommand 是否推荐
     * @param $isnew 是否新品
     * @param $ishot 是否热卖
     * @param $isdiscount 是否促销
     * @param $issendfree 是否包邮
     * @param $istime 是否限时卖
     * @param $isnodiscount 是否不参与会员折扣
     * @param $thumb 缩略图
     * @param $images 其他图片集合
     * @param $marketprice 现价
     * @param $productprice 原价
     * @param $costprice 成本价
     * @param $total 库存
     * @param $maxbuy 单次最多购买量
     * @param $sales 已出售数
     * @param $content 商品描述
     * @param $nocommission 是否参与分销
     * @param $hidecommission 显示"我要分销"按钮
     * @param $isreturn 是否订单全返
     * @param $isreturntwo 是否订单双返
     * @param $isreturnqueue 是否排列全返
     * @param $return_appoint_amount 全返分红金额
     * @param $dispatchprice 运费设置
     * @param $status 商品状态
     * @return int|mixed|string
     */
    public function update($id, $shopid, $genlisid, $sort, $name, $pnid, $nid, $type, $isrecommand, $isnew, $ishot, $isdiscount, $issendfree, $istime, $isnodiscount, $thumb, $images = array(), $marketprice, $productprice, $costprice, $total, $maxbuy, $sales, $content, $nocommission, $hidecommission, $isreturn, $isreturntwo, $isreturnqueue, $return_appoint_amount, $dispatchprice, $status) {
        $data = array(
            'id'=>$id,
            'shopid'=>$shopid,
            'genlisid'=>$genlisid,
            'sort'=>$sort,
            'name'=>$name,
            'pnid'=>$pnid,
            'nid'=>$nid,
            'type'=>$type,
            'isrecommand'=>$isrecommand,
            'isnew'=>$isnew,
            'ishot'=>$ishot,
            'isdiscount'=>$isdiscount,
            'issendfree'=>$issendfree,
            'istime'=>$istime,
            'isnodiscount'=>$isnodiscount,
            'thumb'=>$thumb,
            'images'=>$images ? arrayJsonToArray($images) : '',
            'marketprice'=>$marketprice,
            'productprice'=>$productprice,
            'costprice'=>$costprice,
            'total'=>$total,
            'maxbuy'=>$maxbuy,
            'sales'=>$sales,
            'content'=>$content,
            'nocommission'=>$nocommission,
            'hidecommission'=>$hidecommission,
            'isreturn'=>$isreturn,
            'isreturntwo'=>$isreturntwo,
            'isreturnqueue'=>$isreturnqueue,
            'return_appoint_amount'=>$return_appoint_amount,
            'dispatchprice'=>$dispatchprice,
            'status'=>$status,
            'updatetime'=>NOW_TIME
        );
        if($this->create($data)){
            $this->save();
            return 1;
        }else{
            return $this->getError();
        }
    }

    /**
     * 删除商品
     * @param number $id 商品id
     */
    public function remove($id) {
        return $this->delete($id);
    }

    /**
     * 商品上架、下架
     * @param $id 商品id
     * @param $status 商品状态
     * @return bool
     */
    public function setDocumentStatus($id, $status) {
        return $this->where("id='{$id}'")->setField('status', $status ? 0 : 1);
    }

    /**
     * 设置产品的属性
     * @param $id 产品id
     * @param $property 产品属性
     * @param $value 产品属性的值
     */
    public function setDocumentProperty($id, $property, $value) {
        return $this->where("id='{$id}'")->setField($property, $value ? 0 : 1);
    }
}