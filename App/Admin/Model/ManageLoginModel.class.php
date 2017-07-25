<?php
namespace Admin\Model;
use Think\Model;
use Think\Auth;

class ManageLoginModel extends Model{
	
	//管理员登陆日志账号自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();

	/**
     * ajax获取数据
     * @param string $draw datatables 获取Datatables发送的参数 必要, 这个值作者会直接返回给前台
     * @param string $order_column 排序的字段下标
     * @param string $order_dir 排序的方式，asc OR desc
     * @param string $search_value 搜索的条件
     * @param string $start 查找开始的地方
     * @param string $length 要查找数据的条数
     */
    public function getAjaxManageLoginlogList($draw, $search_value, $start, $length) {
        //总记录数
        $recordsTotal = $this
            ->table('__MANAGE_LOGIN__ a,__MANAGE__ b')
            ->where("a.manageid=b.id")
            ->count();
         
        //存在搜索条件
        if(strlen($search_value)>0){
            $obj = $this
                ->table('__MANAGE_LOGIN__ a,__MANAGE__ b')
                ->field('a.manageid,a.loginip,a.logintime,a.loginlocation,b.manager')
                ->where("a.manageid=b.id AND b.manager LIKE '%{$search_value}%'")
                ->limit(intval($start), intval($length))
                ->order(array('a.logintime'=>'DESC'))
                ->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this
                ->table('__MANAGE_LOGIN__ a,__MANAGE__ b')
                ->where("a.manageid=b.id AND b.manager LIKE '%{$search_value}%'")
                ->count();
        }else{
            $obj = $this
                ->table('__MANAGE_LOGIN__ a,__MANAGE__ b')
                ->field('a.manageid,a.loginip,a.logintime,a.loginlocation,b.manager')
                ->where("a.manageid=b.id")
                ->limit(intval($start), intval($length))
                ->order(array('a.logintime'=>'DESC'))
                ->select();
            $recordsFiltered = $this->table('__MANAGE_LOGIN__ a,__MANAGE__ b')->where("a.manageid=b.id")->count();
        }
        
        $list = [];//返回数组
        $Auth = new Auth();
        foreach ($obj as $key=>$value) {
	        $obj[$key]['logintime'] = date('Y-m-d H:i:s',$value['logintime']);
	        $obj[$key]['loginip'] = long2ip($value['loginip']);
	        	
	        //角色处理
	        $rules = $Auth->getGroups($value['manageid']);
	        $obj[$key]['role'] = $rules[0]['title'];
            
            $row = array(
                $key+1,
                $obj[$key]['manager'],
                $obj[$key]['role'],
                $obj[$key]['logintime'],
                $obj[$key]['loginip'],
                $obj[$key]['loginlocation']
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
	
	//新增管理员登陆日志
	public function addManageLogin($managerid, $logintime, $loginip) {
		$data = array(
			'manageid'=>$managerid,
			'logintime'=>$logintime,
		    'loginip'=>$loginip,
		    'loginlocation'=>getPositionCitywithIP()//根据iP地址定位
		);
		if($this->create($data)){
			return $this->add();
		}else{
			return $this->getError();
		}
	}
	
}