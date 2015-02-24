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


class output_a extends output_root {

	/**
	 * Constructor for this class.
	 *
	 * @since 1.1.0
	 *
	 * @param \calderawp\licensing\main|object $main_licensing_class The related instance of the main licensing class.
	 */
	function __construct( $main_licensing_class  ) {
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
			$value = __( 'Activate License', 'easy-pods' );
		}else{
			$value = __( 'Deactivate License', 'easy-pods' );
		}

		return '<input type="submit" id="code-submit" value=" ' . $value . ' "/>';


	}


}
