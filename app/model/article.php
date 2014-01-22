<?php


class article extends model
{
	public $article_fields = 'article_id,title_key_,urlkey,image';
	
	public function get_cate($cate_id)
	{
		$key = 'article_cate_'.$cate_id;
		$cate = $this->cache($key);
		if(!$cate){
			$cate = $this->model('mdata')->table('article_cate')->where("cate_id=$cate_id")->get();
			$this->cache($key, $cate);
		}
		return $cate;
	}
	
	public function cates_list()
	{
		$list = $this->cache('article_cate_list');
		if(!$list){
			$list = $this->model('mdata')
						->table('article_cate')
						->where('status=1')
						->order('sort_order asc')
						->getlist();
			foreach($list as $k=>$v){
				$list[$k]['url'] = $this->model('urlkey')->geturl('cate_id', $v['cate_id'], $v['urlkey']);
			}
			$this->cache('article_cate_list', $list);
		}
		return $list;
	}
	
	public function get_list($cate_id=0, $num=0)
	{
		$key = 'article_list_'.$cate_id;
		$list = $this->cache($key);
		if(!$list){
			$where = 'status=1'.($cate_id ? " and cate_id=$cate_id" : "");
			$limit = ($num > 0 ? $num : '');
			$list = $this->model('mdata')
						->table('article')
						->field($this->article_fields)
						->where($where)
						->order('sort_order asc')
						->limit($limit)
						->getlist();
			foreach($list as $k=>$v){
				$list[$k]['url'] = $this->model('urlkey')->geturl('article_id', $v['article_id'], $v['urlkey']);
			}
			$this->cache($key, $list);
		}
		return $list;
	}
	
	public function get_article($article_id)
	{
		$key = 'article_'.$article_id;
		$article = $this->cache($key);
		if(!$article){
			$article = $this->model('mdata')->table('article')->where("article_id=$article_id")->get();
			$this->cache($key, $article);
		}
		return $article;
	}
}
?>