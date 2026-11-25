<?php
/**
 * 後台 - 商品庫存
 *
 * @author	Kevin
 */
class Products extends Controller {

	function Products()
	{
		parent::Controller();
		$this->_check_login();
		$this->load->model('product_model');
	}

	function _check_login()
	{
		if ($this->session->userdata('is_login') != 1)
		{
			redirect('admin/login');
			exit;
		}
	}

	function index()
	{
		$data['title']    = '商品庫存';
		$data['products'] = $this->product_model->get_all();
		$data['msg']      = $this->session->flashdata('msg');

		$this->load->view('admin/header', $data);
		$this->load->view('admin/product_list', $data);
		$this->load->view('admin/footer');
	}

	/**
	 * 修改庫存 POST: id, stock
	 */
	function update()
	{
		$id    = $this->input->post('id');
		$stock = $this->input->post('stock');

		$this->product_model->set_stock($id, $stock);

		$this->session->set_flashdata('msg', '庫存已更新');
		redirect('admin/products');
	}
}

/* End of file products.php */
/* Location: ./system/application/controllers/admin/products.php */
