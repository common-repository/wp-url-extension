<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

class WPUE_Settings {

	/**
	 * Permalink settings.
	 * @var array
	 */
	private $permalinks = array();

	/* Hook in tabs */
	public function __construct() {
		add_action('admin_init', array($this,'settings_init'));
		add_action('admin_init', array($this,'settings_save'));
	}

	/* Init our settings */
	public function settings_init() {
		add_settings_section( 'wpue-permalink', __( 'WP URL Extension Settings', 'wpue' ), array( $this, 'settings' ), 'permalink' );
	}

	public function settings() {
		// delete_option('wpue_permalinks');
		$args = array('public' => true, '_builtin' => false );
		$post_types = get_post_types( $args );
		$restricted_post_types = array( 'post', 'page', 'post_tag', 'category' );
		$post_types = array_merge( $restricted_post_types, $post_types );
		$permalinks = array(
	     'url_end' => '.html'
			,'url_cat_end' => '.html'
			,'post_types' => $post_types//array()
		);
		if(!get_option('wpue_permalinks')) update_option( 'wpue_permalinks', $permalinks );

		$this->permalinks = get_option('wpue_permalinks');

		// prr($this->permalinks);
		// _e( 'If you <code>like</code>.', 'wpue' );
		?>
		<table class="form-table wc-permalink-structure">
			<tbody>
				<tr>
					<th><?php _e( 'Extension', 'wpue' )?></th>
					<td><input name="url_end" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['url_end'] ); ?>" placeholder="" /></td>
				</tr>
				<tr>
					<th><?php _e( 'Categories Extension', 'wpue' )?></th>
					<td><input name="url_cat_end" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['url_cat_end'] ); ?>" placeholder="" /></td>
				</tr>
				<tr>
					<th><?php _e( 'Remove /category/', 'wpue' )?></th>
					<td><input name="no_cat" type="checkbox" value="1" <?php checked( $this->permalinks['no_cat'], 1, true )?>></td>
				</tr>
				<?php
				if ( ! empty( $post_types ) ){
					echo '<style>.hide{display:none}.evi_post_types_lists li{display:inline-block;width:150px; margin-right:10px;}.evi_post_types_lists li input{vertical-align:bottom;}</style>';
					echo "<tr class='hide'><th>".__( 'Select post types', 'wpue' )."</th><td><ul class='evi_post_types_lists'>";
					foreach( $post_types as $k => $post_type ){
						$post_type_name = strtoupper( $post_type );
						$post_type_name = str_replace( '_', ' ', $post_type_name );
						echo '<li>
										<label><input type="checkbox" checked name="post_types[ ]" value="' . esc_html( $post_type ) . '">'.esc_html( $post_type_name ) . '</Label>
									</li>'; /* ' . checked( in_array( $post_type, $selected_post_type ),1,0 ) . ' */
					}
					echo '</ul></td></tr>';
				}
				?>
			</tbody>
		</table>
	<? }

	/* Save the settings */
	public function settings_save() {
		if(!is_admin())	return;

		if( $_POST['no_cat'] == 1 )
			$_POST['category_base'] = '.';
		elseif(  $_POST['category_base'] == '.' )
			$_POST['category_base'] = '';

		if(preg_match("/\.(.*?)+$/i", $_POST['permalink_structure'], $matches))
			$_POST['permalink_structure'] = substr($_POST['permalink_structure'], 0, (strlen($matches[0])*-1));

		if(substr($_POST['permalink_structure'], -1) == '/')
			$_POST['permalink_structure'] = substr($_POST['permalink_structure'], 0, -1);

		if( isset( $_POST['url_end'], $_POST['url_cat_end'] ) ){

			$permalinks									= (array) get_option( 'wpue_permalinks', array() );
			$permalinks['url_end']			= sanitize_text_field( $_POST['url_end'] );
			$permalinks['url_cat_end']	= sanitize_text_field( $_POST['url_cat_end'] );
			$permalinks['no_cat']				= sanitize_key( $_POST['no_cat'] );
			$permalinks['post_types']		= array();
			if(isset($_POST['post_types'])){
				foreach ($_POST['post_types'] as $k => $pt) {
					$permalinks['post_types'][$k] = sanitize_key($pt);
				}
			}
			update_option( 'wpue_permalinks', $permalinks );
		}
	}

}
