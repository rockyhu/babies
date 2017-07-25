<?php
namespace Admin\Model;
use Think\Model;

class QuestionModel extends Model{
    
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
    public function ajaxlistQuestion($draw, $search_value, $start, $length) {
        //总记录数
        $recordsTotal = $this->count();
         
        //存在搜索条件
        if(strlen($search_value)>0){
            $obj = $this
                ->field('id,title,content,tags,readcount,create')
                ->where("title LIKE '%{$search_value}%' OR tags LIKE '%{$search_value}%'")
                ->limit(intval($start), intval($length))
                ->order(array('create'=>'DESC'))
                ->select();
            //条件过滤后记录数 必要
            $recordsFiltered = $this->where("title LIKE '%{$search_value}%' OR tags LIKE '%{$search_value}%'")->count();
        }else{
            $obj = $this
                ->field('id,title,content,tags,readcount,create')
                ->limit(intval($start), intval($length))
                ->order(array('create'=>'DESC'))
                ->select();
            $recordsFiltered = $recordsTotal;
        }
    
        $list = [];//返回数组
        foreach ($obj as $key=>$value) {
            $obj[$key]['create'] = date('Y/m/d H:i:s', $value['create']);

            //权限处理
            $btn_html = '';
            if(in_array('Question/edit', session('pageNavDos'))) {
                $btn_html .= '<a href="'.U("Question/edit",array('id'=>$value['id'])).'" class="btn btn-xs btn-primary"><i class="ion-ios-compose-outline"></i></a> ';
            }
            if(in_array('Question/remove', session('pageNavDos'))) {
                $btn_html .= '<a href="javascript:void(0);" class="btn btn-xs btn-primary del-btn" data-id="'.$value['id'].'"><i class="ion-ios-close-outline"></i></a>';
            }

            $row = array(
                $key+1,
                $obj[$key]['title'],
                $obj[$key]['tags'],
                $obj[$key]['readcount'],
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
     * 获取单个常见问题
     * @param number $questionid 常见问题id
     * @return string
     */
    public function getOneQuestion($questionid) {
    	$map['id'] = $questionid;
    	$oneQuestion = $this->field('id,title,content,tags')->where($map)->find();
    	if($oneQuestion) {
            $oneQuestion['content'] = htmlspecialchars_decode($oneQuestion['content']);
            $oneQuestion['tags'] = D('QuestionTags')->getQuestionTagsList($oneQuestion['tags']);
        }
    	return $oneQuestion;
    }
	
    /**
     * 新增常见问题
     * @param string $title 常见问题标题
     * @param string $content 常见问题内容
     * @param string $tags 常见问题标签
     * @return Ambigous boolean|string
     */
    public function addQuestion($title, $content, $tags) {
        $data = array(
            'title'=>$title,
            'content'=>$content,
            'tags'=>implode(',', $tags)
        );
        if($this->create($data)){
            $questionid = $this->add();
            if($questionid>0) {
                D('QuestionTags')->leijiaQuestionTags($tags, []);
            }
            return $questionid ? $questionid : 0;
        }else{
            return $this->getError();
        }
    }
    
    /**
     * 更新常见问题
     * @param number $id 常见问题id
     * @param string $title 常见问题标题
     * @param string $content 常见问题内容
     * @param string $tags 常见问题标签
     * @return Ambigous <number, boolean>|string
     */
    public function update($id, $title, $content, $tags) {
    	$data = array(
    		'id'=>$id,
    		'title'=>$title,
    		'content'=>$content,
            'tags'=>implode(',', $tags)
    	);
        //获取目前的标签数组
        $oldtagsStr = $this->where("id='{$id}'")->getField('tags');
    	if($this->create($data)){
    		$questionid = $this->save();
            if($questionid>0) {
                D('QuestionTags')->leijiaQuestionTags($tags, explode(',', $oldtagsStr));
            }
    		return $questionid ? $questionid : 0;
    	}else{
    		return $this->getError();
    	}
    }
    
    /**
     * 删除常见问题
     * @param number $id 常见问题id
     */
    public function remove($id) {
        //先获取到常见问题涉及到的标签
        $tagstr = $this->where("id='{$id}'")->getField('tags');
        D('QuestionTags')->leijiaQuestionTags([], explode(',', $tagstr));
	    return $this->delete($id);
	}
}