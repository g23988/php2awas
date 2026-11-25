<?php
/**
 * 商品 API
 *
 * @author	Kevin
 */
class Product extends Controller {

	function Product()
	{
		parent::Controller();
		$this->load->model('product_model');
	}

	/**
	 * 查詢庫存
	 * GET: /api/product/stock/商品ID
	 * 回傳 OK|商品ID|名稱|售價|庫存|狀態
	 */
	function stock($id = 0)
	{
		$product = $this->product_model->get(intval($id));
		if ( ! $product)
		{
			echo 'ERR|E02|商品不存在';
			return;
		}

		echo 'OK|' . $product['id'] . '|' . $product['name'] . '|' . $product['price'] . '|' . $product['stock'] . '|' . $product['status'];
	}
}

/* End of file product.php */
/* Location: ./system/application/controllers/api/product.php */
