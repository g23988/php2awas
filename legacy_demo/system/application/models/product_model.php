<?php
class Product_model extends Model {

	function Product_model()
	{
		parent::Model();
	}

	function get($id)
	{
		$this->db->where('id', $id);
		$query = $this->db->get('product');

		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		return $query->row_array();
	}

	function get_all()
	{
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('product');
		return $query->result_array();
	}

	/**
	 * 更新庫存
	 */
	function set_stock($id, $stock)
	{
		$sql = "UPDATE product SET stock = " . $stock . ", updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $id;
		$this->db->query($sql);
	}
}

/* End of file product_model.php */
/* Location: ./system/application/models/product_model.php */
