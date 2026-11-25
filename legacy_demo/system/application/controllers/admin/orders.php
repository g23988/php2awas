<?php
/**
 * 後台 - 訂單管理
 *
 * 2010/05/03 Kevin: 改成 osCommerce 的樣子，點一筆右邊出現功能
 * 2010/09/14 Kevin: 加收據列印、發票列印 (會計要求)
 *
 * @author	Kevin
 */
class Orders extends Controller {

	function Orders()
	{
		parent::Controller();
		$this->_check_login();
		$this->load->model('order_model');
		$this->load->model('member_model');
		$this->load->library('shop_mailer');
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
	 * 訂單列表 /admin/orders/index/狀態/選取的訂單ID
	 */
	function index($status = 'all', $selected = 0)
	{
		$keyword = $this->input->post('keyword');

		$data['title']    = '訂單管理';
		$data['status']   = $status;
		$data['keyword']  = $keyword;
		$data['orders']   = $this->order_model->search($keyword, $status);
		$data['msg']      = $this->session->flashdata('msg');

		// 沒有選的話預設選第一筆
		$data['sel'] = FALSE;
		foreach ($data['orders'] as $o)
		{
			if ($selected == 0 || $o['id'] == $selected)
			{
				$data['sel'] = $o;
				break;
			}
		}

		$this->load->view('admin/header', $data);
		$this->load->view('admin/order_list', $data);
		$this->load->view('admin/footer');
	}

	function detail($id = 0)
	{
		$order = $this->order_model->get($id);
		if ( ! $order)
		{
			show_error('查無此訂單');
		}

		$data['title']  = '訂單明細';
		$data['order']  = $order;
		$data['items']  = $this->order_model->get_items($id);
		$data['member'] = $this->member_model->get($order['member_id']);

		$this->load->view('admin/header', $data);
		$this->load->view('admin/order_detail', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * 編輯訂單
	 */
	function edit($id = 0)
	{
		$order = $this->order_model->get($id);
		if ( ! $order)
		{
			show_error('查無此訂單');
		}

		if ($this->input->post('receiver'))
		{
			$sql = "UPDATE orders SET "
				 . "receiver = '" . $this->input->post('receiver') . "', "
				 . "phone = '" . $this->input->post('phone') . "', "
				 . "zip = '" . $this->input->post('zip') . "', "
				 . "address = '" . $this->input->post('address') . "', "
				 . "note = '" . $this->input->post('note') . "', "
				 . "status = " . $this->input->post('status')
				 . " WHERE id = " . $id;
			$this->db->query($sql);

			$this->session->set_flashdata('msg', '訂單 ' . $order['order_no'] . ' 已更新');
			redirect('admin/orders/index/all/' . $id);
			return;
		}

		$data['title'] = '編輯訂單';
		$data['order'] = $order;

		$this->load->view('admin/header', $data);
		$this->load->view('admin/order_edit', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * 刪除訂單
	 */
	function delete($id = 0)
	{
		$order = $this->order_model->get($id);
		if ($order)
		{
			$this->db->query("DELETE FROM order_item WHERE order_id = " . $id);
			$this->db->query("DELETE FROM orders WHERE id = " . $id);
			$this->session->set_flashdata('msg', '訂單 ' . $order['order_no'] . ' 已刪除');
		}
		redirect('admin/orders');
	}

	/**
	 * 出貨
	 */
	function ship($id = 0)
	{
		$order = $this->order_model->get($id);
		if ($order && $order['status'] == 1)
		{
			$this->order_model->set_shipped($id);

			$member = $this->member_model->get($order['member_id']);
			$this->shop_mailer->order_shipped($member, $order);
		}

		redirect('admin/orders/detail/' . $id);
	}

	/**
	 * 收據列印 (出貨單) - popup
	 */
	function receipt($id = 0)
	{
		$data['order']  = $this->order_model->get($id);
		$data['items']  = $this->order_model->get_items($id);
		$data['member'] = $this->member_model->get($data['order']['member_id']);
		$this->load->view('admin/print_receipt', $data);
	}

	/**
	 * 發票列印 (二聯式) - popup
	 */
	function invoice($id = 0)
	{
		$data['order']  = $this->order_model->get($id);
		$data['items']  = $this->order_model->get_items($id);
		$data['member'] = $this->member_model->get($data['order']['member_id']);
		$this->load->view('admin/print_invoice', $data);
	}
}

/* End of file orders.php */
/* Location: ./system/application/controllers/admin/orders.php */
