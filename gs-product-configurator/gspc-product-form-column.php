<?php
/**
 * Gravity Shop // GS Product Configurator // Add Gravity Form Column to Products List
 * https://gravitywiz.com/gravity-shop-product-configurator/
 *
 * This plugin adds a "Gravity Form" column to the WooCommerce Products admin list, showing
 * which products are connected to Gravity Forms via GS Product Configurator. The column
 * displays the form name as a clickable link that takes you directly to the form editor,
 * along with the form ID for quick reference. Products without attached forms show "No Form".
 * The column is also sortable, allowing you to easily organize products by their form
 * associations.
 *
 * This enhancement provides administrators with a clear overview of product-form relationships
 * directly in the WooCommerce admin interface, eliminating the need to check each product
 * individually to see if it has an attached configurator form.
 *
 * Instructions:
 *
 * 1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
/**
 * Plugin Name: GSPC Product Form Column
 * Description: Adds a column to the WooCommerce Products list showing the associated Gravity Form.
 * Version: 1.0
 * Author: Gravity Wiz
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * 
 * This plugin requires both WooCommerce and GS Product Configurator to be active.
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Check if required plugins are active
 */
function gspc_pfc_check_dependencies() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p><strong>GS Product Configurator Product Form Column</strong> requires WooCommerce to be installed and active.</p></div>';
        });
        return false;
    }
    
    if (!function_exists('gs_product_configurator')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p><strong>GS Product Configurator Product Form Column</strong> requires GS Product Configurator to be installed and active.</p></div>';
        });
        return false;
    }
    
    return true;
}

/**
 * Add custom column to the WooCommerce Products list
 */
function gspc_pfc_add_product_form_column($columns) {
    // Insert the new column after the product name column
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // Add our column after the product name
        if ($key === 'name') {
            $new_columns['gspc_form'] = __('Gravity Form', 'gs-product-configurator');
        }
    }
    
    return $new_columns;
}

/**
 * Populate custom column content in the WooCommerce Products list
 */
function gspc_pfc_product_form_column_content($column, $post_id) {
    if ($column !== 'gspc_form') {
        return;
    }

    // Check if GS Product Configurator is available
    if (!function_exists('gs_product_configurator')) {
        echo '<span style="color: #999;">—</span>';
        return;
    }

    $product = wc_get_product($post_id);
    
    if (!$product) {
        echo '<span style="color: #999;">—</span>';
        return;
    }

    try {
        $form = gs_product_configurator()->product_form_lookup->get_form($product);
        
        if ($form && !empty($form['title'])) {
            // Get the form ID for the link
            $form_id = $form['id'];
            $edit_url = admin_url('admin.php?page=gf_edit_forms&id=' . $form_id);
            
            echo '<a href="' . esc_url($edit_url) . '" title="' . esc_attr__('Edit form', 'gs-product-configurator') . '">';
            echo '<strong>' . esc_html($form['title']) . '</strong>';
            echo '</a>';
            echo '<br><small style="color: #666;">ID: ' . esc_html($form_id) . '</small>';
        } else {
            echo '<span style="color: #999;">' . __('No Form', 'gs-product-configurator') . '</span>';
        }
    } catch (Exception $e) {
        echo '<span style="color: #d63638;" title="' . esc_attr($e->getMessage()) . '">Error</span>';
    }
}

/**
 * Make the custom column sortable
 */
function gspc_pfc_make_column_sortable($columns) {
    $columns['gspc_form'] = 'gspc_form';
    return $columns;
}

/**
 * Handle sorting for the custom column
 */
function gspc_pfc_handle_column_sorting($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');
    
    if ($orderby === 'gspc_form') {
        global $wpdb;
        
        // Join with the GSPC lookup table and Gravity Forms table
        $query->set('meta_query', array());
        
        // Custom join and orderby to sort by form title
        add_filter('posts_join', function($join) use ($wpdb) {
            global $wpdb;
            $gspc_table = $wpdb->prefix . 'gspc_form_lookup';
            $gf_table = $wpdb->prefix . 'gf_form';
            
            $join .= " LEFT JOIN {$gspc_table} AS gspc_lookup ON {$wpdb->posts}.ID = gspc_lookup.object_id AND gspc_lookup.object_type = 'product'";
            $join .= " LEFT JOIN {$gf_table} AS gf_form ON gspc_lookup.form_id = gf_form.id";
            
            return $join;
        });
        
        add_filter('posts_orderby', function($orderby) use ($wpdb) {
            $order = (strtoupper($wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_http_referer' LIMIT 1")) === 'DESC') ? 'DESC' : 'ASC';
            if (isset($_GET['order'])) {
                $order = (strtoupper($_GET['order']) === 'DESC') ? 'DESC' : 'ASC';
            }
            
            return "COALESCE(gf_form.title, 'zzz') {$order}";
        });
    }
}

/**
 * Add CSS to improve column appearance
 */
function gspc_pfc_admin_css() {
    $screen = get_current_screen();
    
    if ($screen && $screen->id === 'edit-product') {
        echo '<style>
            .column-gspc_form {
                width: 150px;
            }
            .column-gspc_form a {
                text-decoration: none;
            }
            .column-gspc_form a:hover {
                text-decoration: underline;
            }
        </style>';
    }
}

/**
 * Initialize the plugin
 */
function gspc_pfc_init() {
    // Check dependencies first
    if (!gspc_pfc_check_dependencies()) {
        return;
    }
    
    // Add hooks only if dependencies are met
    add_filter('manage_edit-product_columns', 'gspc_pfc_add_product_form_column');
    add_action('manage_product_posts_custom_column', 'gspc_pfc_product_form_column_content', 10, 2);
    add_filter('manage_edit-product_sortable_columns', 'gspc_pfc_make_column_sortable');
    add_action('pre_get_posts', 'gspc_pfc_handle_column_sorting');
    add_action('admin_head', 'gspc_pfc_admin_css');
}

// Hook into WordPress
add_action('plugins_loaded', 'gspc_pfc_init');

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, function() {
    // Check dependencies on activation
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce to be installed and active.', 'gs-product-configurator'));
    }
    
    if (!function_exists('gs_product_configurator')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires GS Product Configurator to be installed and active.', 'gs-product-configurator'));
    }
});
