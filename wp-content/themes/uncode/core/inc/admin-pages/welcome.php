<?php
/**
 * Admin View: Page - Status Report
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function uncode_let_to_num( $size ) {
  $l   = substr( $size, -1 );
  $ret = substr( $size, 0, -1 );
  switch ( strtoupper( $l ) ) {
    case 'P':
      $ret *= 1024;
    case 'T':
      $ret *= 1024;
    case 'G':
      $ret *= 1024;
    case 'M':
      $ret *= 1024;
    case 'K':
      $ret *= 1024;
  }
  return $ret;
}

if ( class_exists('UncodeCommunicator') ) {
	$communicator = new UncodeCommunicator();
	$unread_mess = $communicator->countUnreadItems();
}

if ( class_exists('UncodeHotfix') ) {
	$hotfix = new UncodeHotfix('http://static.undsgn.com/uncode/endpoint');
	$test = array(
		'key' => 'merged',
	    'value' => false
	);
	$patches_count = $hotfix->countCommittedPatches($test);
}

if(isset($_POST['install_uncode'])) {
    $_SESSION['ignore_walkthrough'] = null;
    unset($_SESSION['ignore_walkthrough']);
    ?>
    <script type="text/javascript">window.location.href="<?php echo admin_url(); ?>";</script>
    <?php
}

if (!empty($_SESSION['ignore_walkthrough'])) {
?>
<div class="update-nag">
    <?php echo esc_html__('You need to install Uncode before you can use it.', 'uncode'); ?>
    <form method="POST">
        <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Run Installer', 'uncode' ); ?>" name="install_uncode">
    </form>
</div>
<?php exit; } ?>

<?php
$envato_toolkit_active = false;
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'envato-wordpress-toolkit-master/index.php' ) ) {
	$envato_toolkit_active = true;
}
?>

<div class="wrap uncode-wrap" id="option-tree-settings-api">

	<?php echo uncode_admin_panel_page_title( 'welcome' ); ?>

	<div class="uncode-admin-panel">
		<?php //echo uncode_admin_panel_title(); ?>
		<?php echo uncode_admin_panel_menu( 'welcome' ); ?>

		<div class="uncode-admin-panel__content uncode-admin-panel__content--two-cols">

			<div class="uncode-admin-panel__left">
				<?php if ( defined('ENVATO_HOSTED_SITE') ) { ?>
					<h2 class="uncode-admin-panel__heading"><?php esc_html_e( 'Welcome', 'uncode' ); ?></h2>
				<?php } else { ?>
					<h2 class="uncode-admin-panel__heading"><?php esc_html_e( 'Registration', 'uncode' ); ?></h2>
				<?php } ?>

				<div class="uncode-info-box">
					<p class="uncode-admin-panel__description"><?php
						if ( ! defined('ENVATO_HOSTED_SITE') ) {
							printf(wp_kses( __( '%s is a pixel perfect creative multiuse Wordpress Theme based on WPBakery Page Builder (Visual Composer). It’s clean and modern for any need.<br>With over 40+ pre-made concepts ready to go or combine, anything you can think of can be built. Build beautiful, intelligent websites.','uncode'), array( 'br' => array() ) ), UNCODE_NAME );
						} else {
							printf(esc_html__('According to the %s you are only allowed to use this theme on a single domain. Creating multiple unregistered installations is a copyright violation. Please register your product.','uncode'), '<a tabindex="-1" href="' . esc_url('//themeforest.net/licenses/standard') . '" target="_blank">'.esc_html__('Envato License Terms','uncode').'</a>');
						}
					?></p>

					<?php if ( ! defined('ENVATO_HOSTED_SITE') ) {
						// This flag checks if the license is already used in another domain
						$license_already_in_use = false;

						// This flag checks if we are deregistering a purchase code - We need
						// it becasuse the $communicator->unRegisterDomains()
						// runs after the form submission
						$is_deregistering_license = false;

						if ( class_exists( 'Envato' ) ) {
							$envato = new Envato();
							$envato->setAPIKey( ENVATO_KEY );
							$toolkitDataEmpty = $envato->toolkitDataEmpty();
							$toolkitData = $envato->getToolkitData();
						} else {
							$toolkitDataEmpty = $toolkitData = false;
						}

						// There is a borderline case:
						// - site A was activated
						// - site B was activated after A (so A is now deactivated)
						// - for some reason the user deregister his license in site B
						//
						// In this case we still have the toolkitData in site A. But
						// we still want site A deactivated. So we can check if the
						// purchase code is connected to any domain. If not, we delete
						// the toolkitData on site A.
						if ( function_exists( 'licenseNeedsDeactivation' ) ) {
							$needs_to_be_deactivated = licenseNeedsDeactivation( $toolkitData );

							if ( $needs_to_be_deactivated ) {
								$toolkitData = false;
							}
						}

						if ( function_exists( 'isInstallationLegit' ) ) {
							$installationLegit = isInstallationLegit();

							if ( ! $installationLegit ) {
								$license_already_in_use = true;
							}
						} else {
							$installationLegit = false;
						}

						if ( isset( $_POST[ 'change_license' ] ) && class_exists( 'UncodeCommunicator' ) ) {
							$is_deregistering_license = true;
							$communicator->unRegisterDomains( $toolkitData[ 'purchase_code' ] );
							delete_option( 'uncode-wordpress-data' );
						}

						if ( ! $installationLegit ) {
							$license_ok = false;
						}

						if ( ! isset( $toolkitData[ 'purchase_code' ] ) ) {
							$license_ok = false;
						} else {
							$license_ok = $communicator->isPurchaseCodeLegit( $toolkitData[ 'purchase_code' ] );
						}

						if ( $is_deregistering_license ) {
							$license_ok = false;
						}

						$connected_domain = $communicator->getConnectedDomains( $toolkitData[ 'purchase_code' ] );
						?>

						<form method="POST" id="uncode_api_credentials_form" class="uncode-registration-form uncode-registration-form--<?php echo $license_ok ? 'license-ok' : ''; ?> <?php echo $envato_toolkit_active ? 'uncode-registration-form--toolkit-active' : 'toolkit-not-active'; ?> uncode-registration-form--<?php echo $license_already_in_use ? 'already-in-use' : ''; ?>">

							<div class="format-setting-wrap">
								<div class="format-setting-label">
									<h3 class="label"><?php esc_html_e('Envato Username', 'uncode'); ?></h3>
								</div>
								<div class="format-setting has-desc">
									<div class="description"><?php printf(esc_html__('Please insert your Envato username. %s.','uncode'), '<a tabindex="-1" href="' . esc_url('//support.undsgn.com/hc/en-us/articles/115002308845#envato-username') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></div>
									<div class="format-setting-inner">
										<input type="text" name="envato_username" id="envato_username" class="widefat option-tree-ui-input" value="<?php echo ( ! $is_deregistering_license && isset( $toolkitData[ 'user_name' ] ) && $toolkitData[ 'user_name' ] ) ? $toolkitData[ 'user_name' ] : ''; ?>" <?php echo $license_ok && ! $license_already_in_use ? 'disabled' : ''; ?>>
									</div>
								</div>
							</div>

							<div class="format-setting-wrap">
								<div class="format-setting-label">
									<h3 class="label"><?php esc_html_e('Envato API-Key', 'uncode'); ?></h3>
								</div>
								<div class="format-setting has-desc">
									<div class="description"><?php printf(esc_html__('Please insert your Envato API Key. %s.','uncode'), '<a tabindex="-1" href="' . esc_url('//support.undsgn.com/hc/en-us/articles/115002308845#envato-api-key') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></div>
									<div class="format-setting-inner">
										<input type="text" name="envato_api_key" id="envato_api_key" class="widefat option-tree-ui-input" value="<?php echo ( ! $is_deregistering_license && isset( $toolkitData[ 'api_key' ] ) && $toolkitData[ 'api_key' ] ) ? $toolkitData[ 'api_key' ] : ''; ?>" <?php echo $license_ok && ! $license_already_in_use ? 'disabled' : ''; ?>>
									</div>
								</div>
							</div>

							<div class="format-setting-wrap">
								<div class="format-setting-label">
									<h3 class="label"><?php esc_html_e('Envato purchase code', 'uncode'); ?></h3>
								</div>
								<div class="format-setting has-desc">
									<div class="description"><?php printf(esc_html__('Please insert your Envato purchase code. %s.','uncode'), '<a tabindex="-1" href="' . esc_url('//support.undsgn.com/hc/en-us/articles/115002308845#envato-purchase-code') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></div>
									<div class="format-setting-inner">
										<input type="text" name="envato_purchase_code" id="envato_purchase_code" class="widefat option-tree-ui-input" value="<?php echo ( ! $is_deregistering_license && isset( $toolkitData[ 'purchase_code' ] ) && $toolkitData[ 'purchase_code' ] ) ? $toolkitData[ 'purchase_code' ] : ''; ?>" <?php echo $license_ok && ! $license_already_in_use ? 'disabled' : ''; ?>>
									</div>
								</div>
							</div>

							<div>

								<p style="display: none;" class="uncode-admin-panel__description uncode-admin-registration-info uncode-admin-registration-info--license-invalid uncode-ui-notice uncode-ui-notice--error" id="license_error"><?php esc_html_e('You have entered invalid credentials, or your Envato account does not include Uncode among your purchased items.', 'uncode'); ?></p>
								<p style="display: none;" class="uncode-admin-panel__description uncode-admin-registration-info uncode-admin-registration-info--license-invalid uncode-ui-notice uncode-ui-notice--error" id="license_empty"><?php esc_html_e('Please fill out all required fields.', 'uncode'); ?></p>

								<?php if ( $license_already_in_use ) : ?>
									<p class="uncode-admin-panel__description uncode-admin-registration-info uncode-admin-registration-info--license-invalid uncode-ui-notice uncode-ui-notice--error">
										<?php printf( wp_kses( __( 'This product is in use on another domain: <span>%s</a>', 'uncode' ), array( 'span' => array() ) ), $connected_domain ); ?><br>
										<?php printf(esc_html__('Are you using this theme for a new site? Please purchase a %s or move your license on this domain with the button below.', 'uncode' ), '<a tabindex="-1" href="' . esc_url('//themeforest.net/item/uncode-creative-multiuse-wordpress-theme/13373220?utm_source=undsgn_support&ref=undsgn&license=regular&open_purchase_for_item_id=13373220&purchasable=source') . '" target="_blank">'.esc_html__('new license','uncode').'</a>'); ?></p>

								<?php endif; ?>

								<?php if ( ! $envato_toolkit_active ) : ?>
									<p class="uncode-registration-form__warning uncode-ui-notice uncode-ui-notice--error"><?php printf( wp_kses( __( 'Envato WordPress Toolkit plugin must be active to register your theme. <a href="%1$s"> Click here to activate it.</a>', 'uncode' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( admin_url( 'admin.php?page=uncode-plugins' ) ) ); ?></p>
								<?php endif;

								$register_button_text = $toolkitData && ! $is_deregistering_license ? '' : __( 'Register your theme', 'uncode' );

								if ( $toolkitData && $license_already_in_use ) :
									$register_button_text = __( 'Activate on this domain', 'uncode' );

									?>
									<input type="hidden" id="license_force_activation" name="force_activation" value="1">
									<?php
								endif;
								?>
								<?php if ( $register_button_text ) : ?>
									<button class="button button-primary" type="submit"<?php if ( ! $envato_toolkit_active ) echo ' disabled'; ?> id="envato_update_info" name="envato_update_info"<?php echo ! $envato_toolkit_active ? ' disabled' : ''; ?>><span><span class="uncode-ot-spinner"></span><?php echo esc_attr( $register_button_text ); ?></span></button>
								<?php endif ?>
							</div>

						</form>

						<?php if ( $toolkitData && ! $is_deregistering_license && ! $license_already_in_use ) : ?>
							<form id="change_license_form" method="POST">
								<button type="submit" class="button button-primary" name="change_license"><?php echo esc_html__( 'Deregister your product', 'uncode' ); ?></button>
							</form>
						<?php endif; ?>

					<?php } ?>
				</div><!-- .uncode-info-box -->

			</div><!-- .uncode-admin-panel__left -->

			<div class="uncode-admin-panel__right">
				<h2 class="uncode-admin-panel__heading"><?php esc_html_e( 'System Status', 'uncode' ); ?></h2>

				<p class="uncode-admin-panel__description"><?php
					if ( defined('ENVATO_HOSTED_SITE') ) {
						 esc_html_e("Under System Status, you can find important information about your WordPress setup.", "uncode");
					} else {
						printf(esc_html__("Under System Status, you can find important information about your server setup. If you see red errors that indicate problems, it is likely that you're not in compliance with Uncode's %s.", "uncode"), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213453949') . '" target="_blank">'.esc_html__('Server Requirements','uncode').'</a>');
					}
				?></p>

				<table class="widefat system-status-list" cellspacing="0" id="status">
					<tbody>
						<tr>
							<td data-export-label="License"><?php echo esc_html__("Theme version", "uncode"); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'The version of Uncode installed on your site.', 'uncode' ) . '</small>'; ?></td>
							<td>
							<?php $theme_data = wp_get_theme();
							if ( $theme_data->parent() )
								$parent_theme_version = $theme_data->parent()->version;
							else
								$parent_theme_version = UNCODE_VERSION;

							echo esc_attr($parent_theme_version);
							?>
							</td>
			            </tr>
		            <?php if ( is_child_theme() ) : ?>
						<tr>
							<td data-export-label="Child Theme"><?php echo esc_html__("Child Theme", "uncode"); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Name and version of child theme installed on your site.', 'uncode' ) . '</small>'; ?></td>
							<td>
							<?php printf( wp_kses_post( _x( '%s - %s', 'Child theme name and version', 'uncode' ) ), $theme_data->get( 'Name' ), $theme_data->get( 'Version' ) ) . '</small>'; ?>
							</td>
			            </tr>
			        <?php endif; ?>
			        <?php if ( ! defined('ENVATO_HOSTED_SITE') ) : ?>

						<tr>
							<td data-export-label="Product Registration"><?php echo esc_html__("Product Registration", "uncode"); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Please validate your product license as outlined in Envato\'s license terms.', 'uncode' ) . '</small>'; ?></td>
							<td>
							<?php
							if ( $license_ok && !$license_already_in_use ) {
								echo '<mark class="yes">' . esc_html__( 'Theme registered.', 'uncode' ) . '</mark>';
							} else {
								echo '<mark class="error">' . esc_html__( 'Not registered.', 'uncode' ) . '</mark>';
							}
							?>
							</td>
						</tr>
				        <?php endif; ?>
						<tr>
							<td data-export-label="WP Version"><?php esc_html_e( 'WP Version', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'The version of WordPress installed on your site.', 'uncode' ) . '</small>'; ?></td>
							<td><?php bloginfo('version'); ?></td>
						</tr>
						<tr>
							<td data-export-label="Language"><?php esc_html_e( 'Language', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'The current language used by WordPress. Default = English.', 'uncode' ) . '</small>'; ?></td>
							<td><?php echo get_locale() ?></td>
						</tr>
						<tr>
							<td data-export-label="WP Multisite"><?php esc_html_e( 'WP Multisite', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Whether or not you have WordPress Multisite enabled.', 'uncode' ) . '</small>'; ?></td>
							<td><?php if ( is_multisite() ) echo '&#10004;'; else echo '&ndash;'; ?></td>
						</tr>
						<tr>
							<td data-export-label="Frontend Stylesheet"><?php esc_html_e( 'Frontend Stylesheet', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_html__( 'Uncode is generating a stylesheet when the options are saved. The file must be writtable.', 'uncode' ) . '</small>'; ?></td>
							<td><?php
								global $wp_filesystem;
								if (empty($wp_filesystem)) {
									require_once (ABSPATH . '/wp-admin/includes/file.php');
								}
								$mod_file = (defined('FS_CHMOD_FILE')) ? FS_CHMOD_FILE : false;
								$front_css = get_template_directory() . '/library/css/';
								$front_css_file = $front_css . 'style-custom.css';
								$creds = request_filesystem_credentials($front_css, '', false, false, array());
								$can_write_front = true;
								if (!!$creds) {
									/* initialize the API */
									if ( ! WP_Filesystem($creds) ) {
										/* any problems and we exit */
										$can_write_front = false;
									}
								}
								$filename = trailingslashit($front_css).'test.txt';
								if ( ! $wp_filesystem->put_contents( $filename, 'Test file contents', $mod_file) ) {
									$can_write_front = false;
								} else {
									$wp_filesystem->delete( $filename );
								}

								$file_is_writable = wp_is_writable($front_css_file);

								$front_css = '..' . substr($front_css, strpos($front_css,"/wp-content"));
								$front_css_file = $front_css . 'style-custom.css';

								if ($can_write_front) {
									if ( ! $file_is_writable )
										printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this file <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $front_css_file  );
									else
										echo '<mark class="yes">' . '<code class="yes">' . $front_css .'</code></mark> ';
								} else {
									printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this folder <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $front_css  );
								}
							?></td>
						</tr>
						<tr>
							<td data-export-label="Backend Stylesheet"><?php esc_html_e( 'Backend Stylesheet', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Uncode is generating a stylesheet when the options are saved. The file must be writtable.', 'uncode' ) . '</small>'; ?></td>
							<td><?php
								$mod_file = (defined('FS_CHMOD_FILE')) ? FS_CHMOD_FILE : false;
								$back_css = get_template_directory() . '/core/assets/css/';
								$back_css_file = $back_css . 'admin-custom.css';
								$creds = request_filesystem_credentials($back_css, '', false, false, array());
								$can_write_back = true;
								if (!!$creds) {
									/* initialize the API */
									if ( ! WP_Filesystem($creds) ) {
										/* any problems and we exit */
										$can_write_back = false;
									}
								}
								$filename = trailingslashit($back_css).'test.txt';
								if ( ! $wp_filesystem->put_contents( $filename, 'Test file contents', $mod_file) ) {
									$can_write_back = false;
								} else {
									$wp_filesystem->delete( $filename );
								}

								$back_is_writable = wp_is_writable($back_css_file);

								$back_css = '..' . substr($back_css, strpos($back_css,"/wp-content"));
								$back_css_file = $back_css . 'admin-custom.css';

								if ($can_write_back) {
									if ( ! $back_is_writable )
										printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this file <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $back_css_file  );
									else
										echo '<mark class="yes">' . '<code class="yes">' . $back_css .'</code></mark> ';
								} else {
									printf( '<div class="uncode-note">' . wp_kses(__( 'WordPress doesn\'t have direct access to this folder <code>%s</code>. This is most likely due to a conflict with server file permissions. It is also possible that WordPress\' file access is not configured correctly. The custom CSS will be output inline.', 'uncode' ), array( 'code' => '' )) . '</div>', $back_css  );
								}
							?></td>
						</tr>
						<?php if ( ! defined('ENVATO_HOSTED_SITE') ) : ?>
						<tr>
							<td data-export-label="WP Memory Limit"><?php esc_html_e( 'WP Memory Limit', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Memory Limits not satisfied may produce possible errors on the frontend of the site as blank pages.', 'uncode' ) . '</small>'; ?></td>
							<td><?php
								$memory = uncode_let_to_num( WP_MEMORY_LIMIT );

								if ( $memory < 100663296 ) {
									echo '<mark class="error">' . sprintf(esc_html__('%s - We recommend setting memory to at least 96MB. %s.','uncode'), size_format( $memory ), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213459889') . '" target="_blank">'.esc_html__('More info','uncode').'</a>') . '</mark>';
								} else {
									echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
								}
							?></td>
						</tr>
						<tr>
							<td data-export-label="Server Memory Limit"><?php esc_html_e( 'Server Memory Limit', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'This is actually the real memory available for your installation despite the WP memory limit.', 'uncode' ) . '</small>'; ?></td>
							<td class="real-memory">
								<span class="calculating"><?php esc_html_e( 'Calculating…', 'uncode' ); ?></span>
								<mark class="yes" style="display: none;">%d% MB</mark>
								<mark class="error" style="display: none;"><?php esc_html_e( 'You only have %d% MB available and it\'s not enough to run the system. If you have already increased the memory limit please check with your hosting provider for increase it (at least 96MB is required).','uncode' ); ?></mark>
							</td>
						</tr>
						<tr>
							<td data-export-label="PHP Max Input Vars"><?php esc_html_e( 'PHP Max Input Vars', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Max Input Vars not satisfied may result in loss of Theme Options.', 'uncode' ) . '</small>'; ?></td>
							<td><?php
								$max_input = ini_get('max_input_vars');
								if ( $max_input < 3000 ) {
									echo '<mark class="error">' . sprintf( wp_kses(__( '%s - We recommend setting PHP max_input_vars to at least 3000. See: <a href="%s" target="_blank">Increasing the PHP max vars limit</a>', 'uncode' ), array( 'a' => array( 'href' => array(),'target' => array() ) ) ), $max_input, '//undsgn.com/uncode/documentation/max-input-vars/' ) . '</mark>';
								} else {
									echo '<mark class="yes">' . $max_input . '</mark>';
								}
							?></td>
						</tr>
						<tr>
							<td data-export-label="PHP Max Input Vars Allowed"><?php esc_html_e( 'PHP Max Input Vars (allowed)', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'The effective maximum number of variables your server can use for a single function to avoid overloads. If this value is lower than max_input_vars your server is applying restrictions on the actual number of vars that can be used.', 'uncode' ) . '<br>' . esc_attr__( 'If you modified the server settings refresh the option to test.', 'uncode' ) . '</small>'; ?></td>
							<td class="get-max-input-vars">
								<?php $uncode_test_max_input_vars = intval(get_option('uncode_test_max_input_vars'));
									if ( $uncode_test_max_input_vars != '' ) : ?>
									<span class="calculating" style="display: none"><?php esc_html_e( 'Calculating…', 'uncode' ); ?></span>
									<mark class="yes" <?php if ( $uncode_test_max_input_vars < 3000 ) echo 'style="display: none;"' ?>><?php echo $uncode_test_max_input_vars; ?></mark>
									<mark class="error get_data" <?php if ( $uncode_test_max_input_vars >= 3000 ) echo 'style="display: none;"'; ?>><?php echo $uncode_test_max_input_vars; printf(esc_html__(' - We recommend setting PHP max_input_vars to at least 3000. %s.','uncode'), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213459869') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></mark>
									<a href="#" id="max_vars_checker"><i class="fa fa-refresh"></i></a>
								<?php else : ?>
									<span class="calculating"><?php esc_html_e( 'Calculating…', 'uncode' ); ?></span>
									<mark class="yes" style="display: none;"></mark>
									<mark class="error get_data" style="display: none;">%d%<?php printf(esc_html__(' - We recommend setting PHP max_input_vars to at least 3000. %s.','uncode'), '<a href="' . esc_url('//support.undsgn.com/hc/en-us/articles/213459869') . '" target="_blank">'.esc_html__('More info','uncode').'</a>'); ?></mark>
									<mark class="error no_data" style="display: none;"><?php esc_html_e('No available data','uncode'); ?></mark>
									<a href="#" id="max_vars_checker"><i class="fa fa-refresh"></i></a>
								<?php endif; ?>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td data-export-label="WP Debug Mode"><?php esc_html_e( 'WP Debug Mode', 'uncode' ); ?>
							<?php echo '<span class="toggle-description"></span><small class="description">' . esc_attr__( 'Displays whether or not WordPress is in Debug Mode.', 'uncode' ) . '</small>'; ?></td>
							<td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="yes">' . '&#10004;' . '</mark>'; else echo '&ndash;'; ?></td>
						</tr>
					</tbody>
				</table>
				<?php do_action('uncode_welcome'); ?>
			</div><!-- .uncode-admin-panel__right -->
		</div><!-- .uncode-admin-panel__content -->
	</div><!-- .uncode-admin-panel -->
</div><!-- .uncode-wrap -->

<script type="text/javascript">

/**
 * Used to send a POST request.
 *
 * @param String url
 * @param Object data
 * @param function callback(response)
 */
var wpost = function (url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            callback(xhr.responseText);
        }
    }
    xhr.open('POST', url, true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.send(JSON.stringify(data));
}

if ( document.getElementById('uncode_api_credentials_form') !== null && document.getElementById('envato_update_info') !== null ) {

	document.getElementById('envato_update_info').addEventListener("mousedown", function(e) {
	    var license_empty_dom = document.getElementById('license_empty');
	    e.preventDefault();

        license_empty_dom.style.display = 'none';
	});

	document.getElementById('envato_update_info').addEventListener("click", function(e) {
	    e.preventDefault();

	    var $form_credentials = document.getElementById('uncode_api_credentials_form');
	    var user_name_field = document.getElementById('uncode_api_credentials_form').querySelector('[name="envato_username"]');
	    var api_key_field = document.getElementById('uncode_api_credentials_form').querySelector('[name="envato_api_key"]');
	    var purchase_code_field = document.getElementById('uncode_api_credentials_form').querySelector('[name="envato_purchase_code"]');
	    var user_name = user_name_field.value;
	    var api_key = api_key_field.value;
	    var purchase_code = purchase_code_field.value;
	    var license_empty_dom = document.getElementById('license_empty');
	    var force_activation = jQuery('#license_force_activation').val();

	    if ( user_name === '' || api_key === '' || purchase_code === '' ) {

	    	if ( user_name === '' )
	    		user_name_field.classList.add("error");
	    	if ( api_key === '' )
	    		api_key_field.classList.add("error");
	    	if ( purchase_code === '' )
	    		purchase_code_field.classList.add("error");

            license_empty_dom.style.display = 'inline-block';

	    	return false;

	    } else {

	    	//var $spinner = document.getElementById('uncode_api_credentials_form').querySelector('[class*="uncode-ot-spinner"]');

	    	$form_credentials.classList.add("uncode-ajax-loading");

		    wpost(
		        ajaxurl + '?action=update_license', {
		            "user_name": user_name,
		            "api_key": api_key,
		            "purchase_code": purchase_code,
		            "force_activation": force_activation
			    },
		        function(data) {
		            var obj = JSON.parse(data);

		            if (obj['success'] == 'false' || obj['success'] == false) {
		                var license_error_dom = document.getElementById('license_error');
		                $form_credentials.classList.remove("uncode-ajax-loading");
		                license_error_dom.style.display = 'inline-block';

		                //license_error_dom.innerHTML = obj['data'];
		            } else {
		                document.location.href = document.location.href;
		            }
		        }
		    );
		}
	});
}

/**
 * Small adjustments
 */
jQuery( document ).ready( function ( $ ) {

	//Remove error class from empty fields
	var $uncode_api_credentials_form = $('form#uncode_api_credentials_form'),
		$license_empty = $('#license_empty'),
		$license_error = $('#license_error'),
	$credentials_inputs = $('input[type="text"]', $uncode_api_credentials_form).each(function(){

		var $input = $(this).on('keyup paste', function(){
			$input.removeClass('error');

			if ( ! $('input[type="text"].error', $uncode_api_credentials_form).length )
				$license_empty.add($license_error).hide();
		});

	});

	//Spinner on change license button
	// var $change_license_form = $('form#change_license_form').on('submit', function(){
	// 	var $spinner = $('.spinner', this);

	// 	$spinner.addClass('is-active');
	// });

});
</script>

<script type="text/javascript">

	jQuery( document ).ready( function ( $ ) {
		$( '.help_tip' ).tipTip({
			attribute: 'data-tip'
		});

		$( 'a.help_tip' ).click( function() {
			return false;
		});

		$.ajax({
			type : 'post',
			url: '<?php echo get_template_directory_uri(); ?>/core/inc/admin-pages/testmemory.php',
			success: function(response) {
				var get_memory_array = String(response).split('\n'),
					get_memory;
				$(get_memory_array).each(function(index, el) {
					var temp_memory = el.replace( /^\D+/g, '');
					if ('%'+temp_memory == el) get_memory = temp_memory;
				});
				var	memory_string;
				if (get_memory < 96) {
					memory_string = $('.real-memory .error');
				} else {
					memory_string = $('.real-memory .yes');
				}
				memory_string.text(memory_string.text().replace("%d%", get_memory));
				$('.real-memory .calculating').hide();
				memory_string.show();
			},
			error: function(response) {
				var get_memory_array = String(response.responseText).split('\n'),
					get_memory;
				$(get_memory_array).each(function(index, el) {
					var temp_memory = el.replace( /^\D+/g, '');
					if ('%'+temp_memory == el) get_memory = temp_memory;
				});
				var	memory_string;
				if (get_memory < 96) {
					memory_string = $('.real-memory .error');
				} else {
					memory_string = $('.real-memory .yes');
				}
				memory_string.text(memory_string.text().replace("%d%", get_memory));
				$('.real-memory .calculating').hide();
				memory_string.show();
			}
		});

		var max_vars_checker = function(){
			var $wrap = $('.get-max-input-vars'),
				$calculating = $('.calculating', $wrap),
				$errors = $('.error', $wrap),
				$yes = $('.yes', $wrap),
				$checker = $('#max_vars_checker');

			$checker.on('click', function(e){
				e.preventDefault();

				$yes.add($errors).add($checker).fadeOut(200);
				setTimeout(function(){
					$calculating.fadeIn(200);
					uncode_test_max_input_vars(10000);
				}, 200);

			});

		};
		max_vars_checker();

		var uncode_test_max_input_vars = function($vars){
			var param = [],
				var_string,
				intData;
			for (i = 0; i < $vars; i++) {
				param[i] = 'var_'+i;
			}

			$.ajax({
				url: ajaxurl,
				data: {
					action: 'uncode_test_vars',
					content: param,
				},
				type: 'post',
				error: function(){
					$('.get-max-input-vars .calculating').hide();
					$('.get-max-input-vars .error.no_data').fadeIn();
				},
				success: function(data){
					intData = parseInt(data);
					if ( intData < ($vars-1) ) {
						if ( intData < 2990 ) {
							var_string = $('.get-max-input-vars .error.get_data');
							var_string.html(var_string.html().replace("%d%", intData));
						} else {
							var_string = $('.get-max-input-vars .yes');
							var_string.html(intData);
						}
						$('.get-max-input-vars .calculating').hide();
						var_string.add('#max_vars_checker').fadeIn();

					} else {
						uncode_test_max_input_vars($vars+10000);
					}
					$.ajax({
						url: ajaxurl,
						data: {
							action: 'uncode_update_max_input_vars',
							content: intData,
						},
						type: 'post'
					});
				}
			});

		};

		<?php if ( $uncode_test_max_input_vars == '' ) { ?>
		uncode_test_max_input_vars(10000);
		<?php } ?>

	});

</script>
