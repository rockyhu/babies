<?php
namespace Admin\Model;
use Org\Util\String;
use Think\Model;

class QuestionTagsModel extends Model{
    
    //自动验证
    protected $_validate = array();

    /**
     * 添加问题标签
     * @param $name 问题标签名称
     * @return int|mixed|string
     */
    public function addQuestionTags($name) {
        $data = array(
            'name'=>$name
        );
        if($this->create($data)){
            $questiontagsid = $this->add();
            return $questiontagsid ? $questiontagsid : 0;
        }else{
            return $this->getError();
        }
    }

    /**
     * 验证问题标签是否存在,存在返回TRUE,不存在返回FALSE
     * @param $name
     * @return bool
     */
    public function checkQuestionTagsIsEx($name) {
        $tagsId = $this->where("name='{$name}'")->getField('id');
        return $tagsId>0 ? true : false;
    }

    /**
     * 获取系统所有的问题标签
     */
    public function getAllQuestionTags() {
        $list = $this->field('id,name as text,count')->select();
        $newlist = [];
        foreach ($list as $key=>$value) {
            $newlist[] = [
                'id'=>$value['text'],
                'text'=>$value['text'].' ('.$value['count'].')'
            ];
        }
        return $newlist;
    }

    /**
     * 统计标签数组的次数
     * @param array $newtagsArr 编辑后的标签数组
     * @param array $oldtagsArr 编辑前的标签数组
     */
    public function leijiaQuestionTags($newtagsArr = array(), $oldtagsArr = array()) {
        //两个数组求差集
        $addTagsArr = array_diff($newtagsArr, $oldtagsArr);
        foreach ($addTagsArr as $item) {
            $this->where("name='{$item}'")->setInc('count', 1);
        }
        $minsTagsArr = array_diff($oldtagsArr, $newtagsArr);
        foreach ($minsTagsArr as $item) {
            $this->where("name='{$item}'")->setDec('count', 1);
        }
    }

    /**
     * 获取指定标签列表
     * @param $tagstr 标签字符串
     * @return mixed
     */
    public function getQuestionTagsList($tagstr) {
        $map['name'] = array('in', $tagstr);
        return $this->field('name,count')->where($map)->select();
    }

}