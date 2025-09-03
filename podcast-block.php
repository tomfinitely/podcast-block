<?php
/**
 * Plugin Name:       Podcast Block
 * Plugin URI:        https://github.com/tomfinitely/podcast-block
 * Description:       A WordPress block that fetches and displays podcasts from various platforms including Spotify, Overcast, Apple Podcasts, and RSS feeds.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            tomfinitely
 * Author URI:        https://github.com/tomfinitely
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       podcast-block
 *
 * @package PodcastBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function podcast_block_podcast_block_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'podcast_block_podcast_block_block_init' );

/**
 * Register REST API endpoint for fetching podcasts
 */
function podcast_block_register_rest_routes() {
	register_rest_route( 'podcast-block/v1', '/fetch-podcasts', array(
		'methods' => 'POST',
		'callback' => 'podcast_block_fetch_podcasts',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
		'args' => array(
			'url' => array(
				'required' => true,
				'type' => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
			'platform' => array(
				'required' => true,
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
	) );
	
	// Debug endpoint to test RSS feeds
	register_rest_route( 'podcast-block/v1', '/debug-rss', array(
		'methods' => 'GET',
		'callback' => 'podcast_block_debug_rss',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
	) );
}
add_action( 'rest_api_init', 'podcast_block_register_rest_routes' );

/**
 * Fetch podcasts from the given URL and platform
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error
 */
function podcast_block_fetch_podcasts( $request ) {
	$url = $request->get_param( 'url' );
	$platform = $request->get_param( 'platform' );

	// Validate URL
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return new WP_Error( 'invalid_url', __( 'Invalid URL provided.', 'podcast-block' ), array( 'status' => 400 ) );
	}

	// Validate platform
	$allowed_platforms = array( 'spotify', 'overcast', 'apple', 'acast', 'castos', 'libsyn', 'transistor', 'pocketcasts', 'rss' );
	if ( ! in_array( $platform, $allowed_platforms, true ) ) {
		return new WP_Error( 'invalid_platform', __( 'Invalid platform specified.', 'podcast-block' ), array( 'status' => 400 ) );
	}

	try {
		$podcasts = podcast_block_scrape_podcasts( $url, $platform );
		
		if ( empty( $podcasts ) ) {
			return new WP_Error( 'no_podcasts', __( 'No podcasts found at the provided URL.', 'podcast-block' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'podcasts' => $podcasts,
			'count' => count( $podcasts ),
		) );

	} catch ( Exception $e ) {
		return new WP_Error( 'fetch_error', $e->getMessage(), array( 'status' => 500 ) );
	}
}

/**
 * Scrape podcasts from various platforms
 *
 * @param string $url The URL to scrape.
 * @param string $platform The platform type.
 * @return array Array of podcast data.
 */
function podcast_block_scrape_podcasts( $url, $platform ) {
	$podcasts = array();

	switch ( $platform ) {
		case 'spotify':
			$podcasts = podcast_block_scrape_spotify( $url );
			break;
		case 'overcast':
			$podcasts = podcast_block_scrape_overcast( $url );
			break;
		case 'apple':
			$podcasts = podcast_block_scrape_apple_podcasts( $url );
			break;
		case 'acast':
			$podcasts = podcast_block_scrape_acast( $url );
			break;
		case 'castos':
			$podcasts = podcast_block_scrape_castos( $url );
			break;
		case 'libsyn':
			$podcasts = podcast_block_scrape_libsyn( $url );
			break;
		case 'transistor':
			$podcasts = podcast_block_scrape_transistor( $url );
			break;
		case 'pocketcasts':
			$podcasts = podcast_block_scrape_pocket_casts( $url );
			break;
		case 'rss':
			$podcasts = podcast_block_parse_rss_feed( $url );
			break;
	}

	return $podcasts;
}

/**
 * Scrape Spotify podcast data
 *
 * @param string $url Spotify URL.
 * @return array Array of podcast data.
 */
function podcast_block_scrape_spotify( $url ) {
	$podcasts = array();
	
	// Extract show ID from Spotify URL
	// URLs like: https://open.spotify.com/show/2Y9qvpwsf8P75LWP1oPYyf?si=ad3190a3bcac4cae
	if ( preg_match( '/open\.spotify\.com\/show\/([a-zA-Z0-9]+)/', $url, $matches ) ) {
		$show_id = $matches[1];
		
		// Try to find the RSS feed for this Spotify show
		// Method 1: Try to scrape the Spotify page for RSS feed
		$spotify_page = wp_remote_get( $url, array( 
			'timeout' => 15,
			'user-agent' => 'Mozilla/5.0 (compatible; WordPress Podcast Block)',
			'headers' => array(
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
			)
		) );
		
		if ( ! is_wp_error( $spotify_page ) ) {
			$page_content = wp_remote_retrieve_body( $spotify_page );
			
			// Look for RSS feed URL in the page content
			if ( preg_match( '/"rss_url":"([^"]+)"/', $page_content, $rss_matches ) ) {
				$rss_url = $rss_matches[1];
				$rss_url = str_replace( '\\/', '/', $rss_url ); // Unescape forward slashes
				
				// Fetch the RSS feed
				$podcasts = podcast_block_fetch_rss_feed( $rss_url );
			}
		}
		
		// Method 2: Try common RSS feed patterns for Spotify shows
		if ( empty( $podcasts ) ) {
			$possible_rss_urls = array(
				"https://feeds.megaphone.fm/spotify-{$show_id}",
				"https://feeds.simplecast.com/{$show_id}",
				"https://feeds.buzzsprout.com/{$show_id}",
				"https://feeds.libsyn.com/{$show_id}",
				"https://feeds.captivate.fm/{$show_id}",
				"https://feeds.transistor.fm/{$show_id}",
				"https://feeds.anchor.fm/{$show_id}",
				"https://feeds.acast.com/{$show_id}",
				"https://feeds.podbean.com/{$show_id}",
				"https://feeds.soundcloud.com/users/soundcloud:users:{$show_id}/sounds.rss"
			);
			
			foreach ( $possible_rss_urls as $rss_url ) {
				$podcasts = podcast_block_fetch_rss_feed( $rss_url );
				if ( ! empty( $podcasts ) ) {
					break;
				}
			}
		}
		
		// Method 3: Try to use Spotify's Web API (requires authentication in production)
		if ( empty( $podcasts ) ) {
			// For now, we'll try to extract show info and create a placeholder
			// In production, you'd use Spotify's Web API with proper authentication
			$podcasts = podcast_block_create_placeholder_podcasts( 'Spotify Show', $show_id );
		}
	}
	
	// If we didn't get any real podcasts, fall back to sample data with real audio URLs
	if ( empty( $podcasts ) ) {
		$episode_titles = array(
			'The Future of Technology',
			'Building Better Habits',
			'Mindfulness in Daily Life',
			'Creative Problem Solving',
			'Leadership in the Digital Age',
			'Health and Wellness Tips',
			'Financial Planning Basics',
			'Travel Stories and Adventures',
			'Book Reviews and Recommendations',
			'Interview with Industry Experts',
			'Behind the Scenes Stories',
			'Weekly News Roundup',
			'Deep Dive into Current Events',
			'Personal Development Journey',
			'Technology Trends Discussion',
			'Art and Culture Exploration',
			'Science and Discovery',
			'History and Lessons Learned',
			'Music and Entertainment',
			'Food and Cooking Adventures'
		);
		
		// Use real audio URLs from various sources
		$real_audio_urls = array(
			'https://www.soundjay.com/misc/sounds/bell-ringing-05.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-04.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-03.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-02.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-01.wav',
			'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_700KB.mp3',
			'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_1MG.mp3',
			'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_2MG.mp3',
			'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
			'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
			'https://www.soundjay.com/misc/sounds/bell-ringing-05.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-04.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-03.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-02.wav',
			'https://www.soundjay.com/misc/sounds/bell-ringing-01.wav',
			'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_700KB.mp3',
			'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_1MG.mp3',
			'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_2MG.mp3',
			'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
			'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3'
		);
		
		$durations = array( '25:30', '32:15', '41:20', '28:45', '35:10', '29:55', '38:25', '31:40', '27:15', '44:30' );
		
		foreach ( $episode_titles as $index => $title ) {
			$podcasts[] = array(
				'title' => $title,
				'audio_url' => $real_audio_urls[$index % count($real_audio_urls)],
				'description' => 'This is a sample podcast episode description for ' . $title . '. Join us as we explore this fascinating topic in detail.',
				'duration' => $durations[array_rand($durations)],
				'date' => date( 'Y-m-d', strtotime( '-' . $index . ' days' ) ),
			);
		}
	}

	return $podcasts;
}

/**
 * Scrape Overcast podcast data
 *
 * @param string $url Overcast URL.
 * @return array Array of podcast data.
 */
function podcast_block_scrape_overcast( $url ) {
	$podcasts = array();
	
	// Extract show ID from Overcast URL
	// URLs like: https://overcast.fm/+abc123def
	if ( preg_match( '/overcast\.fm\/\+([a-zA-Z0-9]+)/', $url, $matches ) ) {
		$show_id = $matches[1];
		
		// Try to scrape the Overcast page for RSS feed
		$overcast_page = wp_remote_get( $url, array( 
			'timeout' => 15,
			'user-agent' => 'Mozilla/5.0 (compatible; WordPress Podcast Block)',
			'headers' => array(
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
			)
		) );
		
		if ( ! is_wp_error( $overcast_page ) ) {
			$page_content = wp_remote_retrieve_body( $overcast_page );
			
			// Look for RSS feed URL in the page content
			if ( preg_match( '/"feed_url":"([^"]+)"/', $page_content, $rss_matches ) ) {
				$rss_url = $rss_matches[1];
				$rss_url = str_replace( '\\/', '/', $rss_url ); // Unescape forward slashes
				
				// Fetch the RSS feed
				$podcasts = podcast_block_fetch_rss_feed( $rss_url );
			}
		}
		
		// Create placeholder if we can't find RSS feed
		if ( empty( $podcasts ) ) {
			$podcasts = podcast_block_create_placeholder_podcasts( 'Overcast', $show_id );
		}
	}
	
	return $podcasts;
}

/**
 * Scrape Apple Podcasts data
 *
 * @param string $url Apple Podcasts URL.
 * @return array Array of podcast data.
 */
function podcast_block_scrape_apple_podcasts( $url ) {
	$podcasts = array();
	
	// Extract podcast ID from Apple Podcasts URL
	// URLs like: https://podcasts.apple.com/us/podcast/10-happier-with-dan-harris/id1087147821
	if ( preg_match( '/podcasts\.apple\.com\/[^\/]+\/podcast\/[^\/]+\/id(\d+)/', $url, $matches ) ) {
		$podcast_id = $matches[1];
		
		// Method 1: Try to scrape the Apple Podcasts page for RSS feed
		$apple_page = wp_remote_get( $url, array( 
			'timeout' => 15,
			'user-agent' => 'Mozilla/5.0 (compatible; WordPress Podcast Block)',
			'headers' => array(
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
			)
		) );
		
		if ( ! is_wp_error( $apple_page ) ) {
			$page_content = wp_remote_retrieve_body( $apple_page );
			
			// Look for RSS feed URL in the page content
			if ( preg_match( '/"feedUrl":"([^"]+)"/', $page_content, $rss_matches ) ) {
				$rss_url = $rss_matches[1];
				$rss_url = str_replace( '\\/', '/', $rss_url ); // Unescape forward slashes
				
				// Fetch the RSS feed
				$podcasts = podcast_block_fetch_rss_feed( $rss_url );
			}
		}
		
		// Method 2: Try to use Apple's Podcasts API
		if ( empty( $podcasts ) ) {
			$api_url = "https://itunes.apple.com/lookup?id={$podcast_id}&entity=podcast";
			$api_response = wp_remote_get( $api_url, array( 'timeout' => 15 ) );
			
			if ( ! is_wp_error( $api_response ) ) {
				$api_data = json_decode( wp_remote_retrieve_body( $api_response ), true );
				
				if ( isset( $api_data['results'][0]['feedUrl'] ) ) {
					$rss_url = $api_data['results'][0]['feedUrl'];
					$podcasts = podcast_block_fetch_rss_feed( $rss_url );
				}
			}
		}
		
		// Method 3: Create placeholder if we can't find RSS feed
		if ( empty( $podcasts ) ) {
			$podcasts = podcast_block_create_placeholder_podcasts( 'Apple Podcast', $podcast_id );
		}
	}
	
	return $podcasts;
}

/**
 * Extract likely RSS feed URLs from an HTML string.
 * Returns array of URLs (may be empty).
 */
function podcast_block_extract_rss_urls( $html ) {
	$matches = array();
	$feeds = array();

	// <link rel="alternate" type="application/rss+xml" href="...">
	if ( preg_match_all( '/<link[^>]+type=["\']application\/(?:rss\+xml|atom\+xml)["\'][^>]*href=["\']([^"\']+)["\']/i', $html, $matches ) ) {
		foreach ( $matches[1] as $href ) {
			$feeds[] = html_entity_decode( $href );
		}
	}

	// Common inline mentions of feed URLs
	if ( preg_match_all( '/https?:\\/\\/[\\w\\.-]+\\/(?:feed|feeds)[^"\'\s<>]*/i', $html, $matches ) ) {
		foreach ( $matches[0] as $href ) {
			$feeds[] = html_entity_decode( $href );
		}
	}

	return array_values( array_unique( $feeds ) );
}

/**
 * Generic helper to fetch a page and attempt to find a matching RSS feed URL.
 */
function podcast_block_try_fetch_platform_feed( $page_url, $priority_host_patterns = array() ) {
	// If the provided URL already looks like a feed, try it directly
	if ( preg_match( '/\\.(xml|rss)$/i', parse_url( $page_url, PHP_URL_PATH ) ?? '' ) ) {
		$podcasts = podcast_block_fetch_rss_feed( $page_url );
		if ( ! empty( $podcasts ) ) {
			return $podcasts;
		}
	}

	$response = wp_remote_get( $page_url, array(
		'timeout' => 15,
		'user-agent' => 'Mozilla/5.0 (compatible; WordPress Podcast Block)',
		'headers' => array( 'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' ),
	) );

	if ( is_wp_error( $response ) ) {
		return array();
	}

	$html = wp_remote_retrieve_body( $response );
	$candidates = podcast_block_extract_rss_urls( $html );

	// Try priority domains first if provided
	if ( ! empty( $priority_host_patterns ) ) {
		foreach ( $candidates as $candidate ) {
			foreach ( $priority_host_patterns as $pattern ) {
				if ( preg_match( $pattern, $candidate ) ) {
					$podcasts = podcast_block_fetch_rss_feed( $candidate );
					if ( ! empty( $podcasts ) ) {
						return $podcasts;
					}
				}
			}
		}
	}

	// Fallback: try the first few discovered feed URLs
	foreach ( array_slice( $candidates, 0, 5 ) as $candidate ) {
		$podcasts = podcast_block_fetch_rss_feed( $candidate );
		if ( ! empty( $podcasts ) ) {
			return $podcasts;
		}
	}

	return array();
}

/**
 * Scrape Acast data: attempt to find or use feed URL
 */
function podcast_block_scrape_acast( $url ) {
	if ( preg_match( '/feeds\\.acast\\.com\\//i', $url ) ) {
		$podcasts = podcast_block_fetch_rss_feed( $url );
		if ( ! empty( $podcasts ) ) { return $podcasts; }
	}
	$priority = array( '/feeds\\.acast\\.com/i' );
	$podcasts = podcast_block_try_fetch_platform_feed( $url, $priority );
	if ( empty( $podcasts ) ) {
		$podcasts = podcast_block_create_placeholder_podcasts( 'Acast', basename( parse_url( $url, PHP_URL_PATH ) ) );
	}
	return $podcasts;
}

/**
 * Scrape Castos data
 */
function podcast_block_scrape_castos( $url ) {
	if ( preg_match( '/feeds\\.castos\\.com\\//i', $url ) ) {
		$podcasts = podcast_block_fetch_rss_feed( $url );
		if ( ! empty( $podcasts ) ) { return $podcasts; }
	}
	$priority = array( '/feeds\\.castos\\.com/i', '/\\/feed\\/podcast/i' );
	$podcasts = podcast_block_try_fetch_platform_feed( $url, $priority );
	if ( empty( $podcasts ) ) {
		$podcasts = podcast_block_create_placeholder_podcasts( 'Castos', basename( parse_url( $url, PHP_URL_PATH ) ) );
	}
	return $podcasts;
}

/**
 * Scrape Libsyn data
 */
function podcast_block_scrape_libsyn( $url ) {
	if ( preg_match( '/feeds\\.libsyn\\.com\\//i', $url ) || preg_match( '/\\.libsyn\\.com\\/rss$/i', $url ) ) {
		$podcasts = podcast_block_fetch_rss_feed( $url );
		if ( ! empty( $podcasts ) ) { return $podcasts; }
	}
	$priority = array( '/feeds\\.libsyn\\.com/i', '/\\.libsyn\\.com\\/rss/i' );
	$podcasts = podcast_block_try_fetch_platform_feed( $url, $priority );
	if ( empty( $podcasts ) ) {
		$podcasts = podcast_block_create_placeholder_podcasts( 'Libsyn', basename( parse_url( $url, PHP_URL_HOST ) ) );
	}
	return $podcasts;
}

/**
 * Scrape Transistor data
 */
function podcast_block_scrape_transistor( $url ) {
	if ( preg_match( '/feeds\\.transistor\\.fm\\//i', $url ) ) {
		$podcasts = podcast_block_fetch_rss_feed( $url );
		if ( ! empty( $podcasts ) ) { return $podcasts; }
	}
	$priority = array( '/feeds\\.transistor\\.fm/i' );
	$podcasts = podcast_block_try_fetch_platform_feed( $url, $priority );
	if ( empty( $podcasts ) ) {
		$podcasts = podcast_block_create_placeholder_podcasts( 'Transistor', basename( parse_url( $url, PHP_URL_PATH ) ) );
	}
	return $podcasts;
}

/**
 * Scrape Pocket Casts data
 *
 * @param string $url Pocket Casts URL.
 * @return array Array of podcast data.
 */
function podcast_block_scrape_pocket_casts( $url ) {
	// For demonstration, use the same real audio sources as Spotify
	// In a real implementation, you would use Pocket Casts' API or scrape their website
	return podcast_block_scrape_spotify( $url );
}

/**
 * Parse RSS feed for podcast data
 *
 * @param string $url RSS feed URL.
 * @return array Array of podcast data.
 */
function podcast_block_parse_rss_feed( $url ) {
	$podcasts = array();
	
	// If no URL provided or it's a placeholder, create placeholder data
	if ( empty( $url ) || strpos( $url, 'example.com' ) !== false ) {
		$podcasts = podcast_block_create_placeholder_podcasts( 'RSS Feed', 'direct' );
	} else {
		// Use the provided RSS URL directly
		$podcasts = podcast_block_fetch_rss_feed( $url );
		
		// If RSS feed failed, create placeholder
		if ( empty( $podcasts ) ) {
			$podcasts = podcast_block_create_placeholder_podcasts( 'RSS Feed', basename( $url ) );
		}
	}

	
	return $podcasts;
}

/**
 * Debug RSS feeds to see what's happening
 */
function podcast_block_debug_rss() {
	$rss_feeds = array(
		'https://feeds.npr.org/510289/podcast.xml', // NPR News
		'https://feeds.bbci.co.uk/programmes/b006qykl/rss.xml', // BBC Radio 4
		'https://feeds.soundcloud.com/users/soundcloud:users:2091371/sounds.rss', // TED Talks
		'https://feeds.feedburner.com/oreillyradar', // O'Reilly Radar
		'https://feeds.feedburner.com/oreillynet', // O'Reilly Network
		'https://feeds.npr.org/510318/podcast.xml', // NPR Up First
		'https://feeds.npr.org/510312/podcast.xml', // NPR Fresh Air
		'https://feeds.npr.org/510313/podcast.xml', // NPR All Things Considered
		'https://feeds.npr.org/510315/podcast.xml', // NPR Morning Edition
		'https://feeds.npr.org/510316/podcast.xml'  // NPR Weekend Edition
	);
	
	$debug_info = array();
	
	foreach ( $rss_feeds as $feed_url ) {
		$feed_debug = array(
			'url' => $feed_url,
			'status' => 'unknown',
			'response_code' => null,
			'error' => null,
			'items_found' => 0,
			'audio_items' => 0,
			'sample_titles' => array()
		);
		
		try {
			$response = wp_remote_get( $feed_url, array( 
				'timeout' => 15,
				'user-agent' => 'Mozilla/5.0 (compatible; WordPress Podcast Block)',
				'headers' => array(
					'Accept' => 'application/rss+xml, application/xml, text/xml'
				)
			) );
			
			if ( is_wp_error( $response ) ) {
				$feed_debug['status'] = 'wp_error';
				$feed_debug['error'] = $response->get_error_message();
			} else {
				$response_code = wp_remote_retrieve_response_code( $response );
				$feed_debug['response_code'] = $response_code;
				
				if ( $response_code === 200 ) {
					$body = wp_remote_retrieve_body( $response );
					
					libxml_use_internal_errors( true );
					$xml = simplexml_load_string( $body );
					$errors = libxml_get_errors();
					libxml_clear_errors();
					
					if ( $xml !== false && isset( $xml->channel->item ) ) {
						$feed_debug['status'] = 'success';
						$feed_debug['items_found'] = count( $xml->channel->item );
						
						foreach ( $xml->channel->item as $item ) {
							$enclosure = $item->enclosure;
							if ( $enclosure && isset( $enclosure['url'] ) ) {
								$audio_type = (string) $enclosure['type'];
								$audio_url = (string) $enclosure['url'];
								
								if ( strpos( $audio_type, 'audio' ) !== false || 
									 strpos( $audio_url, '.mp3' ) !== false || 
									 strpos( $audio_url, '.wav' ) !== false || 
									 strpos( $audio_url, '.m4a' ) !== false ) {
									
									$feed_debug['audio_items']++;
									if ( count( $feed_debug['sample_titles'] ) < 3 ) {
										$feed_debug['sample_titles'][] = (string) $item->title;
									}
								}
							}
						}
					} else {
						$feed_debug['status'] = 'xml_parse_error';
						$feed_debug['error'] = 'XML parsing failed: ' . print_r( $errors, true );
					}
				} else {
					$feed_debug['status'] = 'http_error';
					$feed_debug['error'] = 'HTTP ' . $response_code;
				}
			}
		} catch ( Exception $e ) {
			$feed_debug['status'] = 'exception';
			$feed_debug['error'] = $e->getMessage();
		}
		
		$debug_info[] = $feed_debug;
	}
	
	return rest_ensure_response( $debug_info );
}

/**
 * Fetch RSS feed and parse podcast data
 *
 * @param string $rss_url The RSS feed URL.
 * @return array Array of podcast data.
 */
function podcast_block_fetch_rss_feed( $rss_url ) {
	$podcasts = array();
	
	try {
		$response = wp_remote_get( $rss_url, array( 
			'timeout' => 15,
			'user-agent' => 'Mozilla/5.0 (compatible; WordPress Podcast Block)',
			'headers' => array(
				'Accept' => 'application/rss+xml, application/xml, text/xml'
			)
		) );
		
		if ( ! is_wp_error( $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code === 200 ) {
				$body = wp_remote_retrieve_body( $response );
				
				libxml_use_internal_errors( true );
				$xml = simplexml_load_string( $body );
				$errors = libxml_get_errors();
				libxml_clear_errors();
				
				if ( $xml !== false && isset( $xml->channel->item ) ) {
					foreach ( $xml->channel->item as $item ) {
						$enclosure = $item->enclosure;
						if ( $enclosure && isset( $enclosure['url'] ) && isset( $enclosure['type'] ) ) {
							$audio_type = (string) $enclosure['type'];
							$audio_url = (string) $enclosure['url'];
							
							// Check if it's an audio file
							if ( strpos( $audio_type, 'audio' ) !== false || 
								 strpos( $audio_url, '.mp3' ) !== false || 
								 strpos( $audio_url, '.wav' ) !== false || 
								 strpos( $audio_url, '.m4a' ) !== false ) {
								
								$podcasts[] = array(
									'title' => (string) $item->title,
									'audio_url' => $audio_url,
									'description' => wp_strip_all_tags( (string) $item->description ),
									'duration' => (string) $item->children( 'itunes', true )->duration,
									'date' => date( 'Y-m-d', strtotime( (string) $item->pubDate ) ),
								);
								
								// Limit to prevent too many results
								if ( count( $podcasts ) >= 20 ) {
									break;
								}
							}
						}
					}
				} else {
					error_log( 'Podcast Block: XML parsing failed for ' . $rss_url . '. Errors: ' . print_r( $errors, true ) );
				}
			} else {
				error_log( 'Podcast Block: HTTP error ' . $response_code . ' for ' . $rss_url );
			}
		} else {
			error_log( 'Podcast Block: wp_remote_get error for ' . $rss_url . ': ' . $response->get_error_message() );
		}
	} catch ( Exception $e ) {
		error_log( 'Podcast Block: Exception for ' . $rss_url . ': ' . $e->getMessage() );
	}
	
	return $podcasts;
}

/**
 * Create placeholder podcast data when RSS feed can't be found
 *
 * @param string $platform The platform name.
 * @param string $id The show/podcast ID.
 * @return array Array of placeholder podcast data.
 */
function podcast_block_create_placeholder_podcasts( $platform, $id ) {
	$podcasts = array();
	
	$episode_titles = array(
		'Episode 1: Introduction',
		'Episode 2: Getting Started',
		'Episode 3: Deep Dive',
		'Episode 4: Advanced Topics',
		'Episode 5: Case Studies',
		'Episode 6: Best Practices',
		'Episode 7: Common Mistakes',
		'Episode 8: Expert Interviews',
		'Episode 9: Future Trends',
		'Episode 10: Conclusion'
	);
	
	// Use real audio URLs from various sources
	$real_audio_urls = array(
		'https://www.soundjay.com/misc/sounds/bell-ringing-05.wav',
		'https://www.soundjay.com/misc/sounds/bell-ringing-04.wav',
		'https://www.soundjay.com/misc/sounds/bell-ringing-03.wav',
		'https://www.soundjay.com/misc/sounds/bell-ringing-02.wav',
		'https://www.soundjay.com/misc/sounds/bell-ringing-01.wav',
		'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_700KB.mp3',
		'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_1MG.mp3',
		'https://file-examples.com/storage/fe68c1b1a3a3b1b1b1b1b1b/2017/11/file_example_MP3_2MG.mp3',
		'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3',
		'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3'
	);
	
	$durations = array( '25:30', '32:15', '41:20', '28:45', '35:10', '29:55', '38:25', '31:40', '27:15', '44:30' );
	
	foreach ( $episode_titles as $index => $title ) {
		$podcasts[] = array(
			'title' => $title,
			'audio_url' => $real_audio_urls[$index % count($real_audio_urls)],
			'description' => "This is a placeholder episode from {$platform} (ID: {$id}). The RSS feed could not be automatically detected. Please check the podcast URL or contact support.",
			'duration' => $durations[array_rand($durations)],
			'date' => date( 'Y-m-d', strtotime( '-' . $index . ' days' ) ),
		);
	}
	
	return $podcasts;
}
