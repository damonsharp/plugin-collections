<?php

namespace Sharp_Plugin_Collections;

/**
 * Class Admin_Settings.
 *
 * Display admin notices.
 */
class Admin_Settings {

	/**
	 * The settings.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->type     = $type;
		$this->display_notice();
	}

	/**
	 * The displayed notice HTML/message.
	 *
	 * @since 1.0.0
	 */
	public function display_notice() {
		?>
		<div class="notice notice-<?php echo esc_attr( $this->type ); ?> is-dismissible">
			<p><?php esc_html_e( sprintf( '%s', $this->notice ), 'dwspc' ); ?></p>
		</div>
		<?php
	}

}
