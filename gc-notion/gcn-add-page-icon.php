<?php
/**
 * Gravity Connect // Notion // Add Page Icon
 * https://gravitywiz.com/documentation/gravity-connect-notion/
 *
 * Notion page POST reference:
 * https://developers.notion.com/reference/post-page
 *
 * @since 1.0-beta-1.9
 */

/**
 * Alternatively, you can use the `gcn_notion_page_data_add` or `gcn_notion_page_data_update` filters
 * separately to add/change the icon only when adding or updating a page.
 *
 * The filter also supports FORM_ID and FEED_ID modifiers to allow easy scoping to specific forms and feeds.
 *
 * See the full filter reference for more details:
 * https://gravitywiz.com/documentation/gcn_notion_page_data/
 */
add_filter( 'gcn_notion_page_data', function( $page_data, $form, $entry, $feed ) {
	/**
	 * Modify the $emoji variable to change the icon. Note that the Notion API requires a single emoji character.
	 */
	$emoji = '🦚';

	$page_data['icon'] = array(
		'type'  => 'emoji',
		'emoji' => $emoji,
	);

	return $page_data;
}, 10, 4 );
