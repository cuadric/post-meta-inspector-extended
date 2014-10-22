<?php
/**
 * Plugin Name: Post Meta Inspector Extended
 * Plugin URI: http://www.cuadric.com
 * Description: Todas los custom fields de un post, página o custom type en una sola metabox.
 * Author: Gonzalo Sanchez - Cuadric
 * Version: 1
 * Author URI: http://www.cuadric.com/
 */


class post_meta_inspector_extended
{

	private static $instance;

	public $view_cap;

	public function instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new post_meta_inspector_extended;
			self::setup_actions();
		}
		return self::$instance;
	}

	private function __construct() {
		/** Do nothing **/
	}

	private function setup_actions() {

		add_action( 'init', array( self::$instance, 'action_init') );
		add_action( 'add_meta_boxes', array( self::$instance, 'action_add_meta_boxes' ) );
	}

	/**
	 * Init i18n files
	 */
	public function action_init() {
		load_plugin_textdomain( 'post-meta-inspector', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add the post meta box to view post meta if the user has permissions to
	 */
	public function action_add_meta_boxes() {

		$this->view_cap = apply_filters( 'pmix_view_cap', 'manage_options' );
		if ( ! current_user_can( $this->view_cap ) || ! apply_filters( 'pmix_show_post_type', '__return_true', get_post_type() ) )
			return;

		add_meta_box( 'post-meta-inspector', __( 'Post Meta Inspector', 'post-meta-inspector' ), array( self::$instance, 'post_meta_inspector_extended' ), get_post_type() );
	}

	public function pmix_trace($var){
				echo '<pre class="trace">';
				print_r($var);
				echo '</pre>';
	}

	public function post_meta_inspector_extended() {
		?>
		<style>
			#post-meta-inspector table {
				text-align: left;
				width: 100%;
			}
			#post-meta-inspector table .key-column {
				display: table-cell;
				width: 10%;
				padding-right: 20px;
				vertical-align: top;
				border-bottom: 1px solid #eee;
				white-space: nowrap;
			}
			#post-meta-inspector table .value-column {
				width: auto;
				display: table-cell;
				vertical-align: top;
				border-bottom: 1px solid #eee;
			}
			#post-meta-inspector code {
				word-wrap: break-word;
				background: transparent !important;
				word-break: break-word;
				padding: 0;
				margin: 0;
			}
				#post-meta-inspector table .value-column code pre {
					margin: 0;
				}
		</style>

		<table>
			<thead>
				<tr>
					<th class="key-column"><?php _e( 'Key', 'post-meta-inspector' ); ?></th>
					<th class="value-column"><?php _e( 'Value', 'post-meta-inspector' ); ?></th>
				</tr>
			</thead>
			<tbody>

		<?php

			$toggle_length = apply_filters( 'pmix_toggle_long_value_length', 0 );
			$toggle_length = max( intval($toggle_length), 0);
			$toggle_el     = '<a href="javascript:void(0);" class="pmix_toggle">' . __( 'Click to show&hellip;', 'post-meta-inspector' ) . '</a>';

			$custom_fields = get_post_meta( get_the_ID() );

			foreach( $custom_fields as $key => $values ) :

				if ( apply_filters( 'pmix_ignore_post_meta_key', false, $key ) )
					continue;

				foreach( $values as $value ) :
					$unserialized_value = maybe_unserialize($value);
					//$value              = var_export( $value, true );
					$toggled            = $toggle_length && strlen($unserialized_value) > $toggle_length;
					?>

					<tr>
						<td class="key-column"><code><?php echo esc_html( $key ); ?></code></td>
						<td class="value-column">
							<?php if( $toggled ) echo $toggle_el; ?>
							<code <?php if( $toggled ) echo ' style="display: none;"'; ?>><?php $this->pmix_trace( $unserialized_value ) ?></code><?php // esc_html( $value ); ?>
						</td>
					</tr>

					<?php
				endforeach;

			endforeach;
		?>

			</tbody>
		</table>
		<script>
		jQuery(document).ready(function() {
			jQuery('.pmix_toggle').click( function(e){
				jQuery('+ code', this).show();
				jQuery(this).hide();
			});
		});
		</script>
		<?php
	}

}


// -------------------------------------------------------


function post_meta_inspector_extended() {
	return post_meta_inspector_extended::instance();
}
add_action( 'plugins_loaded', 'post_meta_inspector_extended' );


// -------------------------------------------------------

function pmix_do_toggle_long_value_length( $val ) {
	return 0;
}
add_filter( 'pmix_toggle_long_value_length', 'pmix_do_toggle_long_value_length' );

function pmix_do_ignore_post_meta_key( $key ) {
	return false;
}
add_filter( 'pmix_ignore_post_meta_key', 'pmix_do_ignore_post_meta_key' );


// -------------------------------------------------------