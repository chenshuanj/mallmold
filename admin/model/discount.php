<?php
/*
*	@discount.php
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited. 
*	http://www.mallmold.com/
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*	
*	If you want to get an unlimited version of the program or want to obtain
*	additional services, please send an email to <service@mallmold.com>.
*/

class discount extends model
{
	public function type()
	{
		return array(
			1 => lang('percentage_of_cart_total'),
			2 => lang('fixed_amount'),
			3 => lang('free_shipping'),
			4 => lang('grant_coupon')
		);
	}
	
	public function set_item(){
		return array(
			'total' => lang('cart_total'),
			'qty' => lang('cart_qty'),
			'weight' => lang('cart_weight'),
			'goods_id' => lang('cart_goods_id'),
			'cate_id' => lang('cart_cate_id'),
			'group_id' => lang('user_group_id'),
			'currency' => lang('currency_type'),
		);
	}
	
	public function set_logic(){
		return array(
			'=' => lang('equal'),
			'>' => lang('greater_than'),
			'>=' => lang('greater_than_or_equal'),
			'<' => lang('less_than'),
			'<=' => lang('less_than_or_equal'),
			'<>' => lang('not_equal'),
			'in' => lang('included_in'),
		);
	}
	
	public function bind(){
		return array(
			'total' => array('>', '>='),
			'qty' => array('>', '>='),
			'weight' => array('>', '>=', '<', '<='),
			'goods_id' => array('=','<>','in'),
			'cate_id' => array('=','<>','in'),
			'group_id' => array('=','<>','in'),
			'currency' => array('=','<>','in'),
		);
	}
	
}
?>