<?php
/*
Plugin Name: MB WP Custom Login & Registration Form
Plugin URI: https://manojbist.com.np/
Description: A shortcode based WordPress plugin that creates custom login and registration forms with a simple my account page that can be implemented using a shortcode.
Version: 1.0
Author: manoj bist
Author URI: https://manojbist.com.np/
*/

// Register activation hook
register_activation_hook(__FILE__, 'MB_create_plugin_table');

// Function to create table
function MB_create_plugin_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_subscription_record';

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Table does not exist, so we create it
        $sql = "CREATE TABLE $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            entry_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // Include WordPress upgrade script to create the table
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Hook into plugin deletion process
register_uninstall_hook( __FILE__, 'MB_custom_plugin_uninstall' );

// Function to remove custom table
function MB_custom_plugin_uninstall() {
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . 'user_subscription_record';

    // Drop the table if it exists
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}


// Define $MB_load_css as a global variable
global $MB_load_css;
$MB_load_css = false; // Set it to false initially

/* ------------------------------------------------------------------------- */
// user registration login form
/* ------------------------------------------------------------------------- */
function MB_registration_form() {
 
	// only show the registration form to non-logged-in members
	if(!is_user_logged_in()) {
 
		global $MB_load_css;
 
		// set this to true so the CSS is loaded
		$MB_load_css = true;
 
		// check to make sure user registration is enabled
		$registration_enabled = get_option('users_can_register');
 
		// only show the registration form if allowed
		if($registration_enabled) {
			$output = MB_registration_form_fields();
		} else {
			$output = __('User registration is not enabled');
		}
		return $output;
	}
}
add_shortcode('MB_register_form', 'MB_registration_form');

/*Myaccount shortcode*/
function MB_account_page_shortcode() {
 
	// only show the registration form to non-logged-in members
	if(is_user_logged_in()) {
 
		global $MB_load_css;
 
		// set this to true so the CSS is loaded
		$MB_load_css = true;
 
		$output = MB_account_page();
		return $output;
	}
	
}
add_shortcode('MB_account_page', 'MB_account_page_shortcode');

/* ------------------------------------------------------------------------- */
// user login form
/* ------------------------------------------------------------------------- */
function MB_login_form() {
 	
	if(!is_user_logged_in()) {
 
		global $MB_load_css;
 
		// set this to true so the CSS is loaded
		$MB_load_css = true;
 
		$output = MB_login_form_fields();
	} else {
		// could show some logged in user info here
		// $output = 'user info here';
		$output = __('Already Logged-In <a id="MB_logout" href="'. wp_logout_url( get_permalink() ) .'" title="Logout">Logout</a>');
		
	}
	return $output;
}
add_shortcode('MB_login_form', 'MB_login_form');

/* ------------------------------------------------------------------------- */
// registration form fields
/* ------------------------------------------------------------------------- */
function MB_registration_form_fields() {
 
	ob_start(); ?>	
		
		<?php 
		// show any error messages after form submission
		MB_show_error_messages(); ?>
 
		<form id="MB_registration_form" class="MB_form" action="" method="POST">
			<fieldset>
				<p>
					<label for="MB_user_Login"><?php _e('Username'); ?></label>
					<input name="MB_user_login" id="MB_user_login" class="required" type="text"/>
				</p>
				<p>
					<label for="MB_user_email"><?php _e('Email'); ?></label>
					<input name="MB_user_email" id="MB_user_email" class="required" type="email"/>
				</p>
				<p>
					<label for="MB_user_first"><?php _e('First Name'); ?></label>
					<input name="MB_user_first" id="MB_user_first" class="required" type="text"/>
				</p>
				<p>
					<label for="MB_user_last"><?php _e('Last Name'); ?></label>
					<input name="MB_user_last" id="MB_user_last" class="required" type="text"/>
				</p>
				<p>
					<label for="MB_user_phone"><?php _e('Phone'); ?></label>
					<input name="MB_user_phone" id="MB_user_phone" class="required" type="text" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" placeholder="10 digit mobile number" maxlength="10" />
				</p>
				<p>
					<label for="password"><?php _e('Password'); ?></label>
					<input name="MB_user_pass" id="password" class="required" type="password"/>
				</p>
				<p>
					<label for="password_again"><?php _e('Password Again'); ?></label>
					<input name="MB_user_pass_confirm" id="password_again" class="required" type="password"/>
				</p>
				<p style="text-align:center;">
					<input type="hidden" name="MB_register_nonce" value="<?php echo wp_create_nonce('MB-register-nonce'); ?>"/>
					<input type="submit" class="submit-btn" value="<?php _e('Register Your Account'); ?>"/>
				</p>
			</fieldset>
		</form>
	<?php
	return ob_get_clean();
}

