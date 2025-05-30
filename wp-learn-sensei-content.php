<?php
/**
 * Plugin name: WP Learn Sensei Content
 * Description: A custom Sensei extension plugin for WP Learn.
 * Version: 1.0.0
 * Author: WP Learn
 * Text Domain: wp-learn-sensei-content
 * Domain Path: /languages
 * Requires PHP: 7.4
 *
 * @package WP_Learn
 */
declare(strict_types=1);

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP_LEARN_SENSEI_CONTENT_VERSION', '1.0.0' );
define( 'WP_LEARN_SENSEI_CONTENT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_LEARN_SENSEI_CONTENT_URL', plugin_dir_url( __FILE__ ) );

// Initialize the plugin on plugins_loaded to ensure all dependencies are available.
add_action( 'plugins_loaded', 'wp_learn_sensei_content_init' );
/**
 * Initialize the plugin.
 */
function wp_learn_sensei_content_init() {

    // Check if Sensei LMS is active
    if ( ! class_exists( 'Sensei_Main' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>' . esc_html__( 'WP Learn Sensei Content requires Sensei LMS plugin to be installed and activated.', 'wp-learn-sensei-content' ) . '</p></div>';
        } );
        return;
    }

    require_once __DIR__ . '/vendor/automattic/wp-feature-api/wp-feature-api.php';
    add_action( 'wp_feature_api_init', 'wp_learn_sensei_content_register_features' );

}

/**
 * Register features for the plugin.
 */
function wp_learn_sensei_content_register_features() {
    wp_register_feature(
        array(
            'id' => 'wp-learn-sensei-content/create-course',
            'name' => 'Create a Course',
            'description' => 'Creates a course in Sensei LMS with a predefined structure.',
            'callback' => 'wp_learn_sensei_content_create_course_callback',
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            },
            'type' => WP_Feature::TYPE_TOOL,
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'course-title' => array(
                        'type' => 'string',
                        'description' => __( 'The title of the course.', 'wp-learn-sensei-content' ),
                    ),
                    'structure' => array(
                        'type' => 'object',
                        'description' => __( 'The course structure.', 'wp-learn-sensei-content' ),
                    ),
                ),
            ),
        )
    );
}

function wp_learn_sensei_content_create_course_callback( $context ) {
    $course_title = $context['course-title'] ?? '';
    $structure = $context['structure'] ?? [];

    // Create the course first
    $course_id = wp_insert_post(array(
        'post_type' => 'course',
        'post_title' => $course_title,
        'post_content' => '<!-- wp:sensei-lms/button-take-course -->
                                <div class="wp-block-sensei-lms-button-take-course is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
                                    <button class="wp-block-button__link">Take Course</button>
                                </div>
                                <!-- /wp:sensei-lms/button-take-course -->
                                
                                <!-- wp:sensei-lms/course-outline -->
                                <!-- /wp:sensei-lms/course-outline -->',
        'post_status' => 'draft'
    ));

    if ( is_wp_error( $course_id ) ) {
        return (
            array( 'error' => 'Failed to create course: ' . $course_id->get_error_message() )
        );
    }

    // Save the structure using Sensei_Course_Structure
    $course_structure = \Sensei_Course_Structure::instance( $course_id );
    $result = $course_structure->save( $structure );

    if ( $result === true ) {
        return (
            array( 'success' => 'Course structure created successfully!' )
        );
    } else {
        return (
            array( 'error' => 'Error creating structure: ' . $result->get_error_message() )
        );
    }
}
