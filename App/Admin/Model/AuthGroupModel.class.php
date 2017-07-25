<?php
namespace Admin\Model;
use Think\Model;
use Think\Auth;

class AuthGroupModel extends Model {
	
	//自动验证
	protected $_validate = array(
		//-1,角色名称长度不合法！
		array('title','/^[^@]{2,20}$/i',-1,self::EXISTS_VALIDATE),//默认为正则验证
		//-2,角色名称被占用
		array('title', '', -2, self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
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
    public function getAjaxAuthGroupList($draw, $search_value, $start, $length) {
        //总记录数
        $recordsTotal = $this->count();
         
        //存在搜索条件
        if(strlen($search_value)>0){
            $obj = $this
                ->field('id,title,status,rules')
                ->where("title LIKE '%{$search_value}%'")
                ->limit(intval($start), intval($length))
                ->order(array('id'=>'ASC'))
                ->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this->where("title LIKE '%{$search_value}%'")->count();
        }else{
            $obj = $this->field('id,title,status,rules')->limit(intval($start), intval($length))->order(array('id'=>'ASC'))->select();
            $recordsFiltered = $recordsTotal;
        }
        
        $list = [];//返回数组

        //判断是否有权限访问
        //session('pageNavDos') : 缓存了当前页面的菜单操作集合

        foreach ($obj as $key=>$value) {
            if($value['status'] == 1) $obj[$key]['status'] = '<span class="badge bg-green">已开启</span>';
		    else $obj[$key]['status'] = '<span class="badge bg-red">已禁止</span>';

            //操作全选判断
            $html = '';
            if(in_array('AuthGroup/edit', session('pageNavDos'))) {
                $html .= '<a href="'.U("AuthGroup/edit",array('id'=>$value['id'])).'" class="btn btn-primary btn-xs"><i class="ion-ios-compose-outline"></i></a> ';
            }
            if(in_array('AuthGroup/remove', session('pageNavDos'))) {
                $html .= '<a href="javascript:void(0);" class="btn btn-primary btn-xs del-btn" data-id="'.$value['id'].'"><i class="ion-ios-close-outline"></i></a>';
            }
            
            $row = array(
                $key+1,
                $obj[$key]['title'],
                $obj[$key]['status'],
                $html
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

    public function getListAll() {
        return $this->field('id,title as text')->select();
    }

    /**
     * 新增角色
     * @param $title 角色名称
     * @param $rules 角色权限集合
     * @return int|mixed|string
     */
	public function addRole($title, $rules) {
        //统计二级栏目标题集合数组
        $titlesArr = [];//标题数组
        $urlsArr = [];//url数组
        $navdos = [];//二级栏目菜单操作集合
        foreach($rules as $key=>$value) {
            if(!empty($value)) {
                //菜单操作集合处理
                foreach ($value as $k=>$v) {
                    if($k == 0) {//二级栏目处理
                        $navArr = explode('||',$v);
                        $titlesArr[] = $navArr[0];
                        $namePrefix = explode('/', $navArr[1])[0];
                        if($namePrefix) $urlsArr[] = 'Admin/'.$namePrefix.'/';
                    }else {//二级栏目菜单操作处理
                        $navArr = explode('||',$v);
                        $navdos[$key][] = [
                            'text'=>$navArr[0],
                            'url'=>$navArr[1]
                        ];
                    }
                }
            }
        }
		//处理rules,获取id
		$map['title'] = array('in', $titlesArr);
		$objArr = M('AuthRule')->field('id,name')->where($map)->select();
		$ids = '';
		foreach ($objArr as $key=>$value){
            if(in_array($value['name'], $urlsArr)) $ids .= $value['id'].',';
		}
		$ids = substr($ids, 0, -1);
		$data = array(
			'title'=>$title,
			'rules'=>$ids,
		);
		if ($this->create($data)) {
			$roleid = $this->add();
            if($roleid>0) {
                //添加菜单操作的权限
                D('GroupRules')->addGroupRules($roleid, $navdos);
            }
			return $roleid ? $roleid : 0;
		} else {
			return $this->getError();
		}
	}

    /**
     * 编辑角色
     * @param $id 角色id
     * @param $title 角色名称
     * @param $rules 角色权限集合
     * @return bool|int|string
     */
	public function editRole($id, $title, $rules) {
        //统计二级栏目标题集合数组
        $titlesArr = [];//标题数组
        $urlsArr = [];//url数组
        $navdos = [];//二级栏目菜单操作集合
        foreach($rules as $key=>$value) {
            if(!empty($value)) {
                //菜单操作集合处理
                foreach ($value as $k=>$v) {
                    if($k == 0) {//二级栏目处理
                        $navArr = explode('||',$v);
                        $titlesArr[] = $navArr[0];
                        $namePrefix = explode('/', $navArr[1])[0];
                        if($namePrefix) $urlsArr[] = 'Admin/'.$namePrefix.'/';
                    }else {//二级栏目菜单操作处理
                        $navArr = explode('||',$v);
                        $navdos[$key][] = [
                            'text'=>$navArr[0],
                            'url'=>$navArr[1]
                        ];
                    }
                }
            }
        }
		//处理rules,获取id
		$map['title'] = array('in', $titlesArr);
		$objArr = M('AuthRule')->field('id,name')->where($map)->select();
		$ids = '';
		foreach ($objArr as $key=>$value){
            if(in_array($value['name'], $urlsArr)) $ids .= $value['id'].',';
		}
		$ids = substr($ids, 0, -1);
		$data = array(
			'id'=>$id,
			'title'=>$title,
			'rules'=>$ids
		);
		if($this->create($data)){
			$this->save();
            //更新菜单操作的权限
            D('GroupRules')->addGroupRules($id, $navdos);
			return 1;
		}else{
			return $this->getError();
		}
	}

    /**
     * 获取一个角色
     * @param $id 角色id
     * @return mixed
     */
	public function getOneAuthGroup($id) {
		$map['id'] = $id;
		$OneAuth = $this->field('id,title,rules')->where($map)->find();
        if(!empty($OneAuth['rules'])){
            $rulesArray = explode(',', $OneAuth['rules']);
            $map['id'] = array('in', $rulesArray);
            $titleObj = M('AuthRule')->field('title')->where($map)->select();
            $titleArray = array();
            foreach ($titleObj as $k=>$v){
                $titleArray[] = $v['title'];
            }
            $OneAuth['rules'] = $titleArray;
            //获取角色相关的菜单操作集合
            $navdos = D('GroupRules')->getOneGroupRules($id);
            //将菜单操作转换成数组
            $dosArr = [];//操作数组
            foreach ($navdos as $key=>$value) {
                foreach ($value as $k=>$v) {
                    $dosArr[$key][] = $v['url'];
                }
            }
            $OneAuth['dosurl'] = $dosArr;
        }
		//print_r($OneAuth);
		return $OneAuth;
	}
	
	//为特定的管理员添加或删除权限
	public function updateRole($uid,$rid,$action=true) {
	    $Auth = new Auth();
	    $rules = $Auth->getGroups($uid);
	    if($action){//添加
	       $data['rules'] = $rules[0]['rules'].','.$rid;
	    }else{//删除权限
	        $data['rules'] = strRemove($rules[0]['rules'], $rid);
	    }
	    $map['title'] = $rules[0]['title'];
	    $this->where($map)->setField($data);
	}
	
	//删除权限
	public function remove($id) {
        D('GroupRules')->remove($id);
	    return $this->delete($id);
	}
}