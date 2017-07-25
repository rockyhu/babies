<?php
namespace Home\Model;
use Think\Model;

/**
 * 通知公告模型
 * @author rockyhu
 *
 */
class NoticeModel extends Model{
	
	/**
	 * 获取通知公告列表 - 按照时间倒序排序
	 * @param string $limit 获取全部还是获取最新的, 'all' OR 'new'
	 * @return string
	 */
	public function getNoticeList($limit = 'all') {
		if($limit == 'all'){
			$list = $this->field('id,title,content,type,readcount,create')->order(array('create'=>'DESC'))->select();
		}else if($limit == 'new'){
			$list = $this->field('id,title,content,type,readcount,create')->order(array('create'=>'DESC'))->limit(6)->select();
		}
		foreach ($list as $key=>$value) {
			$list[$key]['create'] = $limit == 'all' ? date('Y/m/d H:i:s', $value['create']) : date('Y/m/d', $value['create']);
		}
		//print_r($list);
		return $list;
	}
	
	/**
	 * 获取一个通知公告
	 * @param string $noticeid 通知公告id
	 * @return Ambigous 
	 */
	public function getOneNotice($noticeid) {
		$map['id'] = $noticeid;
		$oneNotice = $this->field('id,title,content,type,readcount,create')->where($map)->find();
		if($oneNotice) {
			$oneNotice['create'] = date('Y/m/d H:i:s', $oneNotice['create']);
			//内容格式化
			$oneNotice['content'] = htmlspecialchars_decode($oneNotice['content']);
			//自增
			$this->where("id='{$noticeid}'")->setInc('readcount');
		}
		//print_r($oneNotice);
		return $oneNotice;
	}
	
	/**
	 * 计算未读的通知公告
	 */
	public function getNoReadNotice() {
		return $this->where("readcount=0")->count();
	}
	
}