<?php

class oauth extends model
{
	public function getConfig($type)
	{
		$where = "`type` = '" . $type . "'";
		return $this->db->table('oauth')->where($where)->get();
	}
}

?>