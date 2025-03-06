<?php
/**
 * Gravity Wiz // Gravity Forms // All Fields Template: Collapsible Sections
 * https://gravitywiz.com/gravity-forms-all-fields-template/
 *
 * Adds support for a collapsible template with All Fields Template.
 * 
 * ![Preview of Collapsible All Fields Template in action](https://gravitywiz.com/app/uploads/2025/02/gwaft-collapsible-preview.png)
 *
 * Instructions:
 *
 * 1. Ensure you have All Fields Template installled.
 *    https://gravitywiz.com/gravity-forms-all-fields-template/
 *
 * 2. Install this snippet. No code configuration required.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 *
 * 3. Enable the collapsible template on any `{all_fields}` merge tag.
 *    `{all_fields:template[collapsible]}`
 */
add_filter( 'gwaft_template_output', function( $content, $slug, $name, $data, $suffixes ) {
	if ( ! in_array( 'collapsible', $suffixes ) ) {
		return $content;
	}
	$pages = $data['form']['pagination']['pages'];
	$page_groups = array();
	foreach ( $data['items'] as $item ) {
		$page_groups[ $item['field']['pageNumber'] ][] = $item;
	}
	ob_start();
	?>
	<style>
		.gwaft-collapsible {
			margin-top: 1rem;
		}
		.gwaft-collapsible details {
			background: var( --gf-color-primary, #2b4cdc );
			color: white;
			border: 2px solid var( --gf-color-primary, #2b4cdc );
			border-radius: var( --gf-radius, 3px );
			padding: 1rem;
			margin-bottom: 1rem;
		}
		.gwaft-collapsible details[open] {
			background: transparent;
			color: inherit;
		}
		.gwaft-collapsible summary {
			outline-width: 0;
		}
		.gwaft-collapsible ul {
			margin: 1rem 0 0;
			padding: 0;
			font-size: 1rem;
		}
		.gwaft-collapsible li {
			display: flex;
			gap: 1rem;
			padding: 1rem;
			border-radius: var( --gf-radius, 3px );
		}
		.gwaft-collapsible li:nth-child(odd) {
			background: rgb( from var(--gf-color-primary) r g b / 5% );
		}
		.gwaft-collapsible li span {
			flex: 1;
		}
	</style>
	<div class="gwaft-collapsible">
		<?php foreach ( $page_groups as $page_number => $page_group ): ?>
		<details>
			<summary><?php echo $pages[ $page_number ] ?></summary>
			<ul>
				<?php foreach( $page_group as $item ):
					?>

					<li>
						<span><?php echo $item['label']; ?></span>
						<span><?php echo $item['value']; ?></span>
					</li>

					<?php
				endforeach; ?>
			</ul>
		</details>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}, 10, 5 );
