<?php /* PHPCS:ignore */
/**
 * Plugin Name: Loopia Driftblogg Widget
 * Description: Adminwidget för att visa planerade och pågående driftstörningar från Loopia Driftblogg.
 * Version: 1.1
 * Author: Jamie Blomerus
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Loopia_Driftblogg-klassen.
 *
 * Denna klass skapar en widget som visar driftstörningar från Loopia Driftblogg.
 */
class Loopia_Driftblogg {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'loopia_driftblogg_widget' ) );
	}

	/**
	 * Registrerar widget
	 *
	 * @return void
	 */
	public function loopia_driftblogg_widget(): void {
		wp_add_dashboard_widget(
			'loopia_driftblogg_widget',
			'Loopia Driftblogg',
			array( $this, 'loopia_driftblogg_widget_content' )
		);
	}

	/**
	 * Innehåll för widget
	 *
	 * @return void
	 */
	public function loopia_driftblogg_widget_content(): void {
		// Försök hämta loopia_driftblogg_posts från cache
		$posts = get_transient('loopia_driftblogg_posts');

		if (false === $posts) {
			// Hämta inlägg från driftbloggen
			$url = 'https://driftbloggen.se/wp-json/wp/v2/posts?categories=1&per_page=3';
			$response = wp_remote_get($url);

			if (is_wp_error($response)) {
				echo 'Error fetching posts';
				return;
			}

			$posts = json_decode(wp_remote_retrieve_body($response));

			// Spara inlägg i cache
			set_transient('loopia_driftblogg_posts', $posts, 300);
		}

		if (empty($posts)) {
			echo 'Inga driftstörningar hittades.';
			return;
		}
		?>
		<ul>
			<?php foreach ( $posts as $post ) : 
				$post_content = wp_remote_get( $post->link );
				$post_content = wp_remote_retrieve_body( $post_content );

				$title = wp_trim_words( $post->title->rendered, 8, '...' );

				// Kapa innehållet till det innan <div class="wrapper" id="single-wrapper">
				$post_content = substr( $post_content, 0, strpos( $post_content, '<div class="wrapper" id="single-wrapper">' ) );

				// Hämta flagga
				$flag = strpos( $post_content, 'flag-ongoing' ) ? 'flag-ongoing' : (strpos( $post_content, 'flag-planned' ) ? 'flag-planned' : 'flag-done');
				?>
				<li>
					<a href="<?php echo esc_attr( $post->link ); ?>" target="_blank">
						<p><strong><?php echo esc_html($title) ?></strong><span class="flag <?php echo esc_attr( $flag ); ?>"><?php echo $flag == 'flag-ongoing' ? 'Pågående' : ( $flag == 'flag-planned' ? 'Planerad' : 'Avklarad' ); ?></span></p>
						<p><i>
						<?php
						$excerpt = wp_trim_words( $post->excerpt->rendered, 10, '...' );
						echo esc_html( $excerpt );
						?>
						</i></p>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<style>
			#loopia_driftblogg_widget ul {
				list-style: none;
				padding-left: 0;
			}
			#loopia_driftblogg_widget ul li {
				margin-bottom: 10px;
			}
			#loopia_driftblogg_widget ul li a {
				text-decoration: none;
			}
			#loopia_driftblogg_widget ul li a p {
				margin-bottom: 0;
			}
			#loopia_driftblogg_widget ul li a p strong {
				font-weight: bold;
			}
			#loopia_driftblogg_widget ul li a p .flag {
				font-weight: bold;
				float: right;
				color: #fff;
				font-size: 0.8em;
				padding: 2px 5px;
				border-radius: 5px;
				margin-right: 10px;
			}
			#loopia_driftblogg_widget ul li a p .flag-ongoing {
				background-color: #FF2D2D;
			}
			#loopia_driftblogg_widget ul li a p .flag-planned {
				background-color: #e8b000;
			}
			#loopia_driftblogg_widget ul li a p .flag-done {
				background-color: #64c882;
			}
		<?php
	}
}
new Loopia_Driftblogg();