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
class output extends output_root {

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
	 * Output a submit button for the activate/deactivate action
	 *
	 * @since 1.1.0
	 *
	 * @return string Button HTML
	 */
	public function submit_button($class_name = 'add-new-h2') {

		if ( ! $this->is_license_valid() ) {
			$license_action = 'activate';
			$value = __( 'Activate License', 'easy-pods' );
		}else{
			$license_action = 'deactivate';
			$value = __( 'Deactivate License', 'easy-pods' );
		}

		return $this->button_html( $license_action, $value, $class_name );


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
	protected function button_html( $license_action, $button_value, $class_name ) {
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


		return sprintf( '%1s<input type="submit" id="code-submit" class="wp-baldrick %2s"  %3s value="%4s"/>',
			$nonce_field,
			esc_attr( $class_name ),
			esc_attr( $atts_html ),			
			ucfirst( $button_value )
		);

	}


}
