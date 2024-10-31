<?php
/*
The settings page
*/

function wprc_menu_item() {
	global $wprc_settings_page_hook;
	$wprc_settings_page_hook = add_submenu_page(
		'wprc_reports_page',
		esc_html__( 'Report Settings', 'report-content' ),
		esc_html__( 'Settings', 'report-content' ),
		'administrator',
		'wprc_settings',
		'wprc_render_settings_page'
	);
}

add_action( 'admin_menu', 'wprc_menu_item' );

function wprc_scripts_styles( $hook ) {
	global $wprc_settings_page_hook;
	if ( $wprc_settings_page_hook != $hook ) {
		return;
	}
	wp_enqueue_style( "options_panel_stylesheet", plugins_url( "static/css/options-panel.css", dirname( __FILE__ ) ), false, "1.0", "all" );
	wp_enqueue_script( "options_panel_script", plugins_url( "static/js/options-panel.js", dirname( __FILE__ ) ), false, "1.0" );
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
}

add_action( 'admin_enqueue_scripts', 'wprc_scripts_styles' );

function wprc_render_settings_page() {
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<h2><?php esc_html_e( 'Report Content Settings', 'report-content' ); ?></h2>
		<?php settings_errors(); ?>
		<div class="clearfix paddingtop20">
			<div class="first ninecol">
				<form method="post" action="options.php">
					<?php settings_fields( 'wprc_settings' ); ?>
					<?php do_meta_boxes( 'wprc_metaboxes', 'advanced', null ); ?>
					<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				</form>
			</div>
			<div class="last threecol">
				<div class="side-block">
					<?php esc_html_e( 'Like the plugin?', 'report-content' ); ?> <br/>
					<a href="https://wordpress.org/support/view/plugin-reviews/report-content#postform"
					   target="_blank"><?php esc_html_e( 'Leave a review', 'report-content' ); ?></a>
				</div>
			</div>
		</div>
	</div>
<?php }