/* ------------------------------------------------------------------------- */
// login form fields
/* ------------------------------------------------------------------------- */
function MB_login_form_fields() {
 
	ob_start(); ?>
		 
		<?php
		// show any error messages after form submission
		MB_show_error_messages(); ?>
 
		<form id="MB_login_form"  class="MB_form" action="" method="post">
			<fieldset>
				<p>
					<label for="MB_user_Login">Username</label>
					<input name="MB_user_login" id="MB_user_login" class="required" type="text"/>
				</p>
				<p>
					<label for="MB_user_pass">Password</label>
					<input name="MB_user_pass" id="MB_user_pass" class="required" type="password"/>
				</p>
				<p>
					<input type="hidden" name="MB_login_nonce" value="<?php echo wp_create_nonce('MB-login-nonce'); ?>"/>
					<input id="MB_login_submit" class="submit-btn" type="submit" value="Login"/>
				</p>
			</fieldset>
		</form>
	<?php
	return ob_get_clean();
}

/*My Account Section*/
function MB_account_page() {
        
        ob_start(); ?>
		<?php
			$action = $_GET['uaction'] ?? null;
			$user = wp_get_current_user();
		?>
        <div class="MB_account_page_wrapper">
            <div class="MB_left_sidebar">
                <ul class="MB_navigation">
                    <li class="<?php if($action == 'profile' || $action==null) {echo 'active';} ?>"><a href="/my-account?uaction=profile">Profile</a></li>
                    <li class="<?php if($action == 'subscription') {echo 'active';} ?>"><a href="/my-account?uaction=subscription">Training Subscription</a></li>
                    <li class="<?php if($action == 'logout') {echo 'active';} ?>"><a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a></li>
                </ul>
            </div>
            <div class="MB_main_content_wrapper">
				
				<?php if($action == 'profile' || $action==null){ 
					//Get User Details
					
        			$user_phone = get_user_meta($user->ID, 'phone_number', true);
				?>
                <div id="profile" class="MB_profile_tab mb_account_tabs">
                    <div class="account-form">
						<div class="account-form-inner">
							<form action="/my-account" method="post">
															
							<div class="account_form-row">
								<div class="account_form_col">
									<label> <?php _e('First Name'); ?> </label>
									<input class="account_input" name="mb_first_name" value="<?php echo $user->first_name; ?>">
								</div>
								<div class="account_form_col">
									<label> <?php _e('Last Name'); ?> </label>
									<input class="account_input" name="mb_last_name" value="<?php echo $user->last_name; ?>">
								</div>
							</div>
							<div class="account_form">								
								<label> <?php _e('Phone Number'); ?> </label>
								<input class="account_input" name="mb_user_phone" value="<?php echo $user_phone ?>">
							</div>
							<div class="account_form">
								<input type="hidden" name="MB_account_update_nonce" value="<?php echo wp_create_nonce('MB-login-nonce'); ?>"/>
								<button class="submit-btn">
									<?php _e('Update'); ?>
								</button>
							</div>
							</form>
						</div>
					</div>
                </div>
				<?php } //Profile end ?>
				
				<?php 
					if($action=='subscription'){ 
				?>
                <div id="training-subscription" class="MB_training_subscription_tab mb_account_tabs">
                     <?php 
					 echo MB_display_subscription_table();
                    ?>
                   <script>
					document.addEventListener('DOMContentLoaded', function() {
						var toggleButtons = document.querySelectorAll('.toggleWPFormData');
						
						toggleButtons.forEach(function(button) {
							button.addEventListener('click', function() {
								var row = this.closest('tr').nextElementSibling;
								if (row.style.display === 'none') {
									row.style.display = 'table-row';
									this.textContent = 'Hide WPForm Data';
								} else {
									row.style.display = 'none';
									this.textContent = 'Show WPForm Data';
								}
							});
						});
					});
					</script>


                </div>
				<?php } //subscrption end ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    
}


