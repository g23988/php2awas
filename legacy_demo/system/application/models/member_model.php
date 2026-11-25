<?php
class Member_model extends Model {

	function Member_model()
	{
		parent::Model();
	}

	function get($id)
	{
		$sql = "SELECT * FROM member WHERE id = " . $id;
		$query = $this->db->query($sql);

		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		return $query->row_array();
	}

	function add_points($id, $point)
	{
		$sql = "UPDATE member SET points = points + " . $point . " WHERE id = " . $id;
		$this->db->query($sql);
	}
}

/* End of file member_model.php */
/* Location: ./system/application/models/member_model.php */
