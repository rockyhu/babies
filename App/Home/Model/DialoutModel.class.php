<?php
namespace Home\Model;
use Think\Model;

/**
 * 拨出率模型
 * @author rockyhu
 *
 */
class DialoutModel extends Model{
	
	//自动验证
	protected $_validate = array();
	
	//自动完成
	protected $_auto = array();
	
	/**
	 * 1.添加今日拨出率记录，每天零点去结算上一天的拨出率
	 * @param string $shouyitotal 总收入
	 * @param string $zhichutotal 总支出
	 * @param string $yinglitotal 总盈利
	 * @param string $dialout 拨出比
	 * @param string $type 类型，首次购物 OR 二次购物
	 * @return mixed|boolean|unknown|string
	 */
	public function addTodayDialout($shouyitotal, $zhichutotal, $yinglitotal, $dialout, $type, $create = '') {
	    $data = array(
	        'shouyitotal'=>$shouyitotal,
	        'zhichutotal'=>$zhichutotal,
	        'yinglitotal'=>$yinglitotal,
	        'dialout'=>$dialout,
	        'type'=>$type,
	        'create'=>!empty($create) ? $create : strtotime(date('Y-m-d', strtotime('-1 day')))//昨天的时间戳
	    );
	    return $this->add($data);
	}
	
	/**
	 * 2.检验当天的拨出率记录是否已经添加，已统计返回false,没统计返回true
	 * @param string $type 类型，首次购物 OR 二次购物
	 * @param string $time 日期
	 */
	public function checkTodayDialout($type, $time = '') {
	    $yesterdaytime = $time ? $time : strtotime(date('Y-m-d', strtotime('-1 day')));
	    $count = $this->where("type='{$type}' AND `create`='{$yesterdaytime}'")->count();
	    return $count>0 ? false : true;
	}
	
	/**
	 * 3.计算昨天的拨出率 - 首次购物
	 * 步骤：
	 * 1.公式：
	 * 总收入：上一天总的订单金额总额（首次购物）。
	 * 总支出：推荐奖+区域提成（层奖+量奖，实际参与日结佣金的区域提成，去除掉封顶的部分）+领导奖（需要领导奖结算时累计到字段中）+感恩奖（需要感恩奖结算时累计到字段中）+代理分红+投资分红+房车奖励
	 * 总盈利：总收入 - 总支出
	 * 拨出率 = 总支出 / 总收入;
	 */
	public function setTodayDialout() {
	    $yesterdaytime = strtotime(date('Y-m-d', strtotime('-1 day')));//昨天时间戳
	    if(!!$this->checkTodayDialout('首次购物', $yesterdaytime)) {
    	    //1.计算昨天总收入
    	    $Order = M('Order');
    	    $zongshouru = $Order->where("order_state<>'待付款' AND type='首次购物' AND FROM_UNIXTIME(paytime,'%Y%m%d')=FROM_UNIXTIME({$yesterdaytime},'%Y%m%d')")->sum('price');
    	    $zongshouru = is_null($zongshouru) ? 0 : $zongshouru;
    	    
    	    //2.结算昨天总支出
    	    $Jinsday = M('Jinsday');
    	    $todayJins = $Jinsday->field('tuijian,rijie,fuwu,lingdao,ganen,daili,touzi,fangche')->where("`create`='{$yesterdaytime}'")->find();
    	    if($todayJins) {
                $zongzhichu = number_format($todayJins['tuijian']+$todayJins['rijie']+$todayJins['fuwu']+$todayJins['lingdao']+$todayJins['ganen']+$todayJins['daili']+$todayJins['touzi']+$todayJins['fangche'], 3, '.', '');
    	    } else {
    	        $zongzhichu = 0;
    	    }
    	    
    	    //3.昨天总盈利
    	    $zongyingli = numberFormat('-', $zongshouru, $zongzhichu);
    	    
    	    //4.昨天拨出率
    	    $yesterdayDialout = $zongshouru == 0 ? '0.00%' : number_format($zongzhichu*100/$zongshouru, 3, '.', '').'%';
    	    //5.添加日拨出率记录
    	    $this->addTodayDialout($zongshouru, $zongzhichu, $zongyingli, $yesterdayDialout, '首次购物', $yesterdaytime);
    	    echo 'Dialout added ok~';
	    }else {
	        echo 'Dialout added~';
	    }
	}
	
}