<?php


require Action('common');

class cartAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->model('cart')->getlist();
		$this->view['checkout'] = $this->model('checkout')->count_cart_total();
		$this->view['html_title'] = lang('Cart');
		$this->view['map'] = array(array('title' => lang('Cart')));
		$this->view('cart/index.html');
	}
	
	public function add()
	{
		$goods_id = intval($_POST['goods_id']);
		$quantity = intval($_POST['quantity']);
		$option = $_POST['option'];
		
		if(!$goods_id || !$quantity){
			$this->error('Parameter error');
			return;
		}
		
		//check
		$status = $this->model('cart')->check_goods($goods_id, $quantity, $option);
		if($status == 0){
			$this->error('This product is not bought or not exist this product');
			return;
		}elseif($status == -1){
			$this->error('Lack of inventory');
			return;
		}elseif($status == -2){
			$this->error('Please select options');
			return;
		}
		
		//add to cart
		$this->model('cart')->add($goods_id, $quantity, $option);
		
		//Jump
		header('Location: '.url('cart/index'));
	}
	
	public function ajax_update()
	{
		$id = intval($_POST['id']);
		$quantity = intval($_POST['quantity']);
		if(!$id || !$quantity){
			echo 0;
		}else{
			$goods_amount = $this->model('cart')->update($id, $quantity);
			if($goods_amount === false){
				echo 0;
				return false;
			}
			
			$checkout = $this->model('checkout')->count_cart_total();
			$checkout['goods_amount'] = $goods_amount;
			echo json_encode($checkout);
		}
		return null;
	}
	
	public function delete()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('Parameter error');
			return;
		}
		
		$status = $this->model('cart')->delete($id);
		if($status){
			header('Location: '.url('cart/index'));
		}else{
			$this->error('Parameter error');
			return;
		}
	}
	
	
	public function addcoupon()
	{
		$coupon_code = trim($_POST['coupon_code']);
		if(!$coupon_code){
			$this->error('Parameter error');
			return;
		}
		
		$id = $this->model('coupon')->check($coupon_code);
		if($id === false){
			$this->error('Can not find this coupon');
			return;
		}
		if($id == -1){
			$this->error('Wrong coupon code');
			return;
		}
		if($id == 0){
			$this->error('This coupon has expired');
			return;
		}
		
		$this->model('checkout')->save_checkout(array(), $id);
		header('Location: '.url('cart/index'));
	}
}

?>