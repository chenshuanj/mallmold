<?php


require Action('common');

class pageAction extends commonAction
{
	public function index()
	{
		$page_id = $this->model('urlkey')->getid('page_id');
		if(!$page_id){
			$this->_404();
			return;
		}
		
		$page = $this->model('mdata')->table('pages')->where("id=$page_id")->get();
		if(!$page){
			$this->_404();
			return;
		}
		
		//parse tag
		$page['content'] = $this->parse($page['content']);
		
		$this->view['html_title'] = $page['meta_title'] ? $page['meta_title'] : $page['title'];
		$this->view['meta_description'] = $page['meta_description'];
		$this->view['meta_keywords'] = $page['meta_keywords'];
		
		//$this->view['map'] = array(array('title' => $page['title']));
		$this->view['page'] = $page;
		$this->view('page/index.html');
	}
	
	
	private function parse($str)
	{
		$str = preg_replace("/\{tag\s+(.+?)\}/ies", "\$this->build('\\1');", $str);
		return $str;
	}
	
	private function build($tag)
	{
		$tag = trim(stripslashes($tag));
		if(!$tag)
			return null;
		
		preg_match_all('/\s(.+?)="(|.+?)"/is', ' '.$tag, $rs);
		$args = array();
		foreach($rs[1] as $k=>$v){
			$v = trim($v);
			$args[$v] = trim($rs[2][$k]);
		}
		
		return $this->convert($args);
	}
	
	private function convert($args)
	{
		if(!$args['type']){
			return false;
		}
		
		switch($args['type'])
		{
			case 'goods/list':
				$this->view['tag_list'] = $this->model('goods')->getlist($args['where'], $args['order'], intval($args['limit']));
				$tpl = $args['tpl'] ? $args['tpl'] : 'tag/goods_list.html';
				$content = $this->view($tpl, 0);
				return $content;
			case 'article/list':
				$this->view['tag_list'] = $this->model('article')->get_list(intval($args['cate_id']), intval($args['limit']));
				$tpl = $args['tpl'] ? $args['tpl'] : 'tag/article_list.html';
				$content = $this->view($tpl, 0);
				return $content;
			case 'slider':
				$this->view['tag_slider'] = $this->model('slider')->get($args['id']);
				$tpl = $args['tpl'] ? $args['tpl'] : 'tag/slider.html';
				$content = $this->view($tpl, 0);
				return $content;
			case 'block':
				$tag = $this->model('mdata')->table('block')->where("code='".$args['id']."'")->get();
				return $tag['content'];
		}
	}
}

?>