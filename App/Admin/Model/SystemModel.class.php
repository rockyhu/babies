<?php
namespace Admin\Model;
use Think\Model;

/**
 * 系统参数模型
 * @author rockyhu
 *
 */
class SystemModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();
	
	/**
	 * 获取系统参数
	 */
	public function getOneSystem() {
	    $system = $this->field('id,webname,keywords,description,copyright,beian,shutdownstate,shutdowntitle,shutdowncontent')->where("id=1")->find();
	    return $system;
	}

    /**
     * 获取系统参数
     */
    public function getSystem() {
        $system = $this->field('id,webname,keywords,description,copyright,beian,shutdownstate,shutdowntitle,shutdowncontent')->where("id=1")->find();
        $systemconfig = [];//系统配置信息
        if($system) {
            $systemconfig = array(
                'webname'=>$system['webname'],
                'keywords'=>$system['keywords'],
                'description'=>$system['description'],
                'copyright'=>$system['copyright'],
                'beian'=>$system['beian'],
                'shutdown'=>array(
                    'shutdownstate'=>$system['shutdownstate'],
                    'shutdowntitle'=>$system['shutdowntitle'],
                    'shutdowncontent'=>$system['shutdowncontent']
                )
            );
        }
        return $systemconfig;
    }

    /**
     * 设置站点参数
     * @param $id
     * @param $webname 站点名称
     * @param $keywords 站点关键字
     * @param $description 站点描述
     * @param $copyright 站点版权信息
     * @param $beian 站点备案信息
     * @param $shutdownstate
     * @param $shutdowntitle
     * @param $shutdowncontent
     * @return int
     */
	public function setSystem($id, $webname, $keywords, $description, $copyright, $beian, $shutdownstate, $shutdowntitle, $shutdowncontent) {
	    $data = array(
	        'id'=>$id,
            'webname'=>$webname,
            'keywords'=>$keywords,
	        'description'=>$description,
            'copyright'=>$copyright,
            'beian'=>$beian,
			'shutdownstate'=>$shutdownstate == 1 ? 1 : 0,
	        'shutdowntitle'=>$shutdowntitle,
	        'shutdowncontent'=>$shutdowncontent,
	        'create'=>NOW_TIME
	    );
	    $this->save($data);
	    return 1;
	}
	
}