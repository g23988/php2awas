<?php
/**
 * 後台 - 客戶管理
 *
 * @author	Kevin
 */
class Customers extends Controller {

	function Customers()
	{
		parent::Controller();
		$this->_check_login();
	}

	function _check_login()
	{
		if ($this->session->userdata('is_login') != 1)
		{
			redirect('admin/login');
			exit;
		}
	}

	/**
	 * /admin/customers/index/選取的客戶ID
	 */
	function index($selected = 0)
	{
		$sql = "SELECT m.*, COUNT(o.id) AS order_count, SUM(IF(o.status IN (1,2), o.total, 0)) AS order_amount "
			 . "FROM member m LEFT JOIN orders o ON o.member_id = m.id "
			 . "GROUP BY m.id ORDER BY m.id";
		$query = $this->db->query($sql);

		$data['title']     = '客戶管理';
		$data['customers'] = $query->result_array();
		$data['sel']       = FALSE;
		foreach ($data['customers'] as $c)
		{
			if ($selected == 0 || $c['id'] == $selected)
			{
				$data['sel'] = $c;
				break;
			}
		}

		$this->load->view('admin/header', $data);
		$this->load->view('admin/customer_list', $data);
		$this->load->view('admin/footer');
	}
}

/* End of file customers.php */
/* Location: ./system/application/controllers/admin/customers.php */
