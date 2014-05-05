<?php
/*
*	@im_exportAction.php
*	Copyright (c)2013 Mallmold Ecommerce(HK) Limited. 
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

require Action('common');

class im_exportAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->db->table('importexport')->getlist();
		$this->view('im_export/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('importexport')->where("id=$id")->get();
				$this->view['data'] = $data;
				$this->view['fields'] = $this->model('importexport')->get_fields($data['model']);
				$this->view['mapping'] = $this->db->table('importexport_mapping')->where("p_id=$id")->order('id asc')->getlist();
			}
			$this->view('im_export/edit.html');
		}else{
			$name = trim($_POST['name']);
			$fields = intval($_POST['fields']);
			$field_name = $_POST['field_name'];
			$mapping_name = $_POST['mapping_name'];
			
			if(!$name){
				$this->error('name_null');
			}
			
			if($fields == 1 && !$field_name){
				$this->error('Please add fields');
			}
			
			$data = array(
				'name' => $name,
				'type' => intval($_POST['type']),
				'format' => intval($_POST['format']),
				'delimiter' => $_POST['delimiter'] ? $_POST['delimiter'] : '; ',
				'model' => trim($_POST['model']),
				'fields' => $fields,
			);
			if($_POST['id']){
				$id = intval($_POST['id']);
				unset($data['model']);
				$this->db->table('importexport')->where("id=$id")->update($data);
			}else{
				$id = $this->db->table('importexport')->where("id=$id")->insert($data);
			}
			
			$this->db->table('importexport_mapping')->where("p_id=$id")->delete();
			if($fields == 1){
				foreach($field_name as $k=>$field){
					if(!$field){
						continue;
					}
					
					$data = array(
						'p_id' => $id,
						'field_name' => $field,
						'mapping_name' => trim($mapping_name[$k]),
					);
					$this->db->table('importexport_mapping')->insert($data);
				}
			}
			
			$this->ok('edit_success', url('im_export/index'));
		}
	}
	
	public function run()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$data = $this->db->table('importexport')->where("id=$id")->get();
		
		$this->view['data'] = $data;
		$this->view['mapping'] = $this->model('importexport')->get_mapping($data);
		$this->view('im_export/run.html');
	}
	
	public function export()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$export = $this->db->table('importexport')->where("id=$id")->get();
		$action = $export['model'];
		$fields = $this->model('importexport')->get_mapping($export);
		$model_data = $this->model('export')->$action($fields);
		$file_data = $this->model('importexport')->file_format($export, $fields, $model_data);
		
		$file_name = $export['name'];
		$this->model('importexport')->out($file_data, $file_name);
	}
	
	public function import()
	{
		$id = intval($_POST['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$upload = $_FILES['importfile'];
		if($upload['name']){
			$import_file = $upload['tmp_name'];
		}else{
			$this->error('Please upload a file');
		}
		
		$import = $this->db->table('importexport')->where("id=$id")->get();
		$import_data = $this->model('importexport')->get_import($import, $import_file);
		
		$action = $import['model'];
		$check = $action.'_check';
		$status = $this->model('import')->$check($import_data);
		
		$this->view['id'] = $id;
		if($status){
			$status = $this->model('import')->$action($import_data);
			if($status){
				$this->view['result'] = $this->model('import')->result;
				$this->view('im_export/result.html');
			}else{
				$this->view['error'] = $this->model('import')->error;
				$this->view('im_export/error.html');
			}
		}else{
			$this->view['error'] = $this->model('import')->error;
			$this->view('im_export/error.html');
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->db->table('importexport')->where("id=$id")->delete();
			$this->db->table('importexport_mapping')->where("p_id=$id")->delete();
		}
		$this->ok('delete_done', url('im_export/index'));
	}
	
	public function ajax_fields()
	{
		$model = trim($_POST['model']);
		$fields = $this->model('importexport')->get_fields($model);
		$html = '<select name="field_name[]"><option value="">-'.lang('select').'-</option>';
		foreach($fields as $field){
			$html .= '<option value="'.$field.'">'.$field.'</option>';
		}
		$html .= '</select>';
		
		echo $html;
	}
}

?>