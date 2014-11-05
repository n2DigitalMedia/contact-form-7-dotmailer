<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Settings
 *
 *
 * @version		1.0.0
 * @category	Admin
 * @author 		n2 Digital Media
 */

/**
 * N2DMCF7_Settings
 */
class N2DMCF7_Settings {

	/**
	 * Output Settings Page
	 */
	public static function output() {

    ?>

    <div class="wrap">
        <h2>Dotmailer Settings</h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'n2dmcf7-settings-group' ); ?>
            <?php do_settings_sections( 'n2dmcf7-dotmailer-settings' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>

    <?php

	}
}