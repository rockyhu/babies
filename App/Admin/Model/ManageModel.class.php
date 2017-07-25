<?php
namespace Admin\Model;
use Think\Model;
use Think\Auth;

class ManageModel extends Model{
	
	//管理员账号自动验证
	protected $_validate = array(
		//-1,账号长度不合法！
	    //array('manager','2,50',-1,self::EXISTS_VALIDATE,'length'),
	    //-1,邮箱格式不正确
	    array('email','email',-1,self::EXISTS_VALIDATE),
		//-2,密码长度不合法！
		array('password','6,30',-2,self::EXISTS_VALIDATE,'length'),
	    //-3,邮箱被占用
	    array('email','',-3,self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT),
	);
	
	//自动完成
	protected $_auto = array(
		array('password','sha1',self::MODEL_BOTH,'function'),
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
    public function getAjaxManageList($draw, $search_value, $start, $length) {
        //总记录数
        $recordsTotal = $this->count();
         
        //存在搜索条件
        if(strlen($search_value)>0){
            $obj = $this->field('id,manager,realname,create,last_login,last_ip')
                ->where("manager LIKE '%{$search_value}%' OR realname LIKE '%{$search_value}%'")
                ->limit(intval($start), intval($length))
                ->order(array('id'=>'ASC'))
                ->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this->where("manager LIKE '%{$search_value}%' OR realname LIKE '%{$search_value}%'")->count();
        }else{
            $obj = $this->field('id,manager,realname,create,last_login,last_ip')->limit(intval($start), intval($length))->order(array('id'=>'ASC'))->select();
            $recordsFiltered = $recordsTotal;
        }
        
        $list = [];//返回数组
        foreach ($obj as $key=>$value) {
            $obj[$key]['create'] = date('Y-m-d H:i',$value['create']);
	        $obj[$key]['last_login'] = date('Y-m-d H:i',$value['last_login']);
	        $obj[$key]['last_ip'] = long2ip($value['last_ip']);
	        	
	        //角色处理
	        $Auth = new Auth();
	        $rules = $Auth->getGroups($value['id']);
	        $obj[$key]['role'] = $rules[0]['title'];

            //权限处理
            $btn_html = '';
            if(in_array('Manage/edit', session('pageNavDos'))) {
                $btn_html .= '<a href="'.U("Manage/edit",array('id'=>$value['id'])).'" class="btn btn-xs btn-primary"><i class="ion-ios-compose-outline"></i></a> ';
            }
            if(in_array('Manage/remove', session('pageNavDos'))) {
                $btn_html .= '<a href="javascript:void(0);" class="btn btn-xs btn-primary del-btn" data-id="'.$value['id'].'"><i class="ion-ios-close-outline"></i></a>';
            }

            $row = array(
                $key+1,
                $obj[$key]['manager'],
                $obj[$key]['realname'],
                $obj[$key]['role'],
                $obj[$key]['create'],
                $obj[$key]['last_login'],
                $obj[$key]['last_ip'],
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
	
	//获取管理员总数
	public function getManageTotal() {
	    return $this->count();
	}
	
	//获取一个管理员
	public function getOneManage($id) {
	    $m['uid'] = $map['id'] = $id;
	    $oneManage = $this->field('id,manager,realname')->where($map)->find();
	    if($oneManage){
	        $AuthGroupAccess = M('AuthGroupAccess');
	        $ma['id'] = $oneManage['role'] = $AuthGroupAccess->where($m)->getField('group_id');
	        $AuthGroup = M('AuthGroup');
	        $oneManage['title'] = $AuthGroup->where($ma)->getField('title');
	    }
	    //print_r($oneManage);
	    return $oneManage;
	}
	
	//验证管理员登录
	public function checkManager($manager, $password) {
		$data = array(
			'manager'=>$manager,
			'password'=>$password
		);
		if($this->create($data)){
			$map['manager'] = $manager;
			$map['password'] = sha1($password);
			$obj = $this->field('id,manager')->where($map)->find();
			if($obj){
                $time = NOW_TIME;
				session('admin',array(
					'id'=>$obj['id'],
					'manager'=>$obj['manager'],
                    'login_expire'=>gmdate('l d F Y H:i:s', strtotime('+2 minute', $time+8*60*60))." GMT",//设置登录过期时间
				));
				//登录成功后写入登录信息
				$update = array(
					'id'=>$obj['id'],
					'last_login'=>$time,
					'last_ip'=>get_client_ip(1)
				);
				$this->save($update);
				//添加管理员登陆记录
                D('ManageLogin')->addManageLogin($obj['id'], $update['last_login'], $update['last_ip']);
				return $obj['id'];
			}else{
				return 0;
			}
		}else{
			return $this->getError();
		}
	}
	
	//新增管理员
	public function addManage($manager, $realname, $password, $role) {
		$data = array(
			'manager'=>$manager,
            'realname'=>$realname,
			'password'=>$password
		);
		if($this->create($data)){
			$mid = $this->add();
			if($mid){
				$data = array(
					'uid'=>$mid,
					'group_id'=>$role
				);
				$AuthGroupAccess = M('AuthGroupAccess');
				$AuthGroupAccess->add($data);
				return $mid;
			}else{
				return 0;
			}
		}else{
			return $this->getError();
		}
	}
	
	//修改管理员
	public function update($id, $realname, $password, $role) {
	    $data = array(
	        'id'=>$id,
            'realname'=>$realname
	    );
	    if(!empty($password)){
	        $data['password'] = $password;
	    }
	   if($this->create($data)){
           if(!empty($password)){
               $data['password'] = sha1($password);
           }
	        $this->save($data);
	        $map['uid'] = $id;
	        $AuthGroupAccess = M('AuthGroupAccess');
	        $AuthGroupAccess->where($map)->setField('group_id',$role);
	        return $id ? $id : 0;
	    }else{
	        return $this->getError();
	    }
	}
	
	//删除管理员
	public function remove($id) {
	    //不能删除当前登录的管理员账号
	    if($id != session('admin.id')){
    	    //先删除管理员对应的角色记录
    	    $AuthGroupAccess = M('AuthGroupAccess');
    	    $map['uid'] = $id;
    	    $agaNum = $AuthGroupAccess->where($map)->delete();
    	    if($agaNum == 1){
    	        return $this->delete($id);
    	    }else{
    	        return 0;
    	    }
	    }else{
	        return 0;
	    }
	}
	
}