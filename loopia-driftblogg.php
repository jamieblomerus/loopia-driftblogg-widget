<?php /* PHPCS:ignore */
/**
 * Plugin Name: Loopia Driftblogg Widget
 * Description: Adminwidget för att visa planerade och pågående driftstörningar från Loopia Driftblogg.
 * Version: 1.0.0
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
		$url      = 'https://driftbloggen.se/wp-json/wp/v2/posts?categories=1&per_page=3';
		$response = wp_remote_get( $url );
		$posts    = json_decode( wp_remote_retrieve_body( $response ) );
		$posts    = array_slice( $posts, 0, 3 );
		?>
		<ul>
			<?php foreach ( $posts as $post ) : ?>
				<li>
					<a href="<?php echo esc_attr( $post->link ); ?>" target="_blank">
						<p><strong><?php echo esc_attr( $post->title->rendered ); ?></strong></p>
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
		<?php
	}
}
new Loopia_Driftblogg();