<?php
namespace Admin\Model;
use Think\Model;

class NavModel extends Model{

    //自动验证
    protected $_validate = array(
        //-1,菜单名称长度不合法！
        array('text','{2,20}',-1,self::EXISTS_VALIDATE,'length'),
        //-3,菜单图标长度不合法！
        array('iconCls','2,30',-2,self::EXISTS_VALIDATE,'length'),
        //-4,名称被占用
        array('text', '', -4, self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT)
    );

    /**
     * 获取导航 - 权限分配
     * @return mixed
     */
    public function getAllNavlistforAuthgroup() {
        $allNavlist = $this->field('id,text,url,nid')->where("nid=0")->order('sort ASC')->select();
        $NavDo = D('NavDo');
        foreach($allNavlist as $key=>$value) {
            $subNavlist = $this->field('id,text,url,nid')->where("nid='{$value['id']}'")->order('sort ASC')->select();
            $subtotal = 0;
            foreach($subNavlist as $k=>$v) {
                if($v['text'] == '系统参数' || $v['url'] == 'System/index') {
                    $subNavlist[$k]['navdotext'] = '查看&设置';
                }else {
                    $subNavlist[$k]['navdotext'] = '查看';
                }
                $twoSubNavlist = $NavDo->getSubNavDolist($v['id']);
                $subNavlist[$k]['twoSubNavlist'] = $twoSubNavlist ? $twoSubNavlist : array();
                $subNavlist[$k]['rowcount'] = count($twoSubNavlist)+2;
                $subtotal += $subNavlist[$k]['rowcount'];
            }
            $allNavlist[$key]['rowcount'] = $subtotal + 1;
            $allNavlist[$key]['subNavlist'] = $subNavlist ? $subNavlist : array();
        }
        //print_r($allNavlist);
        return $allNavlist;
    }

    //根据权限获取导航
    public function getRoleNav($ruleids, $group_id) {
        $obj = $this->table('__NAV__ a,__AUTH_RULE__ b')->field('a.id,a.text,a.state,a.url,a.iconCls,a.nid')->where("a.text=b.title AND a.ishide=0 AND b.id IN ({$ruleids})")->order('a.sort ASC')->select();
        if($obj){
            //从权限栏目菜单中筛选出主菜单id
            $ids = [];
            foreach ($obj as $key=>$value){
                if($value['nid']) {
                    if(!in_array($value['nid'], $ids)) $ids[] = $value['nid'];
                }else {
                    if(!in_array($value['id'], $ids)) $ids[] = $value['id'];
                }
            }
            $map['id'] = array('in', implode(',', $ids));
            //查询
            $mainNav = $this->field('id,text,state,url,iconCls')->where($map)->order('sort ASC')->select();
            if($mainNav){
                //获取当前的url
                $c_url = getCurrentUrl();
                $c_id = 0;//当前选中的栏目id
                foreach ($mainNav as $key=>$value){
                    $mainNav[$key]['class'] = '';
                    if(empty($value['url'])){
                        foreach ($obj as $k=>$v) {
                            if($value['id'] == $v['nid']) {
                                if($v['url'] == $c_url) {
                                    $obj[$k]['class'] = 'active';//子栏目选中
                                    $mainNav[$key]['class'] = 'active';//父栏目选中
                                    $c_id = $v['id'];
                                }
                                $obj[$k]['o_url'] = $v['url'];
                                $obj[$k]['url'] = U($v['url']);
                                $mainNav[$key]['subNav'][] = $obj[$k];
                            }else {
                                $obj[$k]['class'] = '';
                            }
                        }
                    }else{
                        $mainNav[$key]['url'] = U($value['url']);
                        $mainNav[$key]['subNav'] = '';
                    }
                }
                //缓存当前页面的菜单操作对当前管理员
                if($c_id) {
                    $urlDosArr = D('GroupRules')->getOneGroupRulesWithManageid($group_id, $c_id);
                    session('pageNavDos', $urlDosArr);
                }
            }
            //print_r($mainNav);
            //缓存公用导航
            $_SESSION['admininit']['mainNav'] = $mainNav;
            return $mainNav;
        }
    }

    //获取菜单导航
    public function getNav() {
        return $this->field('text AS id,text')->where("url<>''")->order('sort ASC')->select();
    }

