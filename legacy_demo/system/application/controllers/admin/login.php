<?php
/**
 * 後台登入
 *
 * @author	Kevin
 */
class Login extends Controller {

	function Login()
	{
		parent::Controller();
		$this->load->model('admin_model');
	}

	function index()
	{
		$data['error'] = '';

		if ($this->input->post('username'))
		{
			$admin = $this->admin_model->login($this->input->post('username'), $this->input->post('password'));
			if ($admin)
			{
				$this->session->set_userdata(array(
					'admin_id'   => $admin['id'],
					'admin_name' => $admin['name'],
					'is_login'   => 1
				));
				redirect('admin/orders');
				return;
			}
			$data['error'] = '帳號或密碼錯誤';
		}

		$this->load->view('admin/login', $data);
	}

	function logout()
	{
		$this->session->sess_destroy();
		redirect('admin/login');
	}
}

/* End of file login.php */
/* Location: ./system/application/controllers/admin/login.php */
