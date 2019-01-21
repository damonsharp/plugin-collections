<?php

namespace Sharp_Plugin_Collections;

/**
 * Class Admin_Settings.
 *
 * Display admin notices.
 */
class Admin_Settings {

	/**
	 * The setting name.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public $setting_name = ;

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
	 * @param string $notice The notice text.
	 * @param string $type   The message type.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->settings = get_option;
		$this->type   = $type;
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
