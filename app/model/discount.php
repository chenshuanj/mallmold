<?php


class discount extends model
{
	public function get($can_coupon=0)
	{
		$time = time();
		$where = "status=1 and (starttime=0 or starttime<$time) and (endtime=0 or endtime>$time)";
		$list = $this->model('mdata')->table('discount')->where($where)->order('priority asc,id asc')->getlist();
		if(!$list){
			return false;
		}
		
		foreach($list as $v){
			if($can_coupon == 1 && $v['can_coupon'] == 0){
				continue;
			}
			$can_discount = $this->check($v['id']);
			if($can_discount){
				return $v;
			}
		}
		return false;
	}
	
	public function discount_count(array $checkout)
	{
		$can_coupon = $checkout['coupon'] > 0 ? 1 : 0;
		$discount = $this->get($can_coupon);
		if(!$discount){
			return $checkout;
		}
		
		$type = $discount['type'];
		$val = $discount['val'];
		switch($type){
			case 1:
				$discount = round($checkout['subtotal']*($val/100), 2);
				$checkout['discount'] = $discount;
				break;
			case 2:
				$checkout['discount'] = $val;
				break;
			case 3:
				$checkout['shipping'] = 0;
				break;
			case 4:
				$checkout['gift'] = $val;
				break;
		}
		
		$checkout['total'] = $checkout['subtotal']+$checkout['tax']+$checkout['shipping']-$checkout['coupon']-$checkout['discount'];
		return $checkout;
	}
	
	private function check($discount_id)
	{
		$discount = true;
		$set_list = $this->db->table('discount_set')->where("discount_id=$discount_id")->getlist();
		if(!$set_list){
			return $discount;
		}
		
		static $discount_goods = null;
		if($discount_goods == null){
			$discount_goods = $this->model('cart')->getlist();
		}
		
		foreach($set_list as $v){
			$item = trim($v['item']);
			$logic = trim($v['logic']);
			$item_val = trim($v['item_val']);
			switch($item){
				case 'total':
					$total = $this->model('cart')->get_total();
					$discount = $this->checklogic($total, $logic, $item_val);
					break;
				case 'qty':
					$qty = $this->model('cart')->get_qty();
					$discount = $this->checklogic($qty, $logic, $item_val);
					break;
				case 'weight':
					$weight = $this->model('cart')->get_total_weight();
					$discount = $this->checklogic($weight, $logic, $item_val);
					break;
				case 'goods_id':
					foreach($discount_goods as $goods){
						$goods_id = $goods['goods_id'];
						$discount = $this->checklogic($goods_id, $logic, $item_val);
						if($discount == false){
							break;
						}
					}
					break;
				case 'cate_id':
					$goods_ids = array();
					foreach($discount_goods as $goods){
						$goods_ids[] = $goods['goods_id'];
					}
					$cateid_list = $this->db->table('goods_cate_val')
											->where("goods_id in (".implode(',', $goods_ids).")")
											->getlist();
					foreach($cateid_list as $cate){
						$cate_id = $cate['cate_id'];
						$discount = $this->checklogic($cate_id, $logic, $item_val);
						if($discount == false){
							break;
						}
					}
					break;
				case 'group_id':
					$is_login = $this->model('user')->is_login();
					if($is_login){
						$user = $this->model('user')->get();
						$group_id = $user['group_id'];
					}else{
						$group_id = 0;
					}
					$discount = $this->checklogic($group_id, $logic, $item_val);
					break;
				case 'currency':
					$currency = $this->model('common')->current_cur();
					$discount = $this->checklogic($currency, $logic, $item_val);
					break;
			}
			
			if($discount == false){
				break;
			}
			
		}
		return $discount;
	}
	
	private function checklogic($item, $logic, $item_val)
	{
		$conform = false;
		switch($logic){
			case '=':
				if($item == $item_val){
					$conform = true;
				}
				break;
			case '>':
				if($item > $item_val){
					$conform = true;
				}
				break;
			case '>=':
				if($item >= $item_val){
					$conform = true;
				}
				break;
			case '<':
				if($item < $item_val){
					$conform = true;
				}
				break;
			case '<=':
				if($item <= $item_val){
					$conform = true;
				}
				break;
			case '<>':
				if($item != $item_val){
					$conform = true;
				}
				break;
			case 'in':
				$vals = explode(',', $item_val);
				if(in_array($item, $vals)){
					$conform = true;
				}
				break;
		}
		return $conform;
	}
}
?>