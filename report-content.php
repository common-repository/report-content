<?php
/*
Plugin Name: Report Content
Plugin URI: http://wpgurus.net/
Description: Inserts a secure form on specified pages so that your readers can report bugs, spam content and other problems.
Version: 1.7
Author: Hassan Akhtar
Author URI: http://wpgurus.net/
License: GPL2
Text Domain: report-content
Domain Path: /languages
*/

/**********************************************
 *
 * Load plugin text domain
 *
 ***********************************************/

function wprc_load_plugin_textdomain() {
	load_plugin_textdomain(
		'report-content', false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

add_action( 'plugins_loaded', 'wprc_load_plugin_textdomain' );

/**********************************************
 *
 * Creating the contentreports table on installation
 *
 ***********************************************/

define( 'WPRC_TABLE_VERSION', '1.1' );

function wprc_install() {
	global $wpdb;

	$table_name = wprc_table_name();
	$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;
	$table_up_to_date = get_option( "wprc_db_version" ) == WPRC_TABLE_VERSION;

	if ( $table_exists && $table_up_to_date ) {
		return;
	}

	wprc_create_table();
	update_option( 'wprc_db_version', WPRC_TABLE_VERSION );

	if ( $table_exists ) {
		return;
	}

	wprc_initialize_settings();
}

function wprc_table_name() {
	global $wpdb;
	return $wpdb->prefix . "contentreports";
}

function wprc_create_table() {
	global $wpdb;
	$table_name = wprc_table_name();

	$charset_collate = $wpdb->get_charset_collate();
	$sql = "
		CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		reason tinytext NOT NULL,
		status VARCHAR(55) NOT NULL,
		details text DEFAULT '' NULL,
		reporter_name VARCHAR(55) DEFAULT '' NULL,
		reporter_email VARCHAR(55) DEFAULT '' NULL,
		post_id mediumint(9) NOT NULL,
		UNIQUE KEY id (id) ) $charset_collate;
	";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

function wprc_initialize_settings() {
	$wprc_form_settings = array(
		'active_fields'         => array( 'reason' => 1, 'reporter_name' => 1, 'reporter_email' => 1, 'details' => 1 ),
		'required_fields'       => array( 'reason' => 1, 'reporter_name' => 1, 'reporter_email' => 1, 'details' => 1 ),
		'report_reasons'        => esc_html__( "Copyright Infringement\nSpam\nInvalid Contents\nBroken Links", 'report-content' ),
		'slidedown_button_text' => esc_html__( 'Report Content', 'report-content' ),
		'submit_button_text'    => esc_html__( 'Submit Report', 'report-content' ),
		'color_scheme'          => 'yellow',
	);
	$wprc_integration_settings = array(
		'integration_type'        => 'automatically',
		'automatic_form_position' => 'above',
		'display_on'              => 'posts_pages',
	);
	$wprc_email_settings = array(
		'email_recipients'     => 'none',
		'sender_name'          => '',
		'sender_address'       => '',
		'author_email_subject' => esc_html__( 'Your article has been flagged', 'report-content' ),
		'author_email_content' => sprintf(
		/* translators: 1: %AUTHOR%, 2: site name, 3: %POSTURL%, 4: %EDITURL% */
			esc_html__( "Hi %1\$s,\n\nYour article on %2\$s has been flagged.\n\nView the article: %3\$s\nEdit the article: %4\$s", 'report-content' ),
			'%AUTHOR%',
			get_option( 'blogname' ),
			'%POSTURL%',
			'%EDITURL%'
		),
		'admin_email_subject'  => esc_html__( 'New report submitted', 'report-content' ),
		'admin_email_content'  => sprintf(
		/* translators: 1: site name, 2: %POSTURL%, 3: %EDITURL%, 4: %REPORTSURL% */
			esc_html__( "Hi admin,\n\nAn article on your website %1\$s has been flagged.\n\nView the article: %2\$s\nEdit the article: %3\$s\nView all reports: %4\$s", 'report-content' ),
			get_option( 'blogname' ),
			'%POSTURL%',
			'%EDITURL%',
			'%REPORTSURL%'
		),
	);
	$wprc_permissions_settings = array(
		'minimum_role_view'   => 'install_plugins',
		'minimum_role_change' => 'install_plugins',
		'login_required'      => 0,
		'use_akismet'         => 1,
	);
	$wprc_other_settings = array(
		'disable_metabox'   => 0,
		'disable_db_saving' => 0,
	);
	update_option( 'wprc_form_settings', $wprc_form_settings );
	update_option( 'wprc_integration_settings', $wprc_integration_settings );
	update_option( 'wprc_email_settings', $wprc_email_settings );
	update_option( 'wprc_permissions_settings', $wprc_permissions_settings );
	update_option( 'wprc_other_settings', $wprc_other_settings );
}

register_activation_hook( __FILE__, 'wprc_install' );

function wprc_rollback() {
	delete_option( 'wprc_db_version' );
	delete_option( 'wprc_form_settings' );
	delete_option( 'wprc_integration_settings' );
	delete_option( 'wprc_email_settings' );
	delete_option( 'wprc_permissions_settings' );
	delete_option( 'wprc_other_settings' );
	global $wpdb;
	$table_name = $wpdb->prefix . "contentreports";
	return $wpdb->query( "DROP TABLE $table_name" );
}

register_uninstall_hook( __FILE__, 'wprc_rollback' );

/**********************************************
 *
 * Enqueuing scripts and styles
 *
 ***********************************************/

function wprc_enqueue_resources() {
	wp_enqueue_style( 'wprc-style', plugins_url( 'static/css/styles.css', __FILE__ ) );
	wp_enqueue_script( 'wprc-script', plugins_url( 'static/js/scripts.js', __FILE__ ), array( 'jquery' ) );
	wp_localize_script( 'wprc-script', 'wprcajaxhandler', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_enqueue_scripts', 'wprc_enqueue_resources' );

/**********************************************
 *
 * Automatically insert the report form in posts
 *
 ***********************************************/

function wprc_add_report_button_filter( $content ) {
	$integration_options = get_option( 'wprc_integration_settings' );
	if ( ( $integration_options && $integration_options['integration_type'] == 'manually' ) ||
	     ( $integration_options['display_on'] == 'single_post' && ! is_single() ) ||
	     ( $integration_options['display_on'] == 'single_page' && ! is_page() ) ||
	     ( $integration_options['display_on'] == 'posts_pages' && ! is_singular() )
	) {
		return $content;
	}

	ob_start();
	include "inc/report-form.php";
	$form_html = ob_get_contents();
	ob_end_clean();

	if ( $integration_options && $integration_options['automatic_form_position'] == 'below' ) {
		return $content . $form_html;
	}
	return $form_html . $content;
}

add_filter( 'the_content', 'wprc_add_report_button_filter', 99 );

function wprc_report_submission_form() {
	include "inc/report-form.php";
}

function wprc_neutralize_excerpt( $content ) {
	remove_filter( 'the_content', 'wprc_add_report_button_filter' );
	return $content;
}

add_filter( 'get_the_excerpt', 'wprc_neutralize_excerpt', 5 );

/**********************************************
 *
 * Database functions
 *
 ***********************************************/

function wprc_insert_data( $args ) {
	$other_options = get_option( 'wprc_other_settings' );
	if ( $other_options['disable_db_saving'] ) {
		return true;
	}
	global $wpdb;
	$table = $wpdb->prefix . "contentreports";
	$result = $wpdb->insert( $table, $args );
	if ( $result ) {
		return $wpdb->insert_id;
	}
	return false;
}

function wprc_get_post_reports( $post_id ) {
	global $wpdb;
	$table = $wpdb->prefix . "contentreports";
	$query = "SELECT * FROM $table WHERE post_id = $post_id ORDER BY time DESC";
	return $wpdb->get_results( $query, ARRAY_A );
}

function wprc_delete_post_reports( $post_id ) {
	global $wpdb;
	$table = $wpdb->prefix . "contentreports";
	$query = $wpdb->prepare( "DELETE FROM $table WHERE post_id = %d", $post_id );
	return $wpdb->query( $query );
}

/**********************************************
 *
 * Cleanup on post deletion
 *
 ***********************************************/

function wprc_on_post_delete( $post_id ) {
	wprc_delete_post_reports( $post_id );
}

add_action( 'delete_post', 'wprc_on_post_delete' );

/**********************************************
 *
 * Mailing function
 *
 ***********************************************/

function wprc_mail( $report ) {
	$post_id = $report['post_id'];
	$post_url = get_post_permalink( $post_id );
	$post_edit_url = admin_url( "post.php?post=$post_id&action=edit" );
	$reports_url = admin_url( 'admin.php?page=wprc_reports_page' );
	$email_options = get_option( 'wprc_email_settings' );
	$admin_emails_sent = true;
	$author_emails_sent = true;
	$headers = array();
	if ( ! $email_options || $email_options['email_recipients'] == 'none' ) {
		return true;
	}

	if ( $email_options['sender_name'] && $email_options['sender_address'] ) {
		$headers[] = 'From: ' . $email_options['sender_name'] . ' <' . $email_options['sender_address'] . '>';
	}
	$report_string = "\n\nReport:\n\n" . $report['reason'] . "\n\n" . $report['details'];

	if ( 'admin' == $email_options['email_recipients'] || 'author_admin' == $email_options['email_recipients'] ) {
		$to_admin = get_option( 'admin_email' );
		$email_options['admin_email_content'] = str_replace( '%POSTURL%', $post_url, $email_options['admin_email_content'] );
		$email_options['admin_email_content'] = str_replace( '%EDITURL%', $post_edit_url, $email_options['admin_email_content'] );
		$email_options['admin_email_content'] = str_replace( '%REPORTSURL%', $reports_url, $email_options['admin_email_content'] );
		$admin_emails_sent = wp_mail( $to_admin, $email_options['admin_email_subject'], $email_options['admin_email_content'] . $report_string, $headers );
	}

	if ( 'author' == $email_options['email_recipients'] || 'author_admin' == $email_options['email_recipients'] ) {
		$post = get_post( $post_id );
		$author = get_user_by( 'id', $post->post_author );
		$email_options['author_email_content'] = str_replace( '%AUTHOR%', $author->display_name, $email_options['author_email_content'] );
		$email_options['author_email_content'] = str_replace( '%POSTURL%', $post_url, $email_options['author_email_content'] );
		$email_options['author_email_content'] = str_replace( '%EDITURL%', $post_edit_url, $email_options['author_email_content'] );
		if ( $author->user_email != $to_admin ) {
			$author_emails_sent = wp_mail( $author->user_email, $email_options['author_email_subject'], $email_options['author_email_content'] . $report_string, $headers );
		}
	}

	return ( $author_emails_sent && $admin_emails_sent );
}

/**********************************************
 *
 * Check for errors, insert into DB and send emails
 *
 ***********************************************/

function wprc_add_report() {
	$message['success'] = 0;
	$permissions = get_option( 'wprc_permissions_settings' );
	if ( $permissions['login_required'] && ! is_user_logged_in() ) {
		$message['message'] = wprc_login_required_message();

		die( json_encode( $message ) );
	}

	$form_options = get_option( 'wprc_form_settings' );
	$active_fields = $form_options['active_fields'];
	$required_fields = $form_options['required_fields'];
	$data = stripslashes_deep( $_POST );
	foreach ( $required_fields as $key => $field ) {
		if ( $field && $active_fields[ $key ] && ! $data[ $key ] ) {
			$message['message'] = esc_html__( 'You missed a required field', 'report-content' );
			die( json_encode( $message ) );
		}
	}

	if ( $active_fields['reporter_email'] && $data['reporter_email'] && ! is_email( $data['reporter_email'] ) ) {
		$message['message'] = esc_html__( 'Email address invalid', 'report-content' );
		die( json_encode( $message ) );
	}

	$details = $data['details'];
	$reporter_name = ( isset( $data['reporter_name'] ) ) ? $data['reporter_name'] : '';
	$reporter_email = ( isset( $data['reporter_email'] ) ) ? $data['reporter_email'] : '';

	$new_report = array(
		'reason'         => sanitize_text_field( $data['reason'] ),
		'status'         => 'new',
		'time'           => current_time( 'mysql' ),
		'details'        => sanitize_text_field( $details ),
		'reporter_name'  => sanitize_text_field( $reporter_name ),
		'reporter_email' => sanitize_email( $reporter_email ),
		'post_id'        => intval( $data['id'] ),
	);
	if ( wprc_is_spam( $new_report ) ) {
		$message['message'] = esc_html__( 'Your submission has been marked as spam by our filters', 'report-content' );
		die( json_encode( $message ) );
	}

	if ( ! wprc_insert_data( $new_report ) ) {
		$message['message'] = esc_html__( 'An unexpected error occurred. Please try again later', 'report-content' );
		die( json_encode( $message ) );
	}

	wprc_mail( $new_report );
	$message['success'] = 1;
	$message['message'] = esc_html__( 'Report submitted successfully!', 'report-content' );
	die( json_encode( $message ) );
}

add_action( 'wp_ajax_wprc_add_report', 'wprc_add_report' );
add_action( 'wp_ajax_nopriv_wprc_add_report', 'wprc_add_report' );

/**********************************************
 *
 * Adding new columns to edit.php page
 *
 ***********************************************/

function wprc_add_admin_column_headers( $headers ) {
	$permission_options = get_option( 'wprc_permissions_settings' );
	if ( ! current_user_can( $permission_options['minimum_role_view'] ) ) {
		return $headers;
	}

	$headers['wprc_post_reports'] = esc_html__( 'Post Reports', 'report-content' );
	return $headers;
}

add_filter( 'manage_posts_columns', 'wprc_add_admin_column_headers', 10, 2 );

function wprc_add_admin_column_contents( $header, $something ) {
	if ( $header == 'wprc_post_reports' ) {
		global $post;
		$wprc_post_reports = wprc_get_post_reports( $post->ID );
		echo '<a href="' . get_edit_post_link( $post->ID ) . '#wprc-reports">' . count( $wprc_post_reports ) . '</a>';
	}
}

add_filter( 'manage_posts_custom_column', 'wprc_add_admin_column_contents', 10, 2 );

/**********************************************
 *
 * Prepare the report for akismet and run tests
 *
 ***********************************************/

function wprc_is_spam( $report ) {
	$permission_options = get_option( 'wprc_permissions_settings' );
	if ( ! $permission_options['use_akismet'] ) {
		return false;
	}
	if ( wprc_akismet_failed(
		$report['details'],
		$report['reporter_name'],
		$report['reporter_email']
	) ) {
		return true;
	}
	return false;
}

/**********************************************
 *
 * Pass the report through Akismet filters to
 * make sure it isn't spam
 *
 ***********************************************/

function wprc_akismet_failed( $content, $name, $email ) {
	$akismet_available = method_exists( 'Akismet', 'build_query' )
	                     && method_exists( 'Akismet', 'http_post' );
	if ( ! $akismet_available ) {
		return false;
	}

	$args = array(
		'comment_content'      => $content,
		'comment_author'       => $name,
		'comment_author_email' => $email,
		'user_ip'              => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null,
		'user_agent'           => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null,
		'referrer'             => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : get_permalink(),
		'blog'                 => get_option( 'home' ),
		'blog_lang'            => get_locale(),
		'blog_charset'         => get_option( 'blog_charset' ),
	);

	$response = Akismet::http_post( Akismet::build_query( $args ), 'comment-check' );
	if ( $response[1] == 'true' ) {
		return true;
	}

	return false;
}

function wprc_login_required_message() {
	return sprintf(
	/* translators: 1: login link */
		esc_html__( 'To report this post you need to %1$s first.', 'report-content' ),
		sprintf(
			'<a href="%s" title="%s">%s</a>',
			wp_login_url(),
			esc_html__( 'Login', 'report-content' ),
			esc_html__( 'login', 'report-content' )
		)
	);
}

/**********************************************
 *
 * Include the necessary items
 *
 ***********************************************/

include( 'inc/meta-boxes.php' );

include( 'inc/reports-list.php' );

include( 'inc/options-panel.php' );