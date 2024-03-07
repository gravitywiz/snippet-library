<?php
/**
 * Gravity Perks // Entry Blocks // Entries Table Block: Show Entry Count
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Add a count of entries before and after the table.
 */

/**
 * @param string $block_content The block content.
 * @param array $block The full block, including name and attributes.
 * @param WP_Block $instance The block instance.
 *
 * @return string
 */
function gpeb_show_entry_count( $block_content, $block, $instance ) {
	// Only show if the block name is "gp-entry-blocks/entries-table"
	if ( $instance->name !== 'gp-entry-blocks/entries-table' ) {
		return $block_content;
	}

	// Ensure that GF_Queryer exists.
	if ( ! class_exists( 'GP_Entry_Blocks\GF_Queryer' ) ) {
		return $block_content;
	}

	$queryer = GP_Entry_Blocks\GF_Queryer::attach( $instance->context );

	// Display the count before and after the table.
	$entry_label = _n( 'entry', 'entries', $queryer->total_count, 'gp-entry-blocks' );
	$count       = '<div class="gpeb-entry-count"><span>' . $queryer->total_count . '</span> ' . $entry_label . ' found.</div>';

	// If you want it to show before the table
	// return $count . $block_content;

	// If you want it to show after the table
	// return $block_content . $count;

	// Show before and after the table
	return $count . $block_content . $count;
}

add_filter( 'render_block', 'gpeb_show_entry_count', 10, 3 );
