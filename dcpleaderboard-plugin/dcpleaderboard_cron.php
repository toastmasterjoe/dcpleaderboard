<?php


// Exit if accessed directly to prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Step 1: Define a custom cron interval (optional but useful).
 * This function adds a new custom interval to WP-Cron schedules.
 * In this example, we're adding a 'thirty_seconds' interval.
 *
 * @param array $schedules An array of existing WP-Cron schedules.
 * @return array The modified array of WP-Cron schedules.
 */
function dcpleaderboard_cron_intervals( $schedules ) {
    // Add a 'thirty_seconds' interval.
    $schedules['thirty_seconds'] = array(
        'interval' => 30, // Interval in seconds
        'display'  => __( 'Every 30 Seconds', 'dcpleaderboard-text-domain' ) // Display name for the interval
    );
    // You can add more custom intervals here if needed.
    // $schedules['five_minutes'] = array(
    //     'interval' => 300, // 5 minutes * 60 seconds/minute
    //     'display'  => __( 'Every 5 Minutes', 'dcpleaderboard-text-domain' )
    // );
    return $schedules;
}
add_filter( 'cron_schedules', 'dcpleaderboard_cron_intervals' );

/**
 * Step 2: Schedule the custom WP-Cron event.
 * This function is called when the plugin is activated.
 * It checks if the event is already scheduled and schedules it if not.
 */
function dcpleaderboard_cron_activation() {
    // Define a unique hook name for your cron event.
    $event_hook = 'dcpleaderboard_cron_job_hook';

    // Check if the event is already scheduled.
    // wp_next_scheduled() returns the next scheduled timestamp or false if not scheduled.
    if ( ! wp_next_scheduled( $event_hook ) ) {
        // Schedule the event.
        // wp_schedule_event( timestamp, recurrence, hook, args )
        // timestamp: When the event should first run (time()).
        // recurrence: How often the event should repeat ('hourly', 'twicedaily', 'daily', or your custom interval 'thirty_seconds').
        // hook: The name of the action hook to execute.
        // args: Optional array of arguments to pass to the hook.
        wp_schedule_event( time(), 'thirty_seconds', $event_hook );
    }
}
// Register the activation hook for your plugin.
// register_activation_hook( __FILE__, callback_function )
register_activation_hook( __FILE__, 'dcpleaderboard_cron_activation' );

/**
 * Step 3: Define the function that runs when the WP-Cron event is triggered.
 * This function will be executed every time the 'dcpleaderboard_cron_job_hook' is fired.
 */
function dcpleaderboard_cron_job_function() {
    // This is where your regular job logic goes.
    // For demonstration, we'll log a message to the debug log.
    error_log( 'WP-Cron Job Executed at: ' . date( 'Y-m-d H:i:s' ) );

    // You can perform various tasks here, for example:
    // - Clean up transient data
    // - Send out scheduled emails
    // - Fetch data from an external API
    // - Update custom post meta
    // - Generate reports
    // ... and so on.
}
// Hook your function to the custom event hook.
// add_action( hook, callback_function, priority, accepted_args )
add_action( 'dcpleaderboard_cron_job_hook', 'dcpleaderboard_cron_job_function' );

/**
 * Step 4: Unschedule the custom WP-Cron event on plugin deactivation.
 * This function is called when the plugin is deactivated.
 * It ensures that the cron event is removed to prevent orphaned tasks.
 */
function dcpleaderboard_cron_deactivation() {
    // Define the same hook name used for scheduling.
    $event_hook = 'dcpleaderboard_cron_job_hook';

    // Get the next scheduled timestamp for the event.
    $timestamp = wp_next_scheduled( $event_hook );

    // If the event is scheduled, clear it.
    if ( $timestamp ) {
        // wp_unschedule_event( timestamp, hook, args )
        // You need to pass the exact timestamp and hook name that was used to schedule it.
        // If you used arguments, you'd need to pass them here too.
        wp_unschedule_event( $timestamp, $event_hook );
    }
}
// Register the deactivation hook for your plugin.
register_deactivation_hook( __FILE__, 'dcpleaderboard_cron_deactivation' );

// Optional: Add a simple admin notice for demonstration purposes
function dcpleaderboard_admin_notice() {
    if ( current_user_can( 'manage_options' ) ) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>DCPLeaderboard WP-Cron Plugin</strong> is active. A cron job is scheduled to run every 30 seconds (check your debug log for output).</p>';
        echo '</div>';
    }
}
add_action( 'admin_notices', 'dcpleaderboard_admin_notice' );

?>