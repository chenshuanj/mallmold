<?php


require Action('common');

class currencyAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('currency')->getlist();
		$this->view['title'] = lang('currency');
		$this->view('currency/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('currency')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view('currency/edit.html');
		}else{
			if(!$_POST['name'] || !$_POST['code'] || !$_POST['symbol']){
				$this->error('required_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'code' => trim($_POST['code']),
				'rate' => trim($_POST['rate']),
				'symbol' => trim($_POST['symbol']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('currency')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('currency')->add($data);
			}
			
			$this->ok('edit_success', url('currency/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('currency')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('currency/index'));
	}
}

?>