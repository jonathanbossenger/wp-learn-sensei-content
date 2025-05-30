<?php
/**
 * WP CLI command to create a Sensei course structure.
 *
 * @package WP_Learn
 */

namespace WP_Learn\Sensei_Content\CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * Creates a Sensei course with a predefined structure.
 */
class CourseStructure extends WP_CLI_Command {

    /**
     * Creates a Sensei course with modules and lessons.
     *
     * ## OPTIONS
     *
     * [--course-title=<title>]
     * : The title of the course.
     * ---
     * default: Complete PHP Course
     * ---
     *
     * [--course-description=<description>]
     * : The description of the course.
     * ---
     * default: A comprehensive PHP learning experience
     * ---
     *
     * ## EXAMPLES
     *
     *     wp sensei-content create
     *     wp sensei-content create --course-title="Advanced JavaScript" --course-description="Master JavaScript programming"
     *
     * @param array $args       Command arguments.
     * @param array $assoc_args Command options.
     */
    public function create( $args, $assoc_args ) {
        // Parse arguments
        $course_title = $assoc_args['course-title'] ?? 'Complete PHP Course';

        // Create the course first
        WP_CLI::log( "Creating course: {$course_title}" );
        $course_id = wp_insert_post([
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
        ]);

        if ( is_wp_error( $course_id ) ) {
            WP_CLI::error( "Failed to create course: " . $course_id->get_error_message() );
            return;
        }

        WP_CLI::log( "Course created with ID: {$course_id}" );

        // Hard-coded structure array
        $structure = [
            [
                'type' => 'module',
                'title' => 'Introduction',
                'description' => 'Module covering Introduction',
                'lessons' => [
                    [
                        'type' => 'lesson',
                        'title' => 'Getting Started'
                    ],
                    [
                        'type' => 'lesson',
                        'title' => 'Basic Concepts'
                    ]
                ]
            ],
            [
                'type' => 'module',
                'title' => 'Advanced Topics',
                'description' => 'Module covering Advanced Topics',
                'lessons' => [
                    [
                        'type' => 'lesson',
                        'title' => 'Deep Dive'
                    ],
                    [
                        'type' => 'lesson',
                        'title' => 'Best Practices'
                    ],
                    [
                        'type' => 'lesson',
                        'title' => 'Case Studies'
                    ]
                ]
            ],
            [
                'type' => 'module',
                'title' => 'Final Project',
                'description' => 'Module covering Final Project',
                'lessons' => [
                    [
                        'type' => 'lesson',
                        'title' => 'Project Planning'
                    ],
                    [
                        'type' => 'lesson',
                        'title' => 'Implementation'
                    ]
                ]
            ]
        ];

        // Check if Sensei is active and the class exists
        if ( ! class_exists( 'Sensei_Course_Structure' ) ) {
            WP_CLI::error( "Sensei LMS plugin is not active or the Sensei_Course_Structure class is not available." );
            return;
        }

        // Save the structure using Sensei_Course_Structure
        $course_structure = \Sensei_Course_Structure::instance( $course_id );
        $result = $course_structure->save( $structure );

        if ( $result === true ) {
            WP_CLI::success( "Course structure created successfully!" );
        } else {
            WP_CLI::error( "Error creating structure: " . $result->get_error_message() );
        }
    }
}
