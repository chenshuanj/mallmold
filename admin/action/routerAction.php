<?php

require Action('common');

class routerAction extends commonAction
{
	public function index()
	{
		$warning = $_GET['warning'] ? 1 : 0;
		if($warning){
			$setting = &$this->model('common')->setting();
			$frontend = $setting['frontend'];
			$file = BASE_PATH ."/$frontend/router.php";
			if(!file_exists($file)){
				$this->error('none_router_file');
			}
			
			$router = include $file;
			$data = array();
			$data['type'] = $router['type'];
			$data['default'] = $router['*/*'];
			$data['router'] = array();
			foreach($router as $k=>$v){
				if($k != 'type' && $k != '*/*'){
					$data['router'][] = array(
						'key' => $k,
						'router' => $v,
					);
				}
			}
			$this->view['data'] = $data;
		}
		$this->view['warning'] = $warning;
		$this->view['type'] = 'frontend';
		$this->view['title'] = lang('router');
		$this->view('router/index.html');
	}
	
	public function backend()
	{
		$warning = $_GET['warning'] ? 1 : 0;
		if($warning){
			$file = BASE_PATH ."/admin/router.php";
			if(!file_exists($file)){
				$this->error('none_router_file');
			}
			
			$router = include $file;
			$data = array();
			$data['type'] = $router['type'];
			$data['default'] = $router['*/*'];
			$data['router'] = array();
			foreach($router as $k=>$v){
				if($k != 'type' && $k != '*/*'){
					$data['router'][] = array(
						'key' => $k,
						'router' => $v,
					);
				}
			}
			$this->view['data'] = $data;
		}
		$this->view['warning'] = $warning;
		$this->view['type'] = 'backend';
		$this->view['title'] = lang('router');
		$this->view('router/index.html');
	}
	
	public function update()
	{
		$type = trim($_GET['type']);
		if(!in_array($type, array('frontend','backend'))){
			$this->error('args_error');
		}
		
		$data = array();
		$data['type'] = intval($_POST['type']);
		$data['*/*'] = array(
			'scheme' => trim($_POST['scheme']['default']),
			'host' => trim($_POST['host']['default']),
			'query' => trim($_POST['query']['default']),
			'rewrite' => trim($_POST['rewrite']['default']),
		);
		foreach($_POST['key'] as $k=>$v){
			$v = trim($v);
			if(!$v){
				continue;
			}
			
			if(isset($_POST['check'][$k]['scheme'])){
				$data[$v]['scheme'] = trim($_POST['scheme'][$k]);
			}
			if(isset($_POST['check'][$k]['host'])){
				$data[$v]['host'] = trim($_POST['host'][$k]);
			}
			if(isset($_POST['check'][$k]['query'])){
				$data[$v]['query'] = trim($_POST['query'][$k]);
			}
			if(isset($_POST['check'][$k]['rewrite'])){
				$data[$v]['rewrite'] = trim($_POST['rewrite'][$k]);
			}
		}
		
		if($type == 'frontend'){
			$setting = &$this->model('common')->setting();
			$frontend = $setting['frontend'];
			$file = BASE_PATH ."/$frontend/router.php";
		}else{
			$file = BASE_PATH ."/admin/router.php";
		}
		$str = "<?php\r\nreturn ".var_export($data, true)."\r\n?>";
		$str = preg_replace('/=>\s+\n\s+/s', '=> ', $str);
		$str = preg_replace('/\n[\s]{2,2}/', "\n\t", $str);
		$str = preg_replace('/\n\t[\s]{2,2}/', "\n\t\t", $str);
		
		copy($file, str_replace('.php', '.php.bak', $file));
		file_put_contents($file, $str);
		
		$this->ok('edit_success', url('router/'.($type == 'frontend' ? 'index' : 'backend')));
	}
}
?>