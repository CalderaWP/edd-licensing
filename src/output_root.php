<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace calderawp\licensing;


abstract class output_root  {
	/**
	 * Object of the main licensing class
	 *
	 * @since 1.1.0
	 *
	 * @var \calderawp\licensing\main|object
	 */
	public $main_licensing_class;

	/**
	 * Constructor for this class.
	 *
	 * @since 1.1.0
	 *
	 * @param \calderawp\licensing\main|object $main_licensing_class The related instance of the main licensing class.
	 */
	function __construct( $main_licensing_class  ) {
		$this->button_baldrick = $button_baldrick;

	}

	/**
	 * Get current, saved license code.
	 *
	 * @since 1.1.0
	 *
	 * @return string|bool The code or false if not set.
	 */
	public function get_license_code() {
		$code = get_option( $this->main_licensing_class->license_key_option_name, false );

		return $code;

	}

	/**
	 * Get status of license code
	 *
	 * @since 1.1.0
	 *
	 * @return string|bool Status of code
	 */
	public function get_license_status() {
		$status = get_option( $this->main_licensing_class->license_status_option_name, false );

		return $status;
	}

	/**
	 * Check if the stored license is valid.
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	public function is_license_valid() {
		$status  = get_option( $this->main_licensing_class->license_status_option_name, false );
		if ( false == $status || 'invalid' == $status ) {
			return false;
		}

		return true;

	}

	/**
	 * Create a nonce field.
	 *
	 * @since 1.1.0
	 *
	 * @return string Form field HTML
	 */
	public function nonce_field() {
		$nonce_field = wp_nonce_field( $this->main_licensing_class->nonce_field, $this->main_licensing_class->nonce_action, true, false );

		return $nonce_field;

	}


}