    /**
     * ajax获取等级数据
     * @param string $draw datatables 获取Datatables发送的参数 必要, 这个值作者会直接返回给前台
     * @param string $order_column 排序的字段下标
     * @param string $order_dir 排序的方式，asc OR desc
     * @param string $search_value 搜索的条件
     * @param string $start 查找开始的地方
     * @param string $length 要查找数据的条数
     */
    public function ajaxlistNav($draw, $search_value, $start, $length) {
        //总记录数
        $recordsTotal = $this->count();

        //存在搜索条件
        if(strlen($search_value)>0){
            $obj = $this
                ->join(array('a LEFT JOIN __NAV__ b ON a.nid=b.id'))
                ->field('a.id,a.text,a.state,a.url,a.iconCls,a.ishide,a.nid,a.sort,b.text AS ntext')
                ->where("a.text LIKE '%{$search_value}%'")->limit(intval($start), intval($length))
                ->order(array('a.nid'=>'ASC','a.sort'=>'ASC'))
                ->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this
                ->join(array('a LEFT JOIN __NAV__ b ON a.nid=b.id'))
                ->where("a.text LIKE '%{$search_value}%'")->limit(intval($start), intval($length))
                ->count();
        }else{
            $obj = $this
                ->join(array('a LEFT JOIN __NAV__ b ON a.nid=b.id'))
                ->field('a.id,a.text,a.state,a.url,a.iconCls,a.ishide,a.nid,a.sort,b.text AS ntext')
                ->limit(intval($start), intval($length))
                ->order(array('a.nid'=>'ASC','a.sort'=>'ASC'))
                ->select();
            $recordsFiltered = $recordsTotal;
        }
        $list = [];//返回数组
        foreach ($obj as $key=>$value) {
            //权限处理
            $btn_html = '';
            if(in_array('Nav/edit', session('pageNavDos'))) {
                $btn_html .= '<a href="'.U("Nav/edit",array('id'=>$value['id'])).'" class="btn btn-xs btn-primary"><i class="ion-ios-compose-outline"></i></a> ';
            }
            if(in_array('Nav/remove', session('pageNavDos'))) {
                $btn_html .= '<a href="javascript:void(0);" class="btn btn-xs btn-primary del-btn" data-id="'.$value['id'].'"><i class="ion-ios-close-outline"></i></a>';
            }

            $obj[$key]['ishide'] = $value['ishide'] ? '<span class="badge bg-red">隐藏</span>' : '<span class="badge bg-green">显示</span>';

            $row = array(
                $key+1,
                $obj[$key]['text'],
                $obj[$key]['state'],
                $obj[$key]['url'],
                $obj[$key]['iconCls'],
                $obj[$key]['ishide'],
                $obj[$key]['sort'],
                $obj[$key]['ntext'],
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

    //获取主菜单导航列表
    public function getListMain($id=0) {
        $map['nid']=0;
        if($id != 0){
            $map['id'] = array('neq',$id);
        }
        $obj = $this->field('id,text')->where($map)->order('sort ASC')->select();
        $select = array(//设置默认选中的菜单
            array(
                'id'=>-1,
                'text'=>'主菜单'
            )
        );
        return array_merge($select,$obj);
    }

    //获取一个栏目
    public function getOneNav($id) {
        return $this->join(array('a LEFT JOIN __NAV__ b ON a.nid=b.id'))->field('a.id,a.text,a.url,a.iconCls,a.ishide,a.nid,a.sort,b.text AS ntext')->where("a.id='{$id}'")->find();
    }

    //添加菜单
    public function addNav($nid, $text, $url, $iconCls, $ishide) {
        $data = array(
            'nid'=>$nid,
            'text'=>$text,
            'url'=>$url,
            'iconCls'=>$iconCls,
            'ishide'=>$ishide
        );
        //当nid不为0时，验证url
        if($nid != -1){
            $data['state'] = 'open';
        }else{
            $data['state'] = 'closed';
        }
        if($this->create($data)){
            $nnid = $this->add();
            //更新sort
            $this->where(array('id'=>$nnid))->setField('sort',$nnid);
            if($nnid>0){
                //子菜单需要新增规则
                if($nid){
                    $name = 'Admin/'.substr($url, 0, strpos($url, '/')+1);
                    $arid = D('AuthRule')->addRule($name,$text);
                    if($arid){
                        //给超级管理员添加权限
                        D('AuthGroup')->updateRole(session('admin.id'), $arid);
                    }
                }
                //清空缓存的公用导航栏目,方便重新请求缓存
                session('admininit.mainNav', null);
            }
            return $nnid ? $nnid : 0;
        }else{
            return $this->getError();
        }
    }

    //编辑菜单
    public function update($id, $nid, $text, $url, $iconCls, $ishide, $sort) {
        $map['id'] = $id;
        $nav = $this->field('id,text,url')->where($map)->find();
        $data = array(
            'id'=>$id,
            'nid'=>$nid,
            'text'=>$text,
            'url'=>$url,
            'iconCls'=>$iconCls,
            'ishide'=>$ishide,
            'sort'=>$sort
        );
        if($this->create($data)) {
            if($nid){
                $data['state'] = 'open';
            }else{
                $data['state'] = 'closed';
            }
            $nid = $this->save($data);
            if($nid>0){
                //当$url不为空时
                if(!empty($url)) {
                    if($nav['text'] != $text || $nav['url'] != $url) {
                        $name = 'Admin/'.substr($url, 0, strpos($url, '/')+1);
                        D('AuthRule')->editRule($name,$text,$nav['text']);
                    }
                }else {
                    //$url为空表示,当前栏目为文件夹,那就不存在AuthRule规则,即删除掉AuthRule表中的相关记录即可
                    D('AuthRule')->remove($nav['text']);
                }
                //清空缓存的公用导航栏目,方便重新请求缓存
                session('admininit.mainNav', null);
            }
            return $nid ? $nid : 0;
        }else{
            return $this->getError();
        }
    }

    //删除栏目
    public function remove($id,$text,$nid) {//关联删除
        //清空缓存的公用导航栏目,方便重新请求缓存
        session('admininit.mainNav', null);
        if(empty($nid)){//删除主菜单
            return $this->delete($id);
        }else{//删除子菜单
            $num = D('AuthRule')->remove($text);
            if($num>0){
                return $this->delete($id);
            }else{
                return 0;
            }
        }
    }

}