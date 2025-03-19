<?php
/**
 * Gravity Connect // Notion // Add Page Emoji Icon
 *
 * This snippet demonstrates how to add an emoji icon to a Notion page
 * when a form entry is being added to or edited in Notion.
 *
 * Instructions:
 *
 * 1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Modify the filter name to scope as needed.<br>See filter reference for full list of variants: https://gravitywiz.com/documentation/gcn_notion_page_data/
 *
 * 3. Update the $emoji variable to change the icon.
 *
 * References:
 *
 * - GC Notion: https://gravitywiz.com/documentation/gravity-connect-notion/
 * - Notion page POST reference: https://developers.notion.com/reference/post-page
 *
 * @since 1.0-beta-1.9
 */
add_filter( 'gcn_notion_page_data', function( $page_data, $form, $entry, $feed ) {
	/**
	 * Modify the $emoji variable to change the icon.
	 * Note that the Notion API requires a single emoji character.
	 */
	$emoji = '🦚';

	$page_data['icon'] = array(
		'type'  => 'emoji',
		'emoji' => $emoji,
	);

	return $page_data;
}, 10, 4 );
