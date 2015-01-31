<?php
/**
 * Functions to be used in building licensing UI
 *
 * @package   calderawp\licensing
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

/**
 * @version 1.0.0
 */
namespace calderawp\licensing;

/**
 * Class output
 *
 * @package calderawp\licensing
 */
class output {

	/**
	 * Object of the main licensing class
	 *
	 * @since 1.0.0
	 *
	 * @var \calderawp\licensing\main|object
	 */
	public $main_licensing_class;

	/**
	 * Arguments for a BaldrickJS button
	 *
	 * @var array
	 */
	protected $button_baldrick;

	/**
	 * Constructor for this class.
	 *
	 * @param \calderawp\licensing\main|object $main_licensing_class The related instance of the main licensing class.
	 */
	function __construct( $main_licensing_class, $button_baldrick ) {
		$this->button_baldrick = $button_baldrick;
		$this->main_licensing_class = $main_licensing_class;
	}

	/**
	 * Get current, saved license code.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @return string|bool Status of code
	 */
	public function get_license_status() {
		$status = get_option( $this->main_licensing_class->license_status_option_name, false );

		return $status;
	}

	/**
	 * Output a BaldrickJS submit button for the activate/deactivate action
	 *
	 * @since 1.0.0
	 *
	 * @return string Button HTML
	 */
	public function submit_button() {

		if ( ! $this->is_license_valid() ) {
			$license_action = 'activate';
			$value = __( 'Activate License', 'easy-pods' );
		}else{
			$license_action = 'deactivate';
			$value = __( 'Deactivate License', 'easy-pods' );
		}

		return $this->button_html( $license_action, $value );


	}

	/**
	 * Actually constructs the button HTML
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @param string $license_action The action to take activate|deactivate
	 * @param string $value The value attribue for the button.
	 *
	 * @return string Button HTML
	 */
	protected function button_html( $license_action, $value ) {
		$nonce_field = $this->nonce_field();
		$atts = $this->button_baldrick;
		$atts[ 'code' ] = 'false';
		$atts[ $this->main_licensing_class->nonce_field ] = 'false';
		$atts[ 'license_action' ] = $license_action;
		$atts[ 'action' ] = $this->main_licensing_class->ajax_action;

		foreach( $atts as $att => $value ) {
			if ( is_string( $value ) ) {
				$atts_html[] = 'data-' . $att .'=' . esc_attr( $value );
			}
		}

		$atts_html = implode( ' ', $atts_html );


		return sprintf( '%1s<input type="submit" id="code-submit" class="wp-baldrick add-new-h2"  %2s value="%4s"/>',
			$nonce_field,
			esc_attr( $atts_html ),
			ucfirst( $value )
		);

	}

	/**
	 * Check if the stored license is valid.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @return string Form field HTML
	 */
	public function nonce_field() {
		$nonce_field = wp_nonce_field( $this->main_licensing_class->nonce_field, $this->main_licensing_class->nonce_action, true, false );

		return $nonce_field;

	}

}
