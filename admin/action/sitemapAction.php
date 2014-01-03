<?php


require Action('common');

class sitemapAction extends commonAction
{
	public function index()
	{
		$this->view['data'] = $this->model('sitemap')->getconfig();
		$this->view['changefreq'] = $this->model('sitemap')->changefreq();
		$this->view['host'] = $_SERVER['HTTP_HOST'];
		$this->view['title'] = lang('Sitemap');
		$this->view('sitemap/index.html');
	}
	
	public function update()
	{
		$setting = $this->model('sitemap')->getconfig();
		$data = $_POST['data'];
		foreach($data as $k=>$v){
			if(isset($setting[$k])){
				$this->db->table('sitemap')->where("name='$k'")->update(array('val' => $v));
			}
		}
		
		if($_POST['generate'] == 1){
			$this->model('sitemap')->generate();
		}
		
		$this->ok('edit_success', url('sitemap/index'));
	}
}

?>