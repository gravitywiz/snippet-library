<?php
/**
 * Gravity Wiz // Gravity Forms // CID Image Handler
 * https://gravitywiz.com/gravity-forms-cid-image-handler/
 *
 * Automatically detect and embed any img tag with a CID:URL src in email messages. This snippet eliminates the need
 * to manually register each image - it automatically finds CID images and handles replacing the URL with an absolute
 * path for PHPMailer embedding. Supports both simple CID identifiers and full URLs.
 *
 * Usage:
 *
 * 1. [Install and activate this snippet.](https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets)
 * 2. Use any of these formats in your email content:
 *    - `<img src="cid:my-logo" alt="Logo" />`
 *    - `<img src="cid:2025/07/image.jpg" alt="Image" />`
 *    - `<img src="cid:https://domain.com/wp-content/uploads/image.jpg" alt="Full URL" />`
 * 3. Images will be automatically detected, located, and embedded inline.
 *
 * Plugin Name:  GW CID Image Handler
 * Plugin URI:   https://gravitywiz.com/gravity-forms-cid-image-handler/
 * Description:  Automatically detect and embed any img tag with a CID:URL src in email messages. Supports both simple CID identifiers and full URLs.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com
 */
class GW_CID_Image_Handler {

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// Hook into PHPMailer to process CID images
		add_action( 'phpmailer_init', array( $this, 'process_cid_images' ) );
	}

	/**
	 * Process CID images in PHPMailer
	 *
	 * @param PHPMailer $phpmailer The PHPMailer instance
	 */
	public function process_cid_images( $phpmailer ) {
		// Skip if no HTML body is set
		if ( empty( $phpmailer->Body ) ) {
			return;
		}

		// Find all img tags with cid: sources using regex (supports full URLs)
		preg_match_all( '/<img[^>]*src=["\']cid:([^"\']*)["\'][^>]*>/i', $phpmailer->Body, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return;
		}

		// Ensure HTML email (GF sends HTML, but this is a safeguard)
		if ( ! $phpmailer->ContentType || stripos( $phpmailer->ContentType, 'text/plain' ) !== false ) {
			$phpmailer->isHTML( true );
		}

		foreach ( $matches as $match ) {
			$full_img_tag = $match[0];
			$original_cid = $match[1];

			// Try to find the absolute path for this CID
			$path = $this->find_cid_image_path( $original_cid );

			if ( $path && file_exists( $path ) ) {
				$basename = basename( $path );
				$mime = mime_content_type( $path );

				// For full URLs, we need to create a simpler CID identifier for embedding
				// but keep the original CID in the HTML as-is
				if ( filter_var( $original_cid, FILTER_VALIDATE_URL ) ) {
					// Create a simple CID from the filename
					$filename_only = pathinfo( $basename, PATHINFO_FILENAME );
					$clean_filename = preg_replace( '/[^a-zA-Z0-9_-]/', '', $filename_only );
					$simple_cid = $clean_filename . '_' . substr( md5( $original_cid ), 0, 8 );

					// Replace the original CID in the email body with our simple CID
					$phpmailer->Body = str_replace( 'cid:' . $original_cid, 'cid:' . $simple_cid, $phpmailer->Body );

					// Use the simple CID for embedding
					$embed_cid = $simple_cid;
				} else {
					// For non-URL CIDs, use as-is
					$embed_cid = $original_cid;
				}

				// Embed the image inline
				// Arguments: file path, CID, name, encoding, mime type
				$phpmailer->AddEmbeddedImage( $path, $embed_cid, $basename, 'base64', $mime );
			}
		}
	}

	/**
	 * Find the absolute file path for a CID image
	 *
	 * @param string $cid The content ID (without 'cid:' prefix)
	 * @return string|false The absolute path if found, false otherwise
	 */
	private function find_cid_image_path( $cid ) {
		// Check if CID is a full URL (e.g., https://wand.local/wp-content/uploads/2025/07/lego-wizard.jpg)
		if ( filter_var( $cid, FILTER_VALIDATE_URL ) ) {
			// Parse the URL to get the path component
			$parsed_url = parse_url( $cid );
			if ( ! $parsed_url || empty( $parsed_url['path'] ) ) {
				return false;
			}

			// Convert URL path to local file system path
			$url_path = $parsed_url['path'];

			// Try different approaches to map URL path to file system path
			$possible_paths = array();

			// Method 1: Direct mapping from document root
			if ( defined( 'ABSPATH' ) ) {
				$possible_paths[] = rtrim( ABSPATH, '/' ) . $url_path;
			}

			// Method 2: If URL path contains wp-content, map to WP_CONTENT_DIR
			if ( strpos( $url_path, '/wp-content/' ) !== false ) {
				$content_path = substr( $url_path, strpos( $url_path, '/wp-content/' ) + 12 ); // +12 for '/wp-content/'
				$possible_paths[] = WP_CONTENT_DIR . '/' . ltrim( $content_path, '/' );
			}

			// Method 3: If URL path contains uploads, map to uploads directory
			if ( strpos( $url_path, '/uploads/' ) !== false ) {
				$uploads = wp_upload_dir();
				$upload_path = substr( $url_path, strpos( $url_path, '/uploads/' ) + 9 ); // +9 for '/uploads/'
				$possible_paths[] = trailingslashit( $uploads['basedir'] ) . ltrim( $upload_path, '/' );
			}

			// Check each possible path
			foreach ( $possible_paths as $path ) {
				if ( file_exists( $path ) ) {
					return $path;
				}
			}

			// Fallback: try the filename search with the basename from URL
			$cid = basename( $url_path );
		}

		// Original logic for non-URL CIDs or when URL mapping fails
		$uploads = wp_upload_dir();
		$relative_path = ltrim( $cid, '/' );

		// Try direct path from uploads directory
		$path = trailingslashit( $uploads['basedir'] ) . $relative_path;
		if ( file_exists( $path ) ) {
			return $path;
		}

		// Try with wp-content/uploads prefix
		$relative = '/uploads/' . $relative_path;
		$path = trailingslashit( WP_CONTENT_DIR ) . ltrim( $relative, '/' );
		if ( file_exists( $path ) ) {
			return $path;
		}

		// Try searching for the filename in common WordPress directories
		$filename = basename( $cid );
		$search_paths = array(
			$uploads['basedir'],
			trailingslashit( WP_CONTENT_DIR ) . 'uploads',
			get_template_directory() . '/images',
			get_stylesheet_directory() . '/images',
		);

		foreach ( $search_paths as $search_path ) {
			if ( ! is_dir( $search_path ) ) {
				continue;
			}

			// Search recursively for the filename
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $search_path, RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() && $file->getFilename() === $filename ) {
					return $file->getPathname();
				}
			}
		}

		// If all else fails, try to resolve it as an absolute path
		if ( file_exists( $cid ) ) {
			return $cid;
		}

		return false;
	}
}

# Configuration

// Initialize the CID Image Handler
function gw_cid_image_handler() {
	return GW_CID_Image_Handler::get_instance();
}

gw_cid_image_handler();