/* ------------------------------------------------------------------------- */
// Logs a member in after submitting a form
/* ------------------------------------------------------------------------- */
function MB_login_member() {
 
	if(isset($_POST['MB_user_login']) && wp_verify_nonce($_POST['MB_login_nonce'], 'MB-login-nonce')) {
 
		// this returns the user ID and other info from the user name
		$user = get_userdatabylogin($_POST['MB_user_login']);
 
		if(!$user) {
			// if the user name doesn't exist
			MB_errors()->add('empty_username', __('Invalid inputs'));
		}
 
		if(!isset($_POST['MB_user_pass']) || $_POST['MB_user_pass'] == '') {
			// if no password was entered
			MB_errors()->add('empty_password', __('Please enter a password'));
		}
 
		// check the user's login with their password
		if(!wp_check_password($_POST['MB_user_pass'], $user->user_pass, $user->ID)) {
			// if the password is incorrect for the specified user
			MB_errors()->add('empty_password', __('Incorrect inputs'));
		}
 
		// retrieve all error messages
		$errors = MB_errors()->get_error_messages();
 
		// only log the user in if there are no errors
		if(empty($errors)) {
 
			// wp_setcookie($_POST['MB_user_login'], $_POST['MB_user_pass'], true);
			// wp_set_current_user($user->ID, $_POST['MB_user_login']);	
			// do_action('wp_login', $_POST['MB_user_login']);
 
			// wp_redirect(home_url("/my-account")); exit;
			// Perform user login
			wp_setcookie($_POST['MB_user_login'], $_POST['MB_user_pass'], true);
			$user = wp_signon(array(
				'user_login'    => $_POST['MB_user_login'],
				'user_password' => $_POST['MB_user_pass'],
				'remember'      => true
			), false);

			if (is_wp_error($user)) {
				// Login failed, handle the error
				wp_redirect(home_url('/user-login?login=failed'));
				exit;
			}

			// Check if the logged-in user is an administrator
			if (user_can($user, 'administrator')) {
				// Redirect administrators to the WordPress dashboard
				wp_redirect(admin_url());
			} else {
				// Redirect other users to their account page
				wp_set_current_user($user->ID, $_POST['MB_user_login']);    
				do_action('wp_login', $_POST['MB_user_login']);
				wp_redirect(home_url("/my-account"));
			}
			exit;
		}
	}
}
add_action('init', 'MB_login_member');

