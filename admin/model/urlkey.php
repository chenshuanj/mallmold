<?php


class urlkey extends model
{
	public function model_keys()
	{
		return array(
			'id' => 'catalog',
			'goods_id' => 'goods',
			'article_id' => 'article',
			'cate_id' => 'list',
			'page_id' => 'page',
		);
	}
	
	public function geturl($item_key, $item_id, $urlkey='')
	{
		$router = &$GLOBALS['router'];
		$type = isset($router['type']) ? $router['type'] : 0;
		$model_keys = $this->model_keys();
		$model = $model_keys[$item_key];
		if($type == 0){
			return "$model/index?$item_key=$item_id";
		}else{
			return "$model/".($urlkey ? $urlkey : "index/$item_key/$item_id");
		}
	}
	
	public function set_goods($id, $urlkey)
	{
		return $this->save('goods', $id, $urlkey);
	}
	
	public function del_goods($id)
	{
		return $this->del('goods', $id);
	}
	
	public function set_goodscate($id, $urlkey)
	{
		return $this->save('catalog', $id, $urlkey);
	}
	
	public function del_goodscate($id)
	{
		return $this->del('catalog', $id);
	}
	
	public function set_article($article_id, $urlkey)
	{
		return $this->save('article', $article_id, $urlkey);
	}
	
	public function del_article($id)
	{
		return $this->del('article', $id);
	}
	
	public function set_articlecate($cate_id, $urlkey)
	{
		return $this->save('list', $cate_id, $urlkey);
	}
	
	public function del_articlecate($id)
	{
		return $this->del('list', $id);
	}
	
	public function set_page($id, $urlkey)
	{
		return $this->save('page', $id, $urlkey);
	}
	
	public function del_page($id)
	{
		return $this->del('page', $id);
	}
	
	private function save($model, $item_id, $urlkey)
	{
		if($urlkey == ''){
			$urlkey = $item_id;
		}
		
		$data = array(
			'model' => $model,
			'item_id' => $item_id,
			'urlkey' => $urlkey,
		);
		$id = $this->db->table('urlkey')->where("model='$model' and item_id=$item_id")->getval('id');
		if($id){
			$this->db->table('urlkey')->where("id=$id")->update($data);
		}else{
			$id = $this->db->table('urlkey')->insert($data);
		}
		return $id;
	}
	
	private function del($model, $item_id)
	{
		$this->db->table('urlkey')->where("model='$model' and item_id=$item_id")->delete();
	}
}
?>