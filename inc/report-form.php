<?php
global $post;
$form_options = get_option( 'wprc_form_settings' );
$permissions = get_option( 'wprc_permissions_settings' );
$required_fields = $form_options['required_fields'];
$reasons = explode( "\n", $form_options['report_reasons'] );
?>
<div class="wprc-container <?php echo $form_options['color_scheme']; ?>">
	<button type="button" class="wprc-switch"><?php echo $form_options['slidedown_button_text']; ?></button>
	<div class="wprc-content">
		<div class="wprc-message">
		</div>
		<div class="wprc-form">
			<?php if ( $permissions['login_required'] && ! is_user_logged_in() ): ?>
				<?php echo wprc_login_required_message(); ?>
			<?php else: ?>
				<div class="left-section">
					<ul>
						<li class="list-item-reason">
							<label for="input-reason-<?php echo $post->ID; ?>">
								<?php esc_html_e( 'Issue:', 'report-content' ); ?>
								<span class="required-sign">*</span>
							</label><br/>
							<select id="input-reason-<?php echo $post->ID; ?>" class="input-reason">
								<?php foreach ( $reasons as $key => $reason ): ?>
									<option><?php echo esc_attr( $reason ); ?></option>
								<?php endforeach; ?>
							</select>
						</li>
						<li class="list-item-name">
							<?php if ( $form_options['active_fields']['reporter_name'] ): ?>
								<label for="input-name-<?php echo $post->ID; ?>">
									<?php esc_html_e( 'Your Name:', 'report-content' ); ?>
									<?php if ( $required_fields['reporter_name'] ): ?>
										<span class="required-sign">*</span>
									<?php endif; ?>
								</label><br/>
								<input type="text" id="input-name-<?php echo $post->ID; ?>"
								       class="input-name wprc-input"/>
							<?php endif; ?>
						</li>
						<li class="list-item-email">
							<?php if ( $form_options['active_fields']['reporter_email'] ): ?>
								<label for="input-email-<?php echo $post->ID; ?>">
									<?php esc_html_e( 'Your Email:', 'report-content' ); ?>
									<?php if ( $required_fields['reporter_email'] ): ?>
										<span class="required-sign">*</span>
									<?php endif; ?>
								</label><br/>
								<input type="text" id="input-email-<?php echo $post->ID; ?>"
								       class="input-email wprc-input"/>
							<?php endif; ?>
						</li>
					</ul>
				</div>
				<div class="right-section">
					<ul>
						<li class="list-item-details">
							<?php if ( $form_options['active_fields']['details'] ): ?>
								<label for="input-details-<?php echo $post->ID; ?>">
									<?php esc_html_e( 'Details:', 'report-content' ); ?>
									<?php if ( $required_fields['details'] ): ?>
										<span class="required-sign">*</span>
									<?php endif; ?>
								</label><br/>
								<textarea id="input-details-<?php echo $post->ID; ?>"
								          class="input-details wprc-input"></textarea>
							<?php endif; ?>
						</li>
					</ul>
				</div>
				<div class="clear"></div>
				<input type="hidden" class="post-id" value="<?php echo $post->ID; ?>">
				<button type="button" class="wprc-submit"><?php echo $form_options['submit_button_text'] ?></button>
				<img class="loading-img" style="display:none;"
				     src="<?php echo plugins_url( 'static/img/loading.gif', dirname( __FILE__ ) ); ?>"/>
			<?php endif; ?>
		</div>
	</div>
</div>