/* ------------------------------------------------------------------------- */
// Register a new user
/* ------------------------------------------------------------------------- */
function MB_add_new_member() {
  	if (isset( $_POST["MB_user_login"] ) && wp_verify_nonce($_POST['MB_register_nonce'], 'MB-register-nonce')) {
		$user_login		= sanitize_text_field($_POST["MB_user_login"]);	
		$user_email		= sanitize_text_field($_POST["MB_user_email"]);
		$user_first 	= sanitize_text_field($_POST["MB_user_first"]);
		$user_last	 	= sanitize_text_field($_POST["MB_user_last"]);
		$user_pass		= sanitize_text_field($_POST["MB_user_pass"]);
		$pass_confirm 	= sanitize_text_field($_POST["MB_user_pass_confirm"]);
		$user_phone 	= sanitize_text_field($_POST['MB_user_phone']);
 
		// this is required for username checks
		require_once(ABSPATH . WPINC . '/registration.php');
 
		if(username_exists($user_login)) {
			// Username already registered
			MB_errors()->add('username_unavailable', __('Username already taken'));
		}
		if(!validate_username($user_login)) {
			// invalid username
			MB_errors()->add('username_invalid', __('Invalid username'));
		}
		if($user_login == '') {
			// empty username
			MB_errors()->add('username_empty', __('Please enter a username'));
		}

		
		
		if(!is_email($user_email)) {
			//invalid email
			MB_errors()->add('email_invalid', __('Invalid email'));
		}
		if(email_exists($user_email)) {
			//Email address already registered
			MB_errors()->add('email_used', __('Email already registered'));
		}
		if($user_pass == '') {
			// passwords do not match
			MB_errors()->add('password_empty', __('Please enter a password'));
		}
		if($user_pass != $pass_confirm) {
			// passwords do not match
			MB_errors()->add('password_mismatch', __('Passwords do not match'));
		}
		if(!validatePhoneNumber($user_phone) || empty($user_phone)) {
			MB_errors()->add('phone_number', __('Please enter a valid phone number.'));
		}
 
		$errors = MB_errors()->get_error_messages();
 
		// only create the user in if there are no errors
		if(empty($errors)) {
 
			$new_user_id = wp_insert_user(array(
					'user_login'		=> $user_login,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'first_name'		=> $user_first,
					'last_name'			=> $user_last,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'subscriber'
				)
			);

			if($new_user_id) {
				//save phone number
				update_user_meta( $new_user_id, 'phone_number', $user_phone );
				// send an email to the admin alerting them of the registration
				wp_new_user_notification($new_user_id);
 
				// log the new user in
				wp_setcookie($user_login, $user_pass, true);
				wp_set_current_user($new_user_id, $user_login);	
				do_action('wp_login', $user_login);
 
				// send the newly created user to the home page after logging them in
				wp_redirect(home_url("/")); exit;
			}
 
		}
 
	}
}
add_action('init', 'MB_add_new_member');

/* Validate Phone Number*/
function validatePhoneNumber($phoneNumber) {
    // Remove any non-digit characters
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Check if the phone number consists of exactly 10 digits
    if (strlen($phoneNumber) === 10 && ctype_digit($phoneNumber)) {
        return true; // Valid phone number
    } else {
        return false; // Invalid phone number
    }
}


/* ------------------------------------------------------------------------- */
// used for tracking error messages
/* ------------------------------------------------------------------------- */
function MB_errors(){
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

/* ------------------------------------------------------------------------- */
// Displays error messages from form submissions
/* ------------------------------------------------------------------------- */
function MB_show_error_messages() {
	if($codes = MB_errors()->get_error_codes()) {
		echo '<div class="MB_errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = MB_errors()->get_error_message($code);
		        echo '<span class="error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
		    }
		echo '</div>';
	}	
}

/* ------------------------------------------------------------------------- */
// register our form css
/* ------------------------------------------------------------------------- */
function MB_register_css() {
	wp_register_style('MB-form-css', plugin_dir_url( __FILE__ ) . '/css/forms.css');
}
add_action('init', 'MB_register_css');

/* ------------------------------------------------------------------------- */
// load our form css
/* ------------------------------------------------------------------------- */
function MB_print_css() {
	global $pippin_load_css, $MB_load_css;
 
	// this variable is set to TRUE if the short code is used on a page/post
	if ( ! $MB_load_css )
		return; // this means that neither short code is present, so we get out of here
 
	wp_print_styles('MB-form-css');
}
add_action('wp_footer', 'MB_print_css');

/* ------------------------------------------------------------------------- */
// Redirect to custom registration and login form
/* ------------------------------------------------------------------------- */
// Hook the appropriate WordPress action
//add_action('init', 'prevent_wp_login');

