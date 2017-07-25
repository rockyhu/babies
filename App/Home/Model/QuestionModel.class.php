<?php
namespace Home\Model;
use Think\Model;

/**
 * 常见问题模型
 * @author rockyhu
 *
 */
class QuestionModel extends Model{

    /**
     * 获取问题列表
     * @param $tag 刷选标签
     * @return array
     */
	public function getAllQuestionList($tag = '') {
        if($tag) {
            $questionlist = $this
                ->field('id,title,readcount')
                ->where("FIND_IN_SET('{$tag}',tags)>0")
                ->order(array('readcount'=>'DESC'))
                ->select();
        }else {
            $questionlist = $this
                ->field('id,title,readcount')
                ->order(array('readcount'=>'DESC'))
                ->select();
        }
        $taglist = M('QuestionTags')
            ->field('id,name,count')
            ->where("count>0")
            ->order(array('count'=>'DESC'))
            ->select();
        $newtaglist = [];
        $newtaglist[] = [
            'name'=>'全部',
            'count'=>$this->count(),
            'url'=>U('User/question'),
            'selected'=>empty($tag) ? 'selected' : ''
        ];
        foreach ($taglist as $key=>$value) {
            $url = U('User/question', array('tag'=>$value['name']));
            $newtaglist[] = [
                'name'=>$value['name'],
                'count'=>$value['count'],
                'url'=>$url,
                'selected'=>$tag == $value['name'] ? 'selected' : ''
            ];
        }
        unset($taglist);
        return [
            'questions'=>$questionlist,
            'tags'=>$newtaglist
        ];
    }

    /**
     * 问题列表
     * @param $id 问题id
     * @return mixed
     */
    public function getOneQuestion($id, $userid) {
        $oneQuestion = $this->field('id,title,readcount,tags,content')->where("id='{$id}'")->find();
        if($oneQuestion) {
            $tags = explode(',', $oneQuestion['tags']);
            $tagsArr = [];
            foreach ($tags as $tag) {
                $url = U('User/question', array('tag'=>$tag));
                $tagsArr[] = '<span class="tag border-w"><a href="'.$url.'">'.$tag.'</a></span>';
            }
            $oneQuestion['tags'] = implode(' ', $tagsArr);
            $oneQuestion['content'] = htmlspecialchars_decode($oneQuestion['content']);
            $this->heapUpReadcount($userid, $id);
        }
        return $oneQuestion;
    }

    /**
     * 累积常见问题访问次数
     * @param $userid 用户id
     * @param $questionid 常见问题id
     */
    private function heapUpReadcount($userid, $questionid) {
        if(is_null(cookie('preventRefresh'))) {
            //生成防刷新码
            cookie('preventRefresh', [
                'userid'=>$userid,
                'time'=>time(),
                'questionid'=>$questionid
            ]);
            //更新访问次数
            $this->where("id='{$questionid}'")->setInc('readcount');
        }else {
            //如果是同一个用户，并且时间少于了1分钟，不累计阅读次数，否则可以累积阅读次数
            if(cookie('preventRefresh')['userid'] == $userid && cookie('preventRefresh')['questionid'] == $questionid && (time() - cookie('preventRefresh')['time'] <= 60)) {
                //不做任何操作
            }else {
                //生成防刷新码
                cookie('preventRefresh', [
                    'userid'=>$userid,
                    'time'=>time(),
                    'questionid'=>$questionid
                ]);
                //更新访问次数
                $this->where("id='{$questionid}'")->setInc('readcount');
            }
        }
    }

    /**
     * 设置问题的状态
     * @param $questionid 问题id
     * @param $state 状态，saved OR nosaved
     * @param $userid 用户id
     */
    public function setQuestionSavedState($questionid, $state, $userid) {
        if($state == 'saved') {
            $this->where("id='{$questionid}'")->setInc('saved', 1);
        }else if($state == 'nosaved') {
            $this->where("id='{$questionid}'")->setInc('nosaved', 1);
        }
        return 1;
    }
	
}