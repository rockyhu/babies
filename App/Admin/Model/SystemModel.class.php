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
	    $system = $this->field('id,merchantpay,resellerpay,minicashout,cashoutfee,minitransfer,epursefee,gouwubifee,shutdownstate,shutdowntitle,shutdowncontent')->where("id=1")->find();
	    return $system;
	}

    /**
     * 获取系统参数
     */
    public function getSystem() {
        $system = $this->field('id,merchantpay,resellerpay,minicashout,cashoutfee,minitransfer,epursefee,gouwubifee,shutdownstate,shutdowntitle,shutdowncontent')->where("id=1")->find();
        $systemconfig = [];//系统配置信息
        if($system) {
            $systemconfig = array(
                'cashout'=>array(
                    'minicashout'=>$system['minicashout'],
                    'cashoutfee'=>$system['cashoutfee']
                ),
                'merchantpay'=>$system['merchantpay'],
                'resellerpay'=>$system['resellerpay'],
                'epursefee'=>$system['epursefee']/100,
                'gouwubifee'=>$system['gouwubifee']/100,
                'minitransfer'=>$system['minitransfer'],
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
     * @param $id id
     * @param $merchantpay 招商员资格费用
     * @param $resellerpay 分销商资格费用
     * @param $minitransfer 最低转账金额
     * @param $minicashout 最低提现金额
     * @param $cashoutfee 提现手续费
     * @param $epursefee 佣金的百分比进入钱包余额
     * @param $gouwubifee 佣金的百分比进入消费积分
     * @param $shutdownstate 系统是否停用
     * @param $shutdowntitle 维护页面标题
     * @param $shutdowncontent 维护页面内容提示
     * @return int
     */
	public function setSystem($id, $merchantpay, $resellerpay, $minitransfer, $minicashout, $cashoutfee, $epursefee, $gouwubifee, $shutdownstate, $shutdowntitle, $shutdowncontent) {
	    $data = array(
	        'id'=>$id,
            'merchantpay'=>$merchantpay,
            'resellerpay'=>$resellerpay,
	        'minitransfer'=>$minitransfer,
            'minicashout'=>$minicashout,
            'cashoutfee'=>$cashoutfee,
            'epursefee'=>$epursefee,
            'gouwubifee'=>$gouwubifee,
			'shutdownstate'=>$shutdownstate == 1 ? 1 : 0,
	        'shutdowntitle'=>$shutdowntitle,
	        'shutdowncontent'=>$shutdowncontent,
	        'create'=>NOW_TIME
	    );
	    $this->save($data);
	    return 1;
	}
	
}