// function prevent_wp_login() {
//     // WP tracks the current page - global the variable to access it
//     global $pagenow;
//     // Check if a $_GET['action'] is set, and if so, load it into $action variable
//     $action = (isset($_GET['action'])) ? $_GET['action'] : '';
//     // Check if we're on the login page, and ensure the action is not 'logout'
//     if( $pagenow == 'wp-login.php' && ( ! $action || ( $action && ! in_array($action, array('logout', 'lostpassword', 'rp'))))) {
//         // Load the home page url
//         $page = site_url("/login/");
//         // Redirect to the home page
//         wp_redirect($page);
//         // Stop execution to prevent the page loading for any reason
//         exit();
//     }
// }
// function prevent_wp_login() {
//     // WP tracks the current page - global the variable to access it
//     global $pagenow;
//     // Check if a $_GET['action'] is set, and if so, load it into $action variable
//     $action = (isset($_GET['action'])) ? $_GET['action'] : '';
//     // Check if we're on the login page, and ensure the action is not 'logout'
//     if( $pagenow == 'wp-login.php' && ( ! $action || ( $action && ! in_array($action, array('logout', 'lostpassword', 'rp'))))) {
//         // Check if the user is logged in
//         if (is_user_logged_in()) {
//             // Check if the logged-in user is an administrator
//             $user = wp_get_current_user();
//             if (user_can($user, 'administrator')) {
//                 // Allow administrators to access wp-admin
//                 return;
//             }
//         }
//         // Load the home page url
//         $page = site_url("/login/");
//         // Redirect to the home page
//         wp_redirect($page);
//         // Stop execution to prevent the page loading for any reason
//         exit();
//     }
// }


/* ------------------------------------------------------------------------- */
// Disable Admin Bar for All Users Except for Administrators
/* ------------------------------------------------------------------------- */
add_action('after_setup_theme', 'remove_admin_bar');
	function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
	show_admin_bar(false);
	}
}

//Record user form submissions
function MB_wpf_dev_process_complete( $fields, $entry, $form_data, $entry_id ) {
    global $wpdb;

    // Check if user is logged in
    if (is_user_logged_in()) {
        // Get current user ID
        $user_id = get_current_user_id();
        
        // Get current date
        $current_date = current_time('mysql');

        // Table name
        $table_name = $wpdb->prefix . 'user_subscription_record';

        // Insert data into the custom table
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'entry_id' => $entry_id,
                'created_at' => $current_date
            ),
            array(
                '%d',
                '%d',
                '%s'
            )
        );
    }
}
add_action( 'wpforms_process_complete', 'MB_wpf_dev_process_complete', 10, 4 );

//get saved form record for logged in user
function MB_get_user_subscription_data() {
    global $wpdb;

    // Get the current user ID
    $user_id = get_current_user_id();

    // Table name
    $table_name = $wpdb->prefix . 'user_subscription_record';

    // Query to retrieve data
    $query = $wpdb->prepare("
        SELECT * 
        FROM $table_name
        WHERE user_id = %d
    ", $user_id);

    // Retrieve data from the table
    $results = $wpdb->get_results($query, ARRAY_A);

    return $results;
}

//Get form data from wpforms
function MB_get_wpform_data($entry_id) {
    $entry = wpforms()->entry->get( $entry_id );
    return $entry;
}

//generate table for list of subscription user have
function MB_display_subscription_table() {
    $data = MB_get_user_subscription_data();
    
    // Display retrieved data in a table
    if (!empty($data)) {
        echo '<table>';
        echo '<tr><th>User ID</th><th>Subscription ID</th><th>Created At</th><th>WPForm Data</th></tr>';
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . $row['user_id'] . '</td>';
            echo '<td>' . $row['entry_id'] . '</td>';
            echo '<td>' . $row['created_at'] . '</td>';
            
            // Get WPForm data based on entry ID
            $wpform_data = MB_get_wpform_data($row['entry_id']);
            $wpform_data_str = '';
            if ($wpform_data) {
                // Decode the JSON object into an associative array
                $fields = json_decode($wpform_data->fields, true);
                if (!empty($fields)) {
                    // Display button to toggle WPForm data
                    echo '<td><button class="toggleWPFormData submit-btn">Show WPForm Data</button></td>';
                    echo '</tr>';
                    echo '<tr class="wpformDataRow" style="display: none;">';
                    echo '<td colspan="4">';
                    // Create a new table for WPForm data
                    $wpform_data_str .= '<table>';
                    foreach ($fields as $field) {
                        // Add row for each field
                        $wpform_data_str .= '<tr><td>' . $field['name'] . '</td><td>' . $field['value'] . '</td></tr>';
                    }
                    $wpform_data_str .= '</table>';
                    echo $wpform_data_str;
                    echo '</td>';
                    echo '</tr>';
                }
            }
        }
        echo '</table>';
    } else {
        echo 'No data found.';
    }
}