function wprc_create_options() {

	add_settings_section( 'form_settings_section', null, null, 'wprc_settings' );
	add_settings_section( 'integration_settings_section', null, null, 'wprc_settings' );
	add_settings_section( 'email_settings_section', null, null, 'wprc_settings' );
	add_settings_section( 'permissions_settings_section', null, null, 'wprc_settings' );
	add_settings_section( 'other_settings_section', null, null, 'wprc_settings' );

	add_settings_field(
		'active_fields', '', 'wprc_render_settings_field', 'wprc_settings', 'form_settings_section',
		array(
			'title' => esc_html__( 'Active Fields', 'report-content' ),
			'desc'  => esc_html__( 'Fields that will appear on the report form', 'report-content' ),
			'id'    => 'active_fields',
			'type'  => 'multicheckbox',
			'items' => array(
				'reason'         => esc_html__( 'Reason', 'report-content' ),
				'reporter_name'  => esc_html__( 'Name', 'report-content' ),
				'reporter_email' => esc_html__( 'Email', 'report-content' ),
				'details'        => esc_html__( 'Details', 'report-content' ),
			),
			'group' => 'wprc_form_settings',
		)
	);

	add_settings_field(
		'required_fields', '', 'wprc_render_settings_field', 'wprc_settings', 'form_settings_section',
		array(
			'title' => esc_html__( 'Required Fields', 'report-content' ),
			'desc'  => esc_html__( 'Fields that are required', 'report-content' ),
			'id'    => 'required_fields',
			'type'  => 'multicheckbox',
			'items' => array(
				'reason'         => esc_html__( 'Reason', 'report-content' ),
				'reporter_name'  => esc_html__( 'Name', 'report-content' ),
				'reporter_email' => esc_html__( 'Email', 'report-content' ),
				'details'        => esc_html__( 'Details', 'report-content' ),
			),
			'group' => 'wprc_form_settings',
		)
	);

	add_settings_field(
		'report_reasons', '', 'wprc_render_settings_field', 'wprc_settings', 'form_settings_section',
		array(
			'title' => esc_html__( 'Issues', 'report-content' ),
			'desc'  => esc_html__( 'Add one entry per line. These issues will appear in the form of a dropdown.', 'report-content' ),
			'id'    => 'report_reasons',
			'type'  => 'textarea',
			'group' => 'wprc_form_settings',
		)
	);

	add_settings_field(
		'slidedown_button_text', '', 'wprc_render_settings_field', 'wprc_settings', 'form_settings_section',
		array(
			'title' => esc_html__( 'Slide Down Button Text', 'report-content' ),
			'desc'  => '',
			'id'    => 'slidedown_button_text',
			'type'  => 'text',
			'group' => 'wprc_form_settings',
		)
	);

	add_settings_field(
		'submit_button_text', '', 'wprc_render_settings_field', 'wprc_settings', 'form_settings_section',
		array(
			'title' => esc_html__( 'Submit Button Text', 'report-content' ),
			'desc'  => '',
			'id'    => 'submit_button_text',
			'type'  => 'text',
			'group' => 'wprc_form_settings',
		)
	);

	add_settings_field(
		'color_scheme', '', 'wprc_render_settings_field', 'wprc_settings', 'form_settings_section',
		array(
			'title'   => esc_html__( 'Color Scheme', 'report-content' ),
			'desc'    => esc_html__( 'Select a scheme for the form', 'report-content' ),
			'id'      => 'color_scheme',
			'type'    => 'select',
			'options' => array(
				"yellow-colorscheme" => esc_html__( 'Yellow', 'report-content' ),
				"red-colorscheme"    => esc_html__( 'Red', 'report-content' ),
				"blue-colorscheme"   => esc_html__( 'Blue', 'report-content' ),
				"green-colorscheme"  => esc_html__( 'Green', 'report-content' ),
			),
			'group'   => 'wprc_form_settings',
		)
	);

	add_settings_field(
		'integration_type', '', 'wprc_render_settings_field', 'wprc_settings', 'integration_settings_section',
		array(
			'title'   => esc_html__( 'Add the report form', 'report-content' ),
			'desc'    => sprintf(
			/* translators: 1: php template tag for displaying report form */
				esc_html__( 'If you choose manual integration you will have to place %1$s in your theme files manually.', 'report-content' ),
				'<b>&lt;?php wprc_report_submission_form(); ?&gt;</b>'
			),
			'id'      => 'integration_type',
			'type'    => 'select',
			'options' => array(
				"automatically" => esc_html__( 'Automatically', 'report-content' ),
				"manually"      => esc_html__( 'Manually', 'report-content' ),
			),
			'group'   => 'wprc_integration_settings',
		)
	);

	add_settings_field(
		'automatic_form_position', '', 'wprc_render_settings_field', 'wprc_settings', 'integration_settings_section',
		array(
			'title'   => esc_html__( 'Add the form', 'report-content' ),
			'desc'    => esc_html__( 'Where do you want the form to be placed? This option will only work if you choose automatic integration', 'report-content' ),
			'id'      => 'automatic_form_position',
			'type'    => 'select',
			'options' => array(
				"above" => esc_html__( 'Above post content', 'report-content' ),
				"below" => esc_html__( 'Below post content', 'report-content' ),
			),
			'group'   => 'wprc_integration_settings',
		)
	);

	add_settings_field(
		'display_on', '', 'wprc_render_settings_field', 'wprc_settings', 'integration_settings_section',
		array(
			'title'   => esc_html__( 'Display form on', 'report-content' ),
			'desc'    => esc_html__( 'Select the section of your website where you want this form to appear', 'report-content' ),
			'id'      => 'display_on',
			'type'    => 'select',
			'options' => array(
				"everywhere"  => esc_html__( 'The whole site', 'report-content' ),
				"single_post" => esc_html__( 'Posts', 'report-content' ),
				'single_page' => esc_html__( 'Pages', 'report-content' ),
				'posts_pages' => esc_html__( 'Posts & Pages', 'report-content' ),
			),
			'group'   => 'wprc_integration_settings',
		)
	);

	add_settings_field(
		'email_recipients', '', 'wprc_render_settings_field', 'wprc_settings', 'email_settings_section',
		array(
			'title'   => esc_html__( 'On getting a new report send email to', 'report-content' ),
			'desc'    => esc_html__( 'Select email recipients', 'report-content' ),
			'id'      => 'email_recipients',
			'type'    => 'select',
			'options' => array(
				"none"         => esc_html__( 'No one', 'report-content' ),
				"author"       => esc_html__( 'Post Author', 'report-content' ),
				"admin"        => esc_html__( 'Blog administrator', 'report-content' ),
				"author_admin" => esc_html__( 'Author and administrator', 'report-content' ),
			),
			'group'   => 'wprc_email_settings',
		)
	);

	add_settings_field(
		'sender_name', '', 'wprc_render_settings_field', 'wprc_settings', 'email_settings_section',
		array(
			'title' => esc_html__( "Sender's Name", 'report-content' ),
			'desc'  => '',
			'id'    => 'sender_name',
			'type'  => 'text',
			'group' => 'wprc_email_settings',
		)
	);

	add_settings_field(
		'sender_address', '', 'wprc_render_settings_field', 'wprc_settings', 'email_settings_section',
		array(
			'title' => esc_html__( "Sender's Email Address", 'report-content' ),
			'desc'  => '',
			'id'    => 'sender_address',
			'type'  => 'text',
			'group' => 'wprc_email_settings',
		)
	);

	add_settings_field(
		'author_email_subject', '', 'wprc_render_settings_field', 'wprc_settings', 'email_settings_section',
		array(
			'title' => esc_html__( 'Author Email Subject', 'report-content' ),
			'desc'  => esc_html__( 'Subject of the email you want sent to authors. The report will also be appended.', 'report-content' ),
			'id'    => 'author_email_subject',
			'type'  => 'text',
			'group' => 'wprc_email_settings',
		)
	);

	add_settings_field(
		'author_email_content', '', 'wprc_render_settings_field', 'wprc_settings', 'email_settings_section',
		array(
			'title' => esc_html__( 'Author Email Content', 'report-content' ),
			'desc'  => sprintf(
			/* translators: 1: %AUTHOR%, 2: %POSTURL%, 3: %EDITURL% */
				esc_html__( 'This will be sent to the author of the post. The report will also be appended. %1$s will be replaced by author name %2$s will be replaced with a link to the post %3$s will be replaced with a link to the edit page', 'report-content' ),
				'<br/><b>%AUTHOR%</b>',
				'<br/><b>%POSTURL%</b>',
				'<br/><b>%EDITURL%</b>'
			),
			'id'    => 'author_email_content',
			'type'  => 'textarea',
			'group' => 'wprc_email_settings',
		)
	);

	add_settings_field(
		'admin_email_subject', '', 'wprc_render_settings_field', 'wprc_settings', 'email_settings_section',
		array(
			'title' => esc_html__( 'Admin Email Subject', 'report-content' ),
			'desc'  => esc_html__( 'Subject of the email you want sent to admins. The report will also be appended.', 'report-content' ),
			'id'    => 'admin_email_subject',
			'type'  => 'text',
			'group' => 'wprc_email_settings',
		)
	);

	add_settings_field(
		'admin_email_content', '', 'wprc_render_settings_field', 'wprc_settings', 'email_settings_section',
		array(
			'title' => esc_html__( 'Admin Email Content', 'report-content' ),
			'desc'  => sprintf(
			/* translators: 1: %POSTURL%, 2: %EDITURL%, 3: %REPORTSURL% */
				esc_html__( 'This will be sent to the blog admins. The report will also be appended. %1$s will be replaced with a link to the post %2$s will be replaced with a link to the edit page %3$s will be replaced by a link to reports page', 'report-content' ),
				'<br/><b>%POSTURL%</b>',
				'<br/><b>%EDITURL%</b>',
				'<br/><b>%REPORTSURL%</b>'
			),
			'id'    => 'admin_email_content',
			'type'  => 'textarea',
			'group' => 'wprc_email_settings',
		)
	);

	add_settings_field(
		'minimum_role_view', '', 'wprc_render_settings_field', 'wprc_settings', 'permissions_settings_section',
		array(
			'title'   => esc_html__( 'Minimum access level required to view the reports', 'report-content' ),
			'desc'    => esc_html__( "What's the minimum role that a logged in user needs to have in order to view reports", 'report-content' ),
			'id'      => 'minimum_role_view',
			'type'    => 'select',
			'options' => array(
				"install_plugins"      => esc_html__( 'Administrator', 'report-content' ),
				"moderate_comments"    => esc_html__( 'Editor', 'report-content' ),
				"edit_published_posts" => esc_html__( 'Author', 'report-content' ),
				"edit_posts"           => esc_html__( 'Contributor', 'report-content' ),
				"read"                 => esc_html__( 'Subscriber', 'report-content' ),
			),
			'group'   => 'wprc_permissions_settings',
		)
	);

	add_settings_field(
		'minimum_role_change', '', 'wprc_render_settings_field', 'wprc_settings', 'permissions_settings_section',
		array(
			'title'   => esc_html__( 'Minimum access level required to change status of/delete reports', 'report-content' ),
			'desc'    => esc_html__( "What's the minimum role that a logged in user needs to have in order to manipulate reports", 'report-content' ),
			'id'      => 'minimum_role_change',
			'type'    => 'select',
			'options' => array(
				"install_plugins"      => esc_html__( 'Administrator', 'report-content' ),
				"moderate_comments"    => esc_html__( 'Editor', 'report-content' ),
				"edit_published_posts" => esc_html__( 'Author', 'report-content' ),
				"edit_posts"           => esc_html__( 'Contributor', 'report-content' ),
				"read"                 => esc_html__( 'Subscriber', 'report-content' ),
			),
			'group'   => 'wprc_permissions_settings',
		)
	);

	add_settings_field(
		'login_required', '', 'wprc_render_settings_field', 'wprc_settings', 'permissions_settings_section',
		array(
			'title' => esc_html__( 'Users must be logged in to report content', 'report-content' ),
			'desc'  => '',
			'id'    => 'login_required',
			'type'  => 'checkbox',
			'group' => 'wprc_permissions_settings',
		)
	);

	add_settings_field(
		'use_akismet', '', 'wprc_render_settings_field', 'wprc_settings', 'permissions_settings_section',
		array(
			'title' => esc_html__( 'Use Akismet to filter reports', 'report-content' ),
			'desc'  => esc_html__( 'Akismet plugin is required for this feature.', 'report-content' ),
			'id'    => 'use_akismet',
			'type'  => 'checkbox',
			'group' => 'wprc_permissions_settings',
		)
	);

	add_settings_field(
		'disable_metabox', '', 'wprc_render_settings_field', 'wprc_settings', 'other_settings_section',
		array(
			'title' => esc_html__( 'Disabe metabox?', 'report-content' ),
			'desc'  => esc_html__( "Check if you don't want to display the metabox", 'report-content' ),
			'id'    => 'disable_metabox',
			'type'  => 'checkbox',
			'group' => 'wprc_other_settings',
		)
	);

	add_settings_field(
		'disable_db_saving', '', 'wprc_render_settings_field', 'wprc_settings', 'other_settings_section',
		array(
			'title' => esc_html__( "Don't save reports in database", 'report-content' ),
			'desc'  => esc_html__( "Check if you don't want to save reports in database", 'report-content' ),
			'id'    => 'disable_db_saving',
			'type'  => 'checkbox',
			'group' => 'wprc_other_settings',
		)
	);

	// Finally, we register the fields with WordPress
	register_setting( 'wprc_settings', 'wprc_form_settings', 'wprc_settings_validation' );
	register_setting( 'wprc_settings', 'wprc_integration_settings', 'wprc_settings_validation' );
	register_setting( 'wprc_settings', 'wprc_email_settings', 'wprc_settings_validation' );
	register_setting( 'wprc_settings', 'wprc_permissions_settings', 'wprc_settings_validation' );
	register_setting( 'wprc_settings', 'wprc_other_settings', 'wprc_settings_validation' );

} // end sandbox_initialize_theme_options 
add_action( 'admin_init', 'wprc_create_options' );

