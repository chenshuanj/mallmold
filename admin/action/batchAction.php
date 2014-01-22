<?php


require Action('common');

class batchAction extends commonAction
{
	public function index()
	{
		$where = '';
		$sku = trim($_POST['sku']);
		if($sku){
			$where = "sku like '%$sku%'";
		}
		
		$total = $this->db->table('goods')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$this->view['list'] = $this->mdata('goods')->where($where)->order('addtime desc')->limit($limit)->getlist();
		$this->view['sku'] = $sku;
		$this->view['title'] = lang('batch_action');
		$this->view('batch/index.html');
	}
	
	public function update()
	{
		$stock = $_POST['stock'];
		$is_sale = $_POST['is_sale'];
		$sort_order = $_POST['sort_order'];
		$status = $_POST['status'];
		
		if(!$stock){
			$this->error('args_error');
		}
		
		foreach($stock as $k=>$v){
			$data = array(
				'stock' => intval($v),
				'is_sale' => intval($is_sale[$k]),
				'sort_order' => intval($sort_order[$k]),
				'status' => intval($status[$k]),
			);
			$this->db->table('goods')->where("goods_id=$k")->update($data);
		}
		
		$this->ok('edit_success', url('batch/index'));
	}
	

}

?>