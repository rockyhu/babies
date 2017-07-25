<?php
namespace Admin\Model;
use Think\Model;

class DocumentNavModel extends Model{
	
    //自动验证
    protected $_validate = array(
        //-1,产品分类名称长度不合法！
        array('text','{2,20}',-1,self::EXISTS_VALIDATE,'length'),
        //-4,名称被占用
        array('text', '', -4, self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT)
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
	public function ajaxlistDocumentNav($draw, $search_value, $start, $length) {
	    //总记录数
	    $recordsTotal = $this->count();
	     
	    //存在搜索条件
	    if(strlen($search_value)>0){
	        $obj = $this
                ->join(array('a LEFT JOIN __DOCUMENT_NAV__ b ON a.nnid=b.id'))
                ->field('a.id,a.text,a.sort,a.nnid,a.kind,a.isshow,a.create,b.text as ntext')
                ->where("a.text LIKE '%{$search_value}%' OR b.text LIKE '%{$search_value}%'")
                ->limit(intval($start), intval($length))
                ->order(array('a.sort'=>'ASC'))
                ->select();
	        //条件过滤后记录数 必要
	        $recordsFiltered = $this
                ->join(array('a LEFT JOIN __DOCUMENT_NAV__ b ON a.nnid=b.id'))
                ->where("a.text LIKE '%{$search_value}%' OR b.text LIKE '%{$search_value}%'")
                ->count();
	    }else{
	        $obj = $this
                ->join(array('a LEFT JOIN __DOCUMENT_NAV__ b ON a.nnid=b.id'))
                ->field('a.id,a.text,a.kind,a.sort,a.nnid,a.isshow,a.create,b.text as ntext')
                ->limit(intval($start), intval($length))
                ->order(array('a.sort'=>'ASC'))
                ->select();
	        $recordsFiltered = $recordsTotal;
	    }
	
	    $list = [];//返回数组
	    foreach ($obj as $key=>$value) {
            //分类类型
            $obj[$key]['kind'] = $value['kind'] ? '<label class="label label-success">多篇</label>' : '<label class="label label-warning">单篇</label>';
            //是否显示
            $obj[$key]['isshow'] = $value['isshow'] ? '<label class="label label-success">是</label>' : '<label class="label label-warning">否</label>';
            //是否显示
	        $obj[$key]['create'] = date('Y/m/d H:i:s', $value['create']);

            //权限处理
            $btn_html = '';
            if(in_array('DocumentNav/add', session('pageNavDos')) && !$value['nnid']) {
                $btn_html .= '<a href="'.U("DocumentNav/add",array('id'=>$value['id'])).'" class="btn btn-xs btn-primary" title="添加子分类"><i class="ion-ios-plus-outline"></i></a> ';
            }
            if(in_array('DocumentNav/edit', session('pageNavDos'))) {
                $btn_html .= '<a href="'.U("DocumentNav/edit",array('id'=>$value['id'])).'" class="btn btn-xs btn-primary" title="修改"><i class="ion-ios-compose-outline"></i></a> ';
            }
            if(in_array('DocumentNav/remove', session('pageNavDos'))) {
                $btn_html .= '<a href="javascript:void(0);" class="btn btn-xs btn-primary del-btn" data-id="'.$value['id'].'" title="删除"><i class="ion-ios-close-outline"></i></a>';
            }

	        $row = array(
	            $key+1,
                $obj[$key]['id'],
	            $obj[$key]['text'],
                $obj[$key]['kind'],
	            $obj[$key]['sort'],
                $obj[$key]['ntext'],
                $obj[$key]['isshow'],
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
     * 获取导航列表
     * @return mixed
     */
    public function getNav() {
        return $this->field('id,text')->order('sort ASC')->select();
    }

    /**
     * 获取导航列表
     * @return mixed
     */
    public function getMainNav() {
        $navlist = $this->field('id,text')->where("nnid=0")->order('sort ASC')->select();
        $select = array(//设置默认选中的菜单
            array(
                'id'=>-1,
                'text'=>'主分类'
            )
        );
        return array_merge($select, $navlist);
    }

    public function getAllDocumentNavList() {
        $navlist = $this->field('id,text,nnid')->order('sort ASC')->select();
        $mainNav = [];//主分类数组
        if($navlist) {
            //筛选出主分类
            foreach ($navlist as $key=>$value) {
                if($value['nnid'] == 0) $mainNav[] = [
                    'id'=>$value['id'],
                    'text'=>$value['text'],
                    'child'=>[]
                ];
            }
            //筛选出主分类下的子分类
            foreach ($navlist as $key=>$value) {
                if($value['nnid']) {
                    foreach ($mainNav as $k=>$v) {
                        if($value['nnid'] == $v['id']) {
                            $mainNav[$k]['child'][] = [
                                'id'=>$value['id'],
                                'text'=>$value['text']
                            ];
                        }
                    }
                }
            }
        }
        return json_encode($mainNav);
    }

    /**
     * 商品分类
     * @param int $nnid 默认选中的主分类id
     * @param int $nid 默认选中的子分类id
     */
    public function getAllDocumentNavListForedit($nnid = 0, $nid = 0) {
        $navlist = $this->field('id,text,nnid')->order('sort ASC')->select();
        $mainNav = [];//主分类数组
        if($navlist) {
            //筛选出主分类
            foreach ($navlist as $key=>$value) {
                if($value['nnid'] == 0) $mainNav[] = [
                    'id'=>$value['id'],
                    'text'=>$value['text'],
                    'selected'=>$nnid == $value['id'] ? 'selected' : '',
                    'child'=>[]
                ];
            }
            //筛选出主分类下的子分类
            foreach ($navlist as $key=>$value) {
                if($value['nnid']) {
                    foreach ($mainNav as $k=>$v) {
                        if($value['nnid'] == $v['id']) {
                            $mainNav[$k]['child'][] = [
                                'id'=>$value['id'],
                                'text'=>$value['text'],
                                'selected'=>$nid == $value['id'] ? 'selected' : '',
                            ];
                        }
                    }
                }
            }
            //将子分类json化
            $selectedtwo = [];//选中的一级分类下二级分类列表
            foreach ($mainNav as $k=>$v) {
                if($nnid == $v['id']) $selectedtwo = $v['child'];
                $mainNav[$k]['child'] = json_encode($v['child']);
            }
        }
        return [
            'one'=>$mainNav,
            'two'=>$selectedtwo
        ];
    }

	//获取一个产品分类
	public function getOneDocumentNav($id) {
	    return $this
            ->join(array('a LEFT JOIN __DOCUMENT_NAV__ b ON a.nnid=b.id'))
            ->field('a.id,a.text,a.content,a.nnid,a.sort,a.kind,a.isshow,a.create,b.text as ntext')->where("a.id='{$id}'")->find();
	}
	
	//添加产品分类
	public function addDocumentNav($nnid, $text, $content, $kind, $isshow) {
	    $data = array(
	        'text'=>$text,
            'content'=>$content,
            'nnid'=>!empty($nnid) ? $nnid : 0,
            'kind'=>$kind,
            'isshow'=>$isshow,
            'create'=>NOW_TIME
	    );
	    if($this->create($data)){
	        $nnid = $this->add();
	        //更新sort
	        if($nnid>0) $this->where(array('id'=>$nnid))->setField('sort',$nnid);
	        return $nnid ? $nnid : 0;
	    }else{
	        return $this->getError();
	    }
	}
	
	//编辑产品分类
	public function update($id, $nnid, $text, $sort, $content, $kind, $isshow) {
	    $data = array(
	        'id'=>$id,
            'text'=>$text,
            'content'=>$content,
            'nnid'=>!empty($nnid) ? $nnid : 0,
            'kind'=>$kind,
            'isshow'=>$isshow,
	        'sort'=>$sort,
            'create'=>NOW_TIME
	    );
	    if($this->create($data)) {
	        $this->save($data);
	        return 1;
	    }else{
			return $this->getError();
		}
	}
	
	//删除产品分类
	public function remove($id) {
		return $this->delete($id);
	}
	
}