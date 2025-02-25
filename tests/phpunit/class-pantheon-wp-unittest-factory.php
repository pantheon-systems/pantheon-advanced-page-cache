<?php
/**
 * Custom factory class to avoid PHP 8.0 deprecation warnings.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

// phpcs:disable Squiz.Commenting.VariableComment.Missing
// phpcs:disable Squiz.Commenting.FunctionComment.Missing

/**
 * Factory for Pantheon_Advanced_Page_Cache_Testcase setup
 */
class Pantheon_WP_UnitTest_Factory extends WP_UnitTest_Factory {
	public $product_category;

	public function __construct() {
		parent::__construct();
		$this->product_category = new WP_UnitTest_Factory_For_Term( $this, 'product_category' );
	}
}
