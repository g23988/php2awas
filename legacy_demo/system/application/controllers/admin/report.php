<?php
/**
 * 後台 - 每日營業報表
 *
 * 2010/08/10 會計要求加未稅金額
 *
 * @author	Kevin
 */
class Report extends Controller {

	function Report()
	{
		parent::Controller();
		$this->load->model('order_model');
	}

	/**
	 * /admin/report/daily/2011-06-30
	 */
	function daily($date = '')
	{
		if ($date == '')
		{
			$date = date('Y-m-d');
		}

		$data['title']  = '每日營業報表';
		$data['date']   = $date;
		$data['orders'] = $this->order_model->daily_report($date);

		$this->load->view('admin/header', $data);
		$this->load->view('admin/report', $data);
		$this->load->view('admin/footer');
	}

	function index()
	{
		$this->daily();
	}
}

/* End of file report.php */
/* Location: ./system/application/controllers/admin/report.php */