function wprc_settings_validation( $input ) {
	return $input;
}

function wprc_add_meta_boxes() {
	add_meta_box( "wprc_form_settings_metabox", esc_html__( 'Form Settings', 'report-content' ), "wprc_metaboxes_callback", "wprc_metaboxes", 'advanced', 'default', array( 'settings_section' => 'form_settings_section' ) );
	add_meta_box( "wprc_integration_settings_metabox", esc_html__( 'Integration Settings', 'report-content' ), "wprc_metaboxes_callback", "wprc_metaboxes", 'advanced', 'default', array( 'settings_section' => 'integration_settings_section' ) );
	add_meta_box( "wprc_email_settings_metabox", esc_html__( 'Email Settings', 'report-content' ), "wprc_metaboxes_callback", "wprc_metaboxes", 'advanced', 'default', array( 'settings_section' => 'email_settings_section' ) );
	add_meta_box( "wprc_permissions_settings_metabox", esc_html__( 'Security Settings', 'report-content' ), "wprc_metaboxes_callback", "wprc_metaboxes", 'advanced', 'default', array( 'settings_section' => 'permissions_settings_section' ) );
	add_meta_box( "wprc_other_settings_metabox", esc_html__( 'Other Settings', 'report-content' ), "wprc_metaboxes_callback", "wprc_metaboxes", 'advanced', 'default', array( 'settings_section' => 'other_settings_section' ) );
}

