<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

$express_lang = array(
	'name'	=>	'顺风快递',
	'sender_name'	=>	'寄件人姓名',
	'sender_company'	=>	'单位名称',
	'sender_address'	=>	'寄件地址',
	'sender_tel'	=>	'联系电话',
	'payment_account'	=>	'月结帐号',
	'app_code'	=>	'查询公司代码',
	'app_code_shunfeng'	=>	'shunfeng',
);
$config = array(
	'sender_name'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //寄件人姓名
	'sender_company'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //单位名称
	'sender_address'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //寄件地址
	'sender_tel'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //联系电话
	'payment_account'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //月结帐号
	'app_code'	=>	array(
		'INPUT_TYPE'	=>	'1',
		'VALUES'	=> 	array('shunfeng')
	), //kuaidi查询的com编码
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Sf';

    /* 名称 */
    $module['name']    = $express_lang['name'];
    
    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $express_lang;
	return $module;
}

// 顺风快递模型
require_once(APP_ROOT_PATH.'system/libs/express.php');
class Sf_express implements express {

	public function get_express_form($order_id,$express_sn)
	{
		$express_module = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."express where class_name = 'Sf'");
		$express_module['config'] = unserialize($express_module['config']);
		$tmpl = $express_module['print_tmpl'];
		
		//取出发货列表
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."deal_order_item as doi on doi.id = dn.order_item_id where dn.notice_sn = '".$express_sn."' and doi.order_id = ".$order_id);
		$goods_name = '';
		$goods_num = 0;
		foreach($list as $k=>$v)
		{
			$goods_name.=$v['sub_name'].",";
			$goods_num+=intval($v['number']);
		}		
		if($goods_name!='')$goods_name = substr($goods_name,0,-1);
		//开始解析基础配置
		$tmpl = str_replace("%SENDER_NAME%",$express_module['config']['sender_name'],$tmpl);
		$tmpl = str_replace("%SENDER_COMPANY%",$express_module['config']['sender_company'],$tmpl);
		$tmpl = str_replace("%SENDER_ADDRESS%",$express_module['config']['sender_address'],$tmpl);
		$tmpl = str_replace("%SENDER_TEL%",$express_module['config']['sender_tel'],$tmpl);
		$tmpl = str_replace("%SENDER_SIGN%",$express_module['config']['sender_name'],$tmpl);
		$tmpl = str_replace("%PAYMENT_ACCOUNT%",$express_module['config']['payment_account'],$tmpl);
		$tmpl = str_replace("%Y%",to_date(intval($list[0]['delivery_time']),"Y"),$tmpl);
		$tmpl = str_replace("%M%",to_date(intval($list[0]['delivery_time']),"m"),$tmpl);
		$tmpl = str_replace("%D%",to_date(intval($list[0]['delivery_time']),"d"),$tmpl);
		$tmpl = str_replace("%T%",to_date(intval($list[0]['delivery_time']),"H:i"),$tmpl);
		
		//开始解析订单相关
		$tmpl = str_replace("%SENDER_GOODS%",$goods_name,$tmpl);
		$tmpl = str_replace("%SENDER_NUMBER%",$goods_num,$tmpl);
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		$order_info['region_lv1_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".intval($order_info['region_lv1']));
		$order_info['region_lv2_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".intval($order_info['region_lv2']));
		$order_info['region_lv3_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".intval($order_info['region_lv3']));
		$order_info['region_lv4_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".intval($order_info['region_lv4']));
		
		$address = $order_info['region_lv1_name'].$order_info['region_lv2_name'].$order_info['region_lv3_name'].$order_info['region_lv4_name'].$order_info['address'];
		
		$tmpl = str_replace("%RECIEVER_NAME%",$order_info['consignee'],$tmpl);
		$tmpl = str_replace("%RECIEVER_TO%",$order_info['region_lv3_name'],$tmpl);
		$tmpl = str_replace("%RECIEVER_ADDRESS%",$address,$tmpl);
		$tmpl = str_replace("%RECIEVER_TEL%",$order_info['mobile'],$tmpl);
		
		
		return $tmpl;
		
	}

}
?>