<?php

/**
 * Gravity Perks // File Renamer // Enable or Disable on All Forms
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * Allows you to easily enable or disable GPASC on all forms with a single command. Note, that this requires you
 * to use WP CLI.
 *
 * Usage:
 *
 *     To enable:
 *         wp eval-file ./gpasc-enable-or-disable-on-all-forms.php true
 *
 *     To disable:
 *         wp eval-file ./gpasc-enable-or-disable-on-all-forms.php false
 */

global $argv;
$should_enable_arg = $argv[3];

if ( ! $should_enable_arg || ! in_array( $should_enable_arg, array( 'true', 'false', '1', '0' ) ) ) {
	echo PHP_EOL;
	echo '--------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL;
	echo PHP_EOL;
	echo 'Error: please pass "true" or "false" after the filename to enable or disable the plugin on all forms.' . PHP_EOL;
	echo PHP_EOL;
	echo '    Example:' . PHP_EOL;
	echo '    wp eval-file gpasc-enable-on-all-forms.php true' . PHP_EOL;
	echo PHP_EOL;
	echo '--------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL;
	return;
}

$should_enable = false;

if ( in_array( $should_enable_arg, array( 'true', '1' ) ) ) {
	$should_enable = true;
}

$enabled_switch = $should_enable ? '1' : '0';

$forms = GFAPI::get_forms();

foreach ( $forms as $i => &$form ) {
	if ( ! array_key_exists( 'gp-advanced-save-and-continue', $form ) ) {
		continue;
	}

	$form['gp-advanced-save-and-continue']['save_and_continue_enabled']  = $enabled_switch;
	$form['gp-advanced-save-and-continue']['auto_save_and_load_enabled'] = $enabled_switch;

	// only enable this if "true" since we do not want to disable the core save and continue functionality
	// with this script.
	if ( $should_enable === true ) {
		$form['save']['enabled'] = $should_enable;
	}
}

$result = GFAPI::update_forms( $forms );

if ( $result !== true ) {
	echo PHP_EOL;
	echo '--------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL;
	echo 'Failure:' . PHP_EOL;
	echo '    ' . $result->get_error_message();
	echo '--------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL;
} else {
	echo PHP_EOL;
	echo '--------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL;
	echo 'Success!' . PHP_EOL;
	echo '--------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL;
}
