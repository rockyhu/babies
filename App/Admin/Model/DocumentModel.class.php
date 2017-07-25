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
    public function ajaxlistDocument($draw, $search_value, $start, $length, $status, $shuxing, $nnid, $nid, $order) {

        if(isNumeric($status) && $status != -1) $map['a.status'] = $status;
        if(!empty($shuxing)) $map['a.'.$shuxing] = 1;
        if(!empty($nnid) && $nnid != -1) $map['a.nnid'] = $nnid;
        if(!empty($nid) && $nid != -1) $map['a.nid'] = $nid;

        if(!empty($order) && $order != '默认排序') {
            $ordermap['a.'+$order] = 'DESC';
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
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.nnid=c.id'))
                ->field('a.id,a.name,a.thumb,a.nid,a.status,a.create,a.isrecommand,a.isgun,a.ishuan,a.istop,a.readcount,b.text,c.text as ntext')
                ->where($map)
                ->limit(intval($start), intval($length))
                ->order($ordermap)->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this
                ->join(array(
                    'a LEFT JOIN __DOCUMENT_NAV__ b ON a.nid=b.id',
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.nnid=c.id'))
                ->where($map)->count();
        }else{
            $obj = $this
                ->join(array(
                    'a LEFT JOIN __DOCUMENT_NAV__ b ON a.nid=b.id',
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.nnid=c.id'))
                ->field('a.id,a.name,a.thumb,a.nid,a.status,a.create,a.isrecommand,a.isgun,a.ishuan,a.istop,a.readcount,b.text,c.text as ntext')
                ->where($map)
                ->limit(intval($start), intval($length))
                ->order($ordermap)->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this
                ->join(array(
                    'a LEFT JOIN __DOCUMENT_NAV__ b ON a.nid=b.id',
                    'LEFT JOIN __DOCUMENT_NAV__ c ON b.nnid=c.id'))
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
            //商品分类
            $value['ntext_html'] = $value['ntext'] ? '<span class="text-danger">['.$value['ntext'].']</span> <span class="text-info">['.$value['text'].']</span> ' : '<span class="text-info">['.$value['text'].']</span> ';
            //商品属性
            $obj[$key]['shuxing_html'] = '<label class="label label-default cursor property '.($value['istop'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="istop" data-value="'.$value['istop'].'">置顶</label> - <label class="label label-default cursor property '.($value['isrecommand'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="isrecommand" data-value="'.$value['isrecommand'].'">推荐</label> - <label class="label label-default cursor property '.($value['isgun'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="isgun" data-value="'.$value['isgun'].'">滚动</label> - <label class="label label-default cursor property '.($value['ishuan'] ? 'label-info' : '').'" data-id="'.$value['id'].'" data-property="ishuan" data-value="'.$value['ishuan'].'">幻灯</label>';
            $obj[$key]['create'] = date('Y/m/d H:i',$value['create']);
            //商品状态
            $obj[$key]['status'] = $value['status'] ? '<label class="label label-info cursor product-status" data-status="'.$value['status'].'" data-id="'.$value['id'].'">已发布</label>' : '<label class="label label-default cursor product-status" data-status="'.$value['status'].'" data-id="'.$value['id'].'">待发布</label>';
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
                '<div style="text-align: left;"><span style="color: #000;">'.$value['ntext_html'].'<br><span class="title" style="text-decoration: underline">'.$value['name'].'</span><br><div style="padding-top:10px;">'.$obj[$key]['shuxing_html'].'</div></div>',
                $obj[$key]['readcount'],
                $obj[$key]['status'],
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
            ->field('id,name,thumb,keyword,description,info,content,isrecommand,isgun,ishuan,istop,sort,readcount,nnid,nid,status,create')
            ->where($map)
            ->find();
        if($oneProduct) {
            $oneProduct['content'] = htmlspecialchars_decode($oneProduct['content']);
        }
        return $oneProduct;
    }

    /**
     * 添加文档
     * @param $sort
     * @param $name
     * @param $nnid
     * @param $nid
     * @param $istop
     * @param $isrecommand
     * @param $isgun
     * @param $ishuan
     * @param $thumb
     * @param $keyword
     * @param $description
     * @param $info
     * @param $content
     * @param $readcount
     * @param $status
     * @return int|mixed|string
     */
    public function addDocument($sort, $name, $nnid, $nid, $istop, $isrecommand, $isgun, $ishuan, $thumb, $keyword, $description, $info, $content, $readcount, $status) {
        $data = array(
            'sort'=>$sort,
            'name'=>$name,
            'nnid'=>$nnid,
            'nid'=>$nid,
            'istop'=>$istop,
            'isrecommand'=>$isrecommand,
            'isgun'=>$isgun,
            'ishuan'=>$ishuan,
            'thumb'=>$thumb,
            'keyword'=>$keyword,
            'description'=>$description,
            'info'=>$info,
            'content'=>$content,
            'readcount'=>$readcount,
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
     * 编辑文档
     * @param $id
     * @param $sort
     * @param $name
     * @param $nnid
     * @param $nid
     * @param $istop
     * @param $isrecommand
     * @param $isgun
     * @param $ishuan
     * @param $thumb
     * @param $keyword
     * @param $description
     * @param $info
     * @param $content
     * @param $readcount
     * @param $status
     * @return int|string
     */
    public function update($id, $sort, $name, $nnid, $nid, $istop, $isrecommand, $isgun, $ishuan, $thumb, $keyword, $description, $info, $content, $readcount, $status) {
        $data = array(
            'id'=>$id,
            'sort'=>$sort,
            'name'=>$name,
            'nnid'=>$nnid,
            'nid'=>$nid,
            'istop'=>$istop,
            'isrecommand'=>$isrecommand,
            'isgun'=>$isgun,
            'ishuan'=>$ishuan,
            'thumb'=>$thumb,
            'keyword'=>$keyword,
            'description'=>$description,
            'info'=>$info,
            'content'=>$content,
            'readcount'=>$readcount,
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