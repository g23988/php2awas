<?php
/**
 * 單元測試 (CI Unit Testing Class)
 * 有空再補
 *
 * @author	Kevin
 */
class Test extends Controller {

	function Test()
	{
		parent::Controller();
		$this->load->library('unit_test');
	}

	function index()
	{
		$this->unit->run(get_member_discount(3, 1000), 150, 'VIP 1000 元折 150');
		$this->unit->run(get_member_discount(1, 5000), 0, '一般會員不打折');
		$this->unit->run(calc_shipping('100', 500), 60, '本島未滿千運費 60');
		$this->unit->run(calc_shipping('880', 5000), 250, '澎湖運費 250');

		echo $this->unit->report();
	}
}

/* End of file test.php */
/* Location: ./system/application/controllers/test.php */