add_action( 'admin_init', 'wprc_add_meta_boxes' );

function wprc_metaboxes_callback( $post, $args ) {
	do_settings_fields( "wprc_settings", $args['args']['settings_section'] );
	submit_button( esc_html__( 'Save Changes', 'report-content' ), 'secondary' );
}

function wprc_render_settings_field( $args ) {
	$option_value = get_option( $args['group'] );
	?>
	<div class="row clearfix">
		<div class="col colone"><?php echo $args['title']; ?></div>
		<div class="col coltwo">
			<?php if ( $args['type'] == 'text' ): ?>
				<input type="text" id="<?php echo $args['id'] ?>"
				       title=""
				       name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
				       value="<?php echo esc_attr( $option_value[ $args['id'] ] ); ?>">
			<?php elseif ( $args['type'] == 'select' ): ?>
				<select name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
				        title=""
				        id="<?php echo $args['id']; ?>">
					<?php foreach ( $args['options'] as $key => $option ) { ?>
						<option <?php selected( $option_value[ $args['id'] ], $key );
						echo 'value="' . $key . '"'; ?>><?php echo $option; ?></option><?php } ?>
				</select>
			<?php elseif ( $args['type'] == 'checkbox' ): ?>
				<input type="hidden" name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>" value="0"/>
				<input type="checkbox"
				       title=""
				       name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
				       id="<?php echo $args['id']; ?>" value="1" <?php checked( $option_value[ $args['id'] ] ); ?> />
			<?php elseif ( $args['type'] == 'textarea' ): ?>
				<textarea name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
				          title=""
				          type="<?php echo $args['type']; ?>" cols=""
				          rows=""><?php if ( $option_value[ $args['id'] ] != "" ) {
						echo stripslashes( esc_textarea( $option_value[ $args['id'] ] ) );
					} ?></textarea>
			<?php elseif ( $args['type'] == 'multicheckbox' ):
				foreach ( $args['items'] as $key => $checkboxitem ):
					?>
					<input type="hidden" name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
					       value="0"/>
					<label
							for="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"><?php echo $checkboxitem; ?></label>
					<input type="checkbox" name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
					       id="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>" value="1"
					       <?php if ( $key == 'reason' ){ ?>checked="checked" disabled="disabled"<?php } else {
						checked( $option_value[ $args['id'] ][ $key ] );
					} ?> />
				<?php endforeach; ?>
			<?php elseif ( $args['type'] == 'multitext' ):
				foreach ( $args['items'] as $key => $textitem ):
					?>
					<label
							for="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"><?php echo $textitem; ?></label>
					<br/>
					<input type="text" id="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
					       name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
					       value="<?php echo esc_attr( $option_value[ $args['id'] ][ $key ] ); ?>"><br/>
				<?php endforeach; endif; ?>
		</div>
		<div class="col colthree">
			<small><?php echo $args['desc'] ?></small>
		</div>
	</div>
	<?php
}
