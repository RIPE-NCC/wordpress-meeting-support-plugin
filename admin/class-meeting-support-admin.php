<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.ripe.net
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */



class Meeting_Support_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version, $auth)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->auth = $auth;
    }


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Meeting_Support_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Meeting_Support_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style('thickbox');
        wp_enqueue_style(
            $this->plugin_name . '-fontawesome-css',
            plugin_dir_url(__FILE__) . 'css/font-awesome.min.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-datatables-css',
            plugin_dir_url(__FILE__) . 'css/datatables.min.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-select2-css',
            plugin_dir_url(__FILE__) . 'css/select2.min.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-bootstrap-iso',
            plugin_dir_url(__FILE__) . 'css/bootstrap-iso.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/meeting-support-admin.css',
            [],
            $this->version,
            'all'
        );
    }


    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Meeting_Support_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Meeting_Support_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        //wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script(
            $this->plugin_name . '-select2-js',
            plugin_dir_url(__FILE__) . 'js/select2.min.js',
            [],
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-modernizr-js',
            plugin_dir_url(__FILE__) . 'js/modernizr-custom.js',
            [],
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-polyfiller-js',
            plugin_dir_url(__FILE__) . 'js/polyfiller.min.js',
            [],
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-datatables-js',
            plugin_dir_url(__FILE__) . 'js/datatables.min.js',
            ['jquery'],
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/meeting-support-admin.js',
            ['jquery'],
            $this->version,
            false
        );
    }

    public function run_migrations($db_version)
    {
        switch (true) {
            // Add each migration step here
            case $db_version < '1.1.0':
                $this->run_migration_1_1_0();
                // Fallthrough intentional
            case $db_version < '1.2.0':
                $this->run_migration_1_2_0();
                // Fallthrough intentional
            case $db_version < '1.3.0':
                $this->run_migration_1_3_0();
                // Fallthrough intentional
            case $db_version < '1.4.0':
                $this->run_migration_1_4_0();
        }
    }

    public function run_migration_1_1_0()
    {
        /**
         *  Migration Step 1.1.0, added PCSS 1.0 changes
         */

        mps_log('MIGRATION STEP 1.1.0 STARTED');

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        global $wpdb;

        mps_log('MODIFYING TABLES');

        // Add "updated_date" column
        $table_name = $wpdb->base_prefix . 'ms_pc_submissions';
        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          submission_type tinyint NOT NULL,
          submission_title tinytext NOT NULL,
          submission_abstract text NOT NULL,
          submission_url tinytext NOT NULL,
          submission_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          submission_status tinyint NOT NULL,
          filename tinytext NOT NULL,
          author_name tinytext NOT NULL,
          author_affiliation TEXT NOT NULL,
          author_email tinytext NOT NULL,
          author_uuid tinytext NOT NULL,
          author_comments text NOT NULL,
          final_decision text NOT NULL,
          updated_date timestamp NOT NULL,
          UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        dbDelta($sql);

        // Add "timestamp" column
        $table_name = $wpdb->base_prefix . 'ms_pc_submission_ratings';
        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          uuid varchar(40) NOT NULL,
          submission_id int NOT NULL,
          rating_content tinyint NOT NULL,
          rating_presenter tinyint NOT NULL,
          rating_comment text NOT NULL,
          timestamp timestamp NOT NULL,
          UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        dbDelta($sql);

        // Add "ms_pc_submissions_archive" table
        $table_name = $wpdb->base_prefix . 'ms_pc_submissions_archive';
        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          submission_id mediumint NOT NULL,
          submission_type tinyint NOT NULL,
          submission_title tinytext NOT NULL,
          submission_abstract text NOT NULL,
          submission_url tinytext NOT NULL,
          submission_status tinyint NOT NULL,
          filename tinytext NOT NULL,
          author_name tinytext NOT NULL,
          author_affiliation TEXT NOT NULL,
          author_email tinytext NOT NULL,
          author_uuid tinytext NOT NULL,
          author_comments text NOT NULL,
          final_decision text NOT NULL,
          timestamp timestamp NOT NULL,
          UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        dbDelta($sql);

        mps_log('SETTING DEFAULT TIMESTAMP FOR PC SUBMISSION RATINGS');

        $wpdb->update(
            $wpdb->prefix . 'ms_pc_submission_ratings',
            ['timestamp' => date('Y-m-d H:i:s')],
            ['timestamp' => '0000-00-00 00:00:00']
        );

        mps_log('UPDATING DB VERSION');

        mps_update_option('db_version', '1.1.0');

        mps_log('MIGRATION STEP 1.1.0 COMPLETE');
    }

    public function run_migration_1_2_0()
    {
        mps_log('MIGRATION STEP 1.2.0 STARTED');

        // No special migration needed, create the table
        require_once plugin_dir_path(__FILE__) . '../includes/class-meeting-support-activator.php';
        Meeting_Support_Activator::createSpeakersTable();

        mps_log('UPDATING DB VERSION');

        mps_update_option('db_version', '1.2.0');

        mps_log('MIGRATION STEP 1.2.0 COMPLETE');
    }

    /**
     * Migration 1.3.0 to add tags to the speaker_bios table
     * @return null
     */
    public function run_migration_1_3_0()
    {
        global $wpdb;
        mps_log('MIGRATION STEP 1.3.0 STARTED');
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Add "tags" column
        $tableName = $wpdb->prefix . 'ms_speakers';
        $sql = "CREATE TABLE $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name text NOT NULL,
            uuid text NOT NULL,
            slug text NOT NULL,
            bio_texts text  NOT NULL,
            bio_texts_draft text  NOT NULL,
            tags text  NOT NULL,
            allowed tinyint(1) NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        dbDelta($sql);

        mps_log('UPDATING DB VERSION');

        mps_update_option('db_version', '1.3.0');

        mps_log('MIGRATION STEP 1.3.0 COMPLETE');
    }
    /**
     * Migration 1.4.0 to add is_social flag to sessions
     * @return null
     */
    public function run_migration_1_4_0()
    {
        global $wpdb;
        mps_log('MIGRATION STEP 1.4.0 STARTED');
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Add "tags" column
        $tableName = $wpdb->prefix . 'ms_sessions';
        $sql = "CREATE TABLE $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            start_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            end_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            room tinytext NOT NULL,
            url tinytext NOT NULL,
            is_intermission tinyint(1) DEFAULT 0 NOT NULL,
            is_rateable tinyint(1) DEFAULT 0 NOT NULL,
            is_streamed tinyint(1) DEFAULT 0 NOT NULL,
            hide_title tinyint(1) DEFAULT 0 NOT NULL,
            is_social tinyint(1) DEFAULT 0 NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        mps_log('UPDATING DB VERSION');

        mps_update_option('db_version', '1.4.0');

        mps_log('MIGRATION STEP 1.4.0 COMPLETE');
    }

    /**
     * Add the administration menu(s) for the plugin
     * @return void
     */
    public function add_mps_main_menu()
    {

        $blog_id = get_current_blog_id();

        // Add a brand new menu area
        add_menu_page(
            'Meeting Support',
            'Meeting Support',
            'manage_options',
            $this->plugin_name,
            '',
            plugin_dir_url(__FILE__) . 'icon.png'
        );

        // Add main menu
        add_submenu_page(
            $this->plugin_name,
            'Meeting Support - Options',
            'Meeting Support',
            'manage_options',
            $this->plugin_name,
            [$this, 'mps_settings_page']
        );

        if (is_multisite() && $blog_id > 1 || ! is_multisite()) {
            // Add agenda management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Agenda',
                'Agenda',
                'manage_options',
                $this->plugin_name . '-agenda',
                [$this, 'mps_agenda_page']
            );
        }

        // Add user management menu, if we're using 'local' authentication
        if ($this->auth->auth_method == 'local') {
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Users',
                'Users',
                'manage_options',
                $this->plugin_name . '-users',
                array( $this, 'mps_users_page' )
            );
        }

        // Add PC User management menu
        add_submenu_page(
            $this->plugin_name,
            'Meeting Support - PC Roles',
            'PC Roles',
            'manage_options',
            $this->plugin_name . '-pc_users',
            array( $this, 'mps_pc_users_page' )
        );

        if (is_multisite() && $blog_id > 1 || ! is_multisite()) {
            // Add Sponsors management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Sponsors',
                'Sponsors',
                'manage_options',
                $this->plugin_name . '-sponsors',
                array( $this, 'mps_sponsors_page' )
            );

            // Add Slots management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Slots',
                'Slots',
                'edit_posts',
                $this->plugin_name . '-slots',
                array( $this, 'mps_slots_page' )
            );

            // Add Presentations management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Presentations',
                'Presentations',
                'manage_options',
                $this->plugin_name . '-presentations',
                array( $this, 'mps_presentations_page' )
            );

            // Add Presentation Ratings management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Presentation Ratings',
                'Presentation Ratings',
                'edit_pages',
                $this->plugin_name . '-presentation-ratings',
                array( $this, 'mps_presentation_ratings_page' )
            );

            // Add Videos management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Videos',
                'Videos',
                'manage_options',
                $this->plugin_name . '-videos',
                array( $this, 'mps_videos_page' )
            );

            // Add PC Elections management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - PC Elections',
                'PC Elections',
                'manage_options',
                $this->plugin_name . '-pc-elections',
                array( $this, 'mps_pc_elections_page' )
            );

            // Add Speakers management menu
            add_submenu_page(
                $this->plugin_name,
                'Meeting Support - Speakers',
                'Speakers',
                'edit_posts',
                $this->plugin_name . '-speakers',
                array( $this, 'mps_speakers_page' )
            );
        }
        // ------------------------ Options

        // First section, General Options
        add_settings_section(
            'mps_options',
            'General Options',
            '',
            $this->plugin_name
        );

        // Meeting Name
        add_settings_field(
            'mps_meeting_name',
            'Meeting Name',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_options',
            array( 'name' => 'mps_meeting_name', 'placeholder' => 'RIPE XX' )
        );

        if ($this->auth->auth_method == 'crowd') {
            // Meeting event_key
            add_settings_field(
                'mps_agora_event_key',
                'Agora Event Key',
                array( $this, 'mps_text_field_setting_callback' ),
                $this->plugin_name,
                'mps_options',
                [
                    'name' => 'mps_agora_event_key',
                    'placeholder' => 'Shared key to authenticate with agora for agenda changes'
                ]
            );
        }

        // Meeting Logo URL
        add_settings_field(
            'mps_meeting_logo_url',
            'Meeting Logo URL<br><small>for Agenda PDF</small>',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_options',
            array('name' => 'mps_meeting_logo_url', 'placeholder' => '/wp-content/uploads/logo.png' )
        );

        // Meeting Invisible reCAPTCHA Key
        add_settings_field(
            'mps_meeting_irecaptcha_key',
            'Invisible reCAPTCHA Key',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_options',
            array('name' => 'mps_meeting_irecaptcha_key' )
        );

        // Meeting Invisible reCAPTCHA Secret Key
        add_settings_field(
            'mps_meeting_irecaptcha_secret_key',
            'Invisible reCAPTCHA Secret Key',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_options',
            array('name' => 'mps_meeting_irecaptcha_secret_key' )
        );

        // Room config
        add_settings_field(
            'mps_rooms',
            'Meeting Rooms',
            array( $this, 'mps_rooms_setting_callback' ),
            $this->plugin_name,
            'mps_options'
        );

        // Intermission Room config
        add_settings_field(
            'mps_intermission_config',
            'Intermission Room Colours',
            array( $this, 'mps_intermission_config_setting_callback' ),
            $this->plugin_name,
            'mps_options'
        );

        // Authentication config
        add_settings_field(
            'mps_auth_method',
            'Authentication Method',
            array( $this, 'mps_auth_method_setting_callback' ),
            $this->plugin_name,
            'mps_options'
        );


        // Second section, date/time settings
        add_settings_section(
            'mps_datetime',
            'Date/Time Options',
            '',
            $this->plugin_name
        );

        // Meeting start date
        add_settings_field(
            'mps_meeting_increments',
            'Meeting Increments',
            array( $this, 'mps_meeting_increments_setting_callback' ),
            $this->plugin_name,
            'mps_datetime'
        );

        // Meeting start date
        add_settings_field(
            'mps_meeting_start_date',
            'Meeting Start Date',
            array( $this, 'mps_meeting_date_setting_callback' ),
            $this->plugin_name,
            'mps_datetime',
            'start'
        );

        // Meeting end date
        add_settings_field(
            'mps_meeting_end_date',
            'Meeting End Date',
            array( $this, 'mps_meeting_date_setting_callback' ),
            $this->plugin_name,
            'mps_datetime',
            'end'
        );

        // Agenda start time
        add_settings_field(
            'mps_agenda_start_time',
            'Agenda Start Time',
            array( $this, 'mps_agenda_time_setting_callback' ),
            $this->plugin_name,
            'mps_datetime',
            'start'
        );

        // Agenda end time
        add_settings_field(
            'mps_agenda_end_time',
            'Agenda End Time',
            array( $this, 'mps_agenda_time_setting_callback' ),
            $this->plugin_name,
            'mps_datetime',
            'end'
        );

        // Agenda admin end time
        add_settings_field(
            'mps_agenda_admin_end_time',
            'Agenda End Time<br><small>for admin screen</small>',
            array( $this, 'mps_agenda_time_setting_callback' ),
            $this->plugin_name,
            'mps_datetime',
            'admin_end'
        );

        // Meeting timezone
        add_settings_field(
            'mps_meeting_timezone',
            'Meeting Timezone',
            array( $this, 'mps_meeting_timezone_setting_callback' ),
            $this->plugin_name,
            'mps_datetime'
        );


        // Third section, presentation settings
        add_settings_section(
            'mps_presentations',
            'Presentation Options',
            '',
            $this->plugin_name
        );

        // Global Ratings
        add_settings_field(
            'mps_global_ratings',
            'Presentation Ratings Enabled',
            array( $this, 'mps_checkbox_setting_callback' ),
            $this->plugin_name,
            'mps_presentations',
            array('name' => 'mps_global_ratings' )
        );

        // Presentations Directory
        add_settings_field(
            'mps_presentations_dir',
            'Presentations Directory',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_presentations',
            array(
                'name' => 'mps_presentations_dir',
                'placeholder' => 'wp-content/uploads/presentations/(meeting-id)',
                'default' => 'wp-content/uploads/presentations/'
                )
        );

        // Allowed file types
        add_settings_field(
            'mps_allowed_file_types',
            'Allowed File Types',
            array( $this, 'mps_allowed_file_types_setting_callback' ),
            $this->plugin_name,
            'mps_presentations',
            array('name' => 'mps_allowed_file_types', 'placeholder' => 'txt, csv, xls, key' )
        );

        // Fourth section, interface settings

        // Only show recorder option if we're using crowd authentication
        if ($this->auth->auth_method == 'crowd') {
            add_settings_section(
                'mps_recorder',
                'Recorder Options',
                '',
                $this->plugin_name
            );

            // Presentations Directory
            add_settings_field(
                'mps_recorder_password',
                'Recorder Password',
                array( $this, 'mps_password_field_setting_callback' ),
                $this->plugin_name,
                'mps_recorder',
                array('name' => 'mps_recorder_password' )
            );
        }

        // Fifth section, interface settings
        add_settings_section(
            'mps_interface',
            'Interface Options',
            '',
            $this->plugin_name
        );

        // Show the navigation in the top admin bar
        add_settings_field(
            'mps_admin_bar',
            'Navigation in Top Bar',
            array( $this, 'mps_checkbox_setting_callback' ),
            $this->plugin_name,
            'mps_interface',
            array('name' => 'mps_admin_bar' )
        );

        // Show the config in debug mode at the bottom
        add_settings_field(
            'mps_config_debug',
            'Config Debug',
            array( $this, 'mps_checkbox_setting_callback' ),
            $this->plugin_name,
            'mps_interface',
            array('name' => 'mps_config_debug' )
        );

        // email settings
        add_settings_section(
            'mps_email',
            'Email Options',
            '',
            $this->plugin_name
        );

        add_settings_field(
            'mps_meeting_organiser_email',
            'Meeting Organiser Email Address',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_email',
            array('name' => 'mps_meeting_organiser_email', 'placeholder' => 'meeting@ripe.net' )
        );

        add_settings_field(
            'mps_comms_ticket_email',
            'Speaker Bio Review Email Address',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_email',
            array('name' => 'mps_comms_ticket_email', 'placeholder' => 'comms-req@ripe.net' )
        );

        add_settings_field(
            'mps_sender_email_name',
            'Generic Email From Name',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_email',
            array('name' => 'mps_sender_email_name', 'placeholder' => 'RIPE NCC Web Services' )
        );

        add_settings_field(
            'mps_sender_email_address',
            'Generic Email From Address',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_email',
            array('name' => 'mps_sender_email_address', 'placeholder' => 'ws@ripe.net' )
        );

        // crowd settings
        if ($this->auth->auth_method == 'crowd') {
            add_settings_section(
                'mps_crowd_config',
                'Crowd Options',
                '',
                $this->plugin_name
            );

            add_settings_field(
                'mps_crowd_appusername',
                'Crowd App Username',
                array( $this, 'mps_text_field_setting_callback' ),
                $this->plugin_name,
                'mps_crowd_config',
                array('name' => 'mps_crowd_appusername', 'placeholder' => 'rosie' )
            );

            add_settings_field(
                'mps_crowd_apppassword',
                'Crowd App Password',
                array( $this, 'mps_password_field_setting_callback' ),
                $this->plugin_name,
                'mps_crowd_config',
                array('name' => 'mps_crowd_apppassword' )
            );
        }


        if ($this->auth->auth_method == 'local') {
            add_settings_section(
                'mps_user_config',
                'User Options <small><i>URLs are relative to site home URL</i></small>',
                '',
                $this->plugin_name
            );

            add_settings_field(
                'mps_user_registration_url',
                'User Registration URL',
                array( $this, 'mps_text_field_setting_callback' ),
                $this->plugin_name,
                'mps_user_config',
                array(
                    'name' => 'mps_user_registration_url',
                    'default' => '/user-registration/'
                )
            );

            add_settings_field(
                'mps_user_profile_url',
                'User Profile URL',
                array( $this, 'mps_text_field_setting_callback' ),
                $this->plugin_name,
                'mps_user_config',
                array(
                    'name' => 'mps_user_profile_url',
                    'default' => '/user-profile/'
                )
            );
        }

        if ($this->auth->auth_method == 'crowd') {
            // Content Generation settings
            add_settings_section(
                'mps_content_generation',
                'Automatic Content Generation',
                '',
                $this->plugin_name
            );
        }

        // PCSS Area Creation
        add_settings_field(
            'mps_create_pcss_area',
            'Working Group Chair Bios Generator',
            array( $this, 'mps_recreate_wg_chairs_area_callback' ),
            $this->plugin_name,
            'mps_content_generation'
        );

        // PC settings
        add_settings_section(
            'mps_pc_config',
            'Programme Committee Options',
            '',
            $this->plugin_name
        );

        // PCSS Area Creation
        add_settings_field(
            'mps_create_pcss_area',
            'PCSS Area Generator',
            array( $this, 'mps_create_pcss_area_callback' ),
            $this->plugin_name,
            'mps_pc_config'
        );


        add_settings_field(
            'mps_pc_submission_tags',
            'PCSS Tags',
            array( $this, 'mps_textarea_field_setting_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_pc_submission_tags', 'placeholder' => 'IPv6, Cooperation, DNS, IoT' )
        );

        add_settings_field(
            'mps_pc_email_address',
            'PC Email Address',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_pc_email_address' )
        );

        add_settings_field(
            'mps_wg_chair_email_address',
            'WG Chairs Email Address',
            array( $this, 'mps_text_field_setting_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_wg_chair_email_address' )
        );

        add_settings_field(
            'mps_send_new_submission_to_wg_chairs',
            'Send new PC Submissions to WG Chairs',
            array( $this, 'mps_checkbox_setting_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_send_new_submission_to_wg_chairs' )
        );

        add_settings_field(
            'mps_pc_required_uploads',
            'Submission Types Requiring Files',
            array( $this, 'mps_pc_required_uploads_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_pc_required_uploads' )
        );

        add_settings_field(
            'mps_pc_message_sign_in',
            'PC Sign-in Message',
            array( $this, 'mps_textarea_field_setting_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_pc_message_sign_in' )
        );

        add_settings_field(
            'mps_pc_accepted_template',
            'PC Email Accepted Template',
            array( $this, 'mps_textarea_field_setting_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_pc_accepted_template' )
        );

        add_settings_field(
            'mps_pc_declined_template',
            'PC Email Declined Template',
            array( $this, 'mps_textarea_field_setting_callback' ),
            $this->plugin_name,
            'mps_pc_config',
            array('name' => 'mps_pc_declined_template' )
        );


        // Register our settings so that $_POST handling is done for us and
        // our callback function just has to echo the <input>
        register_setting($this->plugin_name, 'mps_meeting_name');
        register_setting($this->plugin_name, 'mps_agora_event_key');
        register_setting($this->plugin_name, 'mps_meeting_logo_url');
        register_setting($this->plugin_name, 'mps_meeting_irecaptcha_key');
        register_setting($this->plugin_name, 'mps_meeting_irecaptcha_secret_key');
        register_setting($this->plugin_name, 'mps_intermission_config');
        register_setting($this->plugin_name, 'mps_auth_method');
        register_setting($this->plugin_name, 'mps_rooms', array( $this, 'mps_tidy_rooms' ));
        register_setting($this->plugin_name, 'mps_meeting_increments');
        register_setting($this->plugin_name, 'mps_meeting_start_date');
        register_setting($this->plugin_name, 'mps_meeting_end_date');
        register_setting($this->plugin_name, 'mps_agenda_start_time', array( $this, 'mps_validate_agenda_time' ));
        register_setting($this->plugin_name, 'mps_agenda_end_time', array( $this, 'mps_validate_agenda_time' ));
        register_setting($this->plugin_name, 'mps_agenda_admin_end_time', array( $this, 'mps_validate_agenda_time' ));
        register_setting($this->plugin_name, 'mps_meeting_timezone');
        register_setting($this->plugin_name, 'mps_global_ratings');
        register_setting($this->plugin_name, 'mps_presentations_dir', array( $this, 'mps_create_presentations_dir' ));
        register_setting($this->plugin_name, 'mps_allowed_file_types', array( $this, 'mps_format_allowed_file_types' ));
        register_setting($this->plugin_name, 'mps_recorder_password');
        register_setting($this->plugin_name, 'mps_admin_bar');
        register_setting($this->plugin_name, 'mps_config_debug');
        register_setting($this->plugin_name, 'mps_meeting_organiser_email');
        register_setting($this->plugin_name, 'mps_comms_ticket_email');
        register_setting($this->plugin_name, 'mps_sender_email_name');
        register_setting($this->plugin_name, 'mps_sender_email_address');
        register_setting($this->plugin_name, 'mps_crowd_appusername');
        register_setting($this->plugin_name, 'mps_crowd_apppassword');
        register_setting($this->plugin_name, 'mps_user_registration_url');
        register_setting($this->plugin_name, 'mps_user_profile_url');
        register_setting($this->plugin_name, 'mps_pc_submission_tags');
        register_setting($this->plugin_name, 'mps_pc_message_sign_in');
        register_setting($this->plugin_name, 'mps_pc_file_mandatory_submission_types');
        register_setting($this->plugin_name, 'mps_pc_required_uploads');
        register_setting($this->plugin_name, 'mps_pc_email_address');
        register_setting($this->plugin_name, 'mps_wg_chair_email_address');
        register_setting($this->plugin_name, 'mps_send_new_submission_to_wg_chairs');
        register_setting($this->plugin_name, 'mps_pc_accepted_template');
        register_setting($this->plugin_name, 'mps_pc_declined_template');
    }

    /**
     * Add the Meeting Support links to the WP_Admin_Bar object
     * @param Object $wp_admin_bar WP_Admin_Bar
     * @return void
     */
    public function admin_plugin_top_bar($wp_admin_bar)
    {
        // Don't try to add stuff to the bar if we shouldn't
        if (! is_super_admin()
         || ! is_object($wp_admin_bar)
         || ! function_exists('is_admin_bar_showing')
         || ! is_admin_bar_showing() ) {
            return;
        }

        $blog_id = get_current_blog_id();

        // Add parent
        $args = array(
        'id'    => 'mps',
        'title' => 'Meeting Support',
        'href'  => admin_url('admin.php?page=' . $this->plugin_name)
        );
        $wp_admin_bar->add_node($args);

        if (is_multisite() && $blog_id > 1 || ! is_multisite()) {
            // Add agenda submenu to top menu
            $args = array(
            'parent' => 'mps',
            'id'     => 'mps_agenda',
            'title'  => 'Agenda',
            'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-agenda')
                );
                $wp_admin_bar->add_node($args);
        }

        if ($this->auth->auth_method == 'local') {
            // Add user submenu to top menu
            $args = array(
            'parent' => 'mps',
            'id'     => 'mps_users',
            'title'  => 'Users',
            'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-users')
                );
                $wp_admin_bar->add_node($args);
        }

        // Add PC Roles submenu to top menu
        $args = array(
        'parent' => 'mps',
        'id'     => 'mps_pc_users',
        'title'  => 'PC Roles',
        'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-pc_users')
        );
        $wp_admin_bar->add_node($args);

        if (is_multisite() && $blog_id > 1 || ! is_multisite()) {
            // Add Sponsors submenu to top menu
            $args = array(
                'parent' => 'mps',
                'id'     => 'mps_sponsors',
                'title'  => 'Sponsors',
                'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-sponsors')
            );
            $wp_admin_bar->add_node($args);

            // Add Slots submenu to top menu
            $args = array(
                'parent' => 'mps',
                'id'     => 'mps_slots',
                'title'  => 'Slots',
                'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-slots')
            );
            $wp_admin_bar->add_node($args);

            // Add Presentations submenu to top menu
            $args = array(
                'parent' => 'mps',
                'id'     => 'mps_presentations',
                'title'  => 'Presentations',
                'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-presentations')
            );
            $wp_admin_bar->add_node($args);


            // Add Presentation Ratings submenu to top menu
            $args = array(
                'parent' => 'mps',
                'id'     => 'mps_presentation_ratings',
                'title'  => 'Presentation Ratings',
                'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-presentation-ratings')
            );
            $wp_admin_bar->add_node($args);

            // Add Videos submenu to top menu
            $args = array(
                'parent' => 'mps',
                'id'     => 'mps_videos',
                'title'  => 'Videos',
                'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-videos')
            );
            $wp_admin_bar->add_node($args);

            // Add PC Elections submenu to top menu
            $args = array(
                'parent' => 'mps',
                'id'     => 'mps_pc_elections',
                'title'  => 'PC Elections',
                'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-pc-elections')
            );
            $wp_admin_bar->add_node($args);

            // Add Speakers submenu to top menu
            $args = array(
                'parent' => 'mps',
                'id'     => 'mps_speakers',
                'title'  => 'Speakers',
                'href'   => admin_url('admin.php?page=' . $this->plugin_name . '-speakers')
            );
            $wp_admin_bar->add_node($args);
        }
    }


    public function mps_update_agora_schedule()
    {
        // Function to tell agora that the schedule has changed
        // Define all the agora endpoints that we need to notify of a change to the schedule

        $agora_event_key = mps_get_option('agora_event_key');
        $agora_api_urls = [
            'https://meeting-app-1.ripe.net/agora/api/agenda/',
        ];

        // Fields for the POST request
        $fields = array(
            'event_key' => $agora_event_key
        );

        $fields_string = '';
        // Iterate through the fields to make a fields_string for POST requests
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        // Do a post request for each one
        foreach ($agora_api_urls as $url) {
            mps_log('Notifying agora of new schedule: ' . $url);
            try {
                //open connection
                $ch = curl_init();

                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, count($fields));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

                //execute post
                $result = curl_exec($ch);

                //close connection
                curl_close($ch);
            } catch (Exception $e) {
                mps_log('Error while telling agora about the new agenda update');
            }
        }
    }


    public function mps_edit_session_callback()
    {

        // Check that the request came from a valid admin page
        check_admin_referer('mps_edit_session');

        global $wpdb;

        // Get the session id, if it's 0 then we're adding a new session as opposed to editing one.
        $session_id = (int) $_POST['session_id'];

        // Don't bother with all the validation stuff if the session should be deleted
        if ($session_id > 0 && isset($_POST['delete-session'])) {
            $wpdb->delete($wpdb->prefix . 'ms_sessions', array( 'id' => $session_id ), array( '%d' ));
            // (Try to) ping agora to tell it there's new data
            $this->mps_update_agora_schedule();
            wp_safe_redirect(
                add_query_arg(
                    array(
                    'updated' => 'true'
                    ),
                    wp_get_referer()
                )
            );
            exit;
        }


        // Get all the variables and validate
        $session_start = new DateTime($_POST['session_start']);
        $session_end = new DateTime($_POST['session_end']);

        $increments = mps_get_option('meeting_increments');

        // Make sure the end time is later than the start time
        if ($session_start >= $session_end) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                    'error' => 'true',
                    'message' => urlencode('End Time must be later than Start Time'),
                    ),
                    wp_get_referer()
                )
            );
            exit();
        }

        // Make sure the dates are multiples of the minute increment
        if ($session_start->format('i') % $increments != 0) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                    'error' => 'true',
                    'message' => urlencode('Start Time is not valid')
                    ),
                    wp_get_referer()
                )
            );
            exit();
        }
        if ($session_end->format('i') % $increments != 0) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                    'error' => 'true',
                    'message' => urlencode('End Time is not valid')
                    ),
                    wp_get_referer()
                )
            );
            exit();
        }

        // Make sure that the session start and end date are equal
        if ($session_start->format('Y-m-d') != $session_end->format('Y-m-d')) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                    'error' => 'true',
                    'message' => urlencode('Session should Start and End on the same day')
                    ),
                    wp_get_referer()
                )
            );
            exit();
        }

        // Make sure that a name has been given to the session
        if ($_POST['session_name'] == '') {
            wp_safe_redirect(
                add_query_arg(
                    array(
                    'error' => 'true',
                    'message' => urlencode('Invalid session name')
                    ),
                    wp_get_referer()
                )
            );
            exit();
        }

        $session = [];
        $session['name'] = sanitize_text_field(stripslashes($_POST['session_name']));
        $session['start_time'] = $session_start->format("Y-m-d H:i:s");
        $session['end_time'] = $session_end->format("Y-m-d H:i:s");
        $session['room'] = sanitize_title($_POST['session_room']);
        $session['url'] = esc_url($_POST['session_url']);
        // Cast checkboxes into boolean
        $session['is_intermission'] = isset($_POST['is_intermission']) ? 1 : 0;
        $session['is_streamed'] = isset($_POST['is_streamed']) ? 1 : 0;
        $session['is_rateable'] = isset($_POST['is_rateable']) ? 1 : 0;
        $session['is_social'] = isset($_POST['is_social']) ? 1 : 0;
        $session['hide_title'] = isset($_POST['hide_title']) ? 1 : 0;

        $format = [
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d'
        ];

        if ($session_id == 0) {
            $wpdb->insert($wpdb->prefix . 'ms_sessions', $session, $format);
        } else {
            $wpdb->update($wpdb->prefix . 'ms_sessions', $session, ['id' => $session_id], $format, ['%d']);
        }
        $this->mps_update_agora_schedule();

        wp_safe_redirect(
            add_query_arg(
                [
                    'updated' => 'true'
                ],
                wp_get_referer()
            )
        );
        exit;
    }

    public function mps_edit_sponsor_section_callback()
    {

        // Adding or editing a Sponsor Section, if sponsor_section_id == 0, then it's a new section.
        check_admin_referer('mps_edit_sponsor_section');

        // Get the fields we need from the post to process the request
        $section_id = (int) $_POST['section_id'];

        $section = [];
        $section['name'] = $_POST['section_name'];
        $section['text_colour'] = $_POST['section_text_colour'];
        $section['is_grayscale'] = (bool) $_POST['section_is_grayscale'];

        // Are we adding a new sponsor section or editing an existing one?
        if ($section_id == -1) {
            // Adding a new section
            $this->mps_add_sponsor_section($section);
        } else {
            // Editing an existing section
            $this->mps_update_sponsor_section($section_id, $section);
        }

            wp_safe_redirect(
                add_query_arg(
                    [
                        'updated' => 'true'
                    ],
                    wp_get_referer()
                )
            );
        exit;
    }

    public function mps_export_presentation_ratings_callback()
    {
        if (empty($_POST['export_sessions'])) {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'updated' => 'true'
                    ],
                    wp_get_referer()
                )
            );
            exit;
        }

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=ratings.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // output the column headings
        fputcsv(
            $output,
            [
                'Presentation Title',
                'Session',
                'Rating Content Count',
                'Rating Content Average',
                'Rating Delivery Count',
                'Rating Delivery Average',
                'Comments'
            ]
        );


        // We have some sessions to get ratings for, let's process them.
        foreach ($_POST['export_sessions'] as $session_id) {
            $session_info = ms_get_session_data($session_id);
            $session_slots = ms_get_session_slots($session_id);
            foreach ($session_slots as $slot) {
                $comments = [];
                $ratings = mps_get_slot_ratings($slot->id);
                $stats = mps_get_presentation_rating_stats($ratings);
                foreach ($ratings as $rating) {
                    if ($rating->rating_comment != '') {
                        $comments[] = $rating->rating_comment;
                    }
                }
                $slot_info = ms_get_slot($slot->id);
                $row = array(
                $slot_info->title,
                $session_info->name,
                $stats['rating_content_count'],
                $stats['rating_content_average'],
                $stats['rating_delivery_count'],
                $stats['rating_delivery_average'],
                implode("\r\n------------\r\n", str_replace("\n", "\r\n", $comments))
                    );
                $row = array_map("utf8_decode", $row);
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    }

    public function mps_edit_day_sponsor_callback()
    {
        $date = $_POST['date'];

        $sponsor_info = [];
        $sponsor_info['title'] = $_POST['sponsor_title'];
        $sponsor_info['title_url'] = $_POST['sponsor_title_url'];
        $sponsor_info['body'] = stripslashes($_POST['sponsor_body']);
        mps_update_option('sponsor_day_' . $date, $sponsor_info);
        wp_safe_redirect(
            wp_get_referer()
        );
        exit;
    }

    public function ms_get_day_sponsor_callback()
    {
        $date = $_POST['date'];
        echo json_encode(mps_get_option('sponsor_day_' . $date));
        exit;
    }

    public function mps_edit_presentation_callback()
    {

        global $wpdb;

        // Adding or editing a slot.
        check_admin_referer('mps_edit_presentation');

        // Get the fields we need from the post to process the request
        $presentation_id = (int) $_POST['presentation_id'];

        if (isset($_POST['delete-presentation'])) {
            $current_user = wp_get_current_user();
            // We should delete this presentation, keep the files though
            $wpdb->delete($wpdb->prefix . 'ms_presentations', array('id' => $presentation_id));
            mps_log($current_user->user_login . ' deleted presentation id #' . $presentation_id);
            wp_safe_redirect(
                add_query_arg(
                    [
                        'updated' => 'true'
                    ],
                    wp_get_referer()
                )
            );
            exit;
        }

        $presentation = [];
        $presentation['title'] = sanitize_text_field(stripslashes($_POST['presentation_title']));
        $presentation['author_name'] = sanitize_text_field(stripslashes($_POST['presentation_author']));
        $presentation['author_email'] = sanitize_email($_POST['presentation_author_email']);
        $presentation['author_uuid'] = sanitize_email($_POST['presentation_author_uuid']);
        $presentation['author_affiliation'] = sanitize_text_field(stripslashes($_POST['presentation_author_affiliation']));
        $presentation['session_id'] = (int) $_POST['presentation_session'];
        $presentation['slot_id'] = (int) $_POST['presentation_slot'];

        $files = [];

        foreach ($_POST['presentation_files'] as $file) {
            // Are we removing a file from the list?
            if (trim($file == '')) {
                continue;
            }
            $files[] = sanitize_file_name($file);
        }

        $presentation['filename'] = json_encode($files);

        $wpdb->update($wpdb->prefix . 'ms_presentations', $presentation, array('id' => $presentation_id));

        if ($_FILES['presentation_new_file']['size'] > 0) {
            // We are adding a new file to the presentation
            $this->mps_add_file_to_presentation($presentation_id);
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'updated' => 'true'
                ],
                wp_get_referer()
            )
        );
        exit;
    }

    private function mps_add_file_to_presentation($presentation_id)
    {
        /**
         * Function to update the $presentation_id row in ms_presentations to associate a file with it. Also to move
         * it to the right place
         *
         * NOTE: File has already been validated by the time this function is called, this is purely for
         * associating the previously-approved file with the presentation
         */

        $presentation = mps_get_presentation($presentation_id);

        if (! $presentation) {
            mps_log('Trying to attach a file to a non-existent presentation');
            return false;
        }

        // First we'll move the file to where we want it to be
        $file_key = 'presentation_new_file';

        $file_name = sanitize_file_name($presentation_id . '-' . $_FILES[$file_key]['name']);

        $presentation_dir = mps_get_option('presentations_dir');

        if (! $presentation_dir) {
            mps_log('Cannot handle file upload, presentation directory not defined');
            return false;
        }

        if (! move_uploaded_file($_FILES[$file_key]['tmp_name'], get_home_path() . $presentation_dir . $file_name)) {
            mps_log('Unable to move_uploaded_file()');
            return false;
        }

        // The file has been moved, let's associate it with the right presentation
        $success = $this->mps_update_presentation_file_list($presentation_id, $file_name);

        if (! $success) {
            mps_log('Cannot add file to database');
            return false;
        }

        return true;
    }

    private function mps_update_presentation_file_list($presentation_id, $file_name, $file_id = 999)
    {
        /**
         * Function to add a file reference to an existing presentation row.
         */

        global $wpdb;

        $presentation = mps_get_presentation($presentation_id);

        if (! $presentation) {
            return false;
        }

        $current_files = json_decode($presentation->filename);

        if ($file_id == 999) {
            $current_files[] = sanitize_file_name($file_name);
        } else {
            // If the filename is already in the array, don't add it again
            if (! in_array($file_name, $current_files)) {
                $current_files[$file_id] = sanitize_file_name($file_name);
            } else {
                unset($current_files[$file_id]);
            }
        }

            // Update existing presentation with the updated list of filename(s)
        $result = $wpdb->update(
            $wpdb->prefix . 'ms_presentations',
            array('filename' => json_encode($current_files) ),
            array('id' => $presentation_id )
        );

        // $result is false if error, or # rows updated (0 is bad)
        return $result;
    }

    public function mps_edit_slot_callback()
    {

        // Adding or editing a slot.
        check_admin_referer('mps_edit_slot');

        // Get the fields we need from the post to process the request
        $slot_id = (int) $_POST['slot_id'];

        // Build the array for the database
        $slot = [];
        $slot['title'] = stripslashes(sanitize_text_field($_POST['slot_title']));
        $slot['content'] = stripslashes($_POST['slot_content']);
        $slot['session_id'] = (int) $_POST['session_id'];
        $slot['ratable'] = isset($_POST['slot_rateable']) ? 1 : 0;
        $slot['parent_id'] = (int) $_POST['slot_parent_id'];

        // Get the next order number for slots in this session, if we need it.
        // We only want to assign it a new order if the session_id or parent_id has changed.
        if ($slot_id == 0) {
            $slot['ordering'] = $this->mps_get_next_slot_sort_number($slot['session_id'], $slot['parent_id']);
        } else {
            // Give the existing slot a new order if the session_id or parent_id has changed
            $current_slot = ms_get_slot($slot_id);
            if ($current_slot->session_id != $slot['session_id'] || $current_slot->parent_id != $slot['parent_id']) {
                $slot['ordering'] = $this->mps_get_next_slot_sort_number($slot['session_id'], $slot['parent_id']);
            } else {
                $slot['ordering'] = $current_slot->ordering;
            }
        }

            // Don't do anything if the title or content is empty
        if (trim($slot['title']) == '' || trim($slot['content']) == '') {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'error' => 'true',
                        'message' => urlencode('Slots require a valid title and content')
                    ],
                    wp_get_referer()
                )
            );
            exit;
        }

            // Are we dealing with a new slot or editing an existing one?
        if ($slot_id == 0) {
            // Adding a new slot
            $this->mps_add_slot($slot);
        } else {
            // Editing existing slot
            $this->mps_update_slot($slot_id, $slot);
        }

        wp_safe_redirect(wp_get_referer() . '&updated=true');
        exit;
    }

    private function mps_add_slot($slot)
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'ms_slots', $slot);
    }

    private function mps_update_slot($slot_id, $slot)
    {
        $slot_id = (int) $slot_id;
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'ms_slots', $slot, array('id' => $slot_id));
    }

    public function mps_edit_sponsor_callback()
    {

        check_admin_referer('mps_edit_sponsor');

        // Get the fields we need from the post to process the request
        $sponsor_id = (int) $_POST['sponsor_id'];

        $sponsor = [];
        $sponsor['section_id'] = (int) $_POST['sponsor_section_id'];
        $sponsor['name'] = sanitize_text_field($_POST['sponsor_name']);
        $sponsor['image_url'] = esc_url_raw($_POST['sponsor_logo_url']);
        $sponsor['link_url'] = esc_url_raw($_POST['sponsor_url']);

        // Get the next order id for sorting
        $next_order = (int) $this->mps_get_next_sponsor_sort_number($sponsor['section_id']);
        $sponsor['sort_order'] = $next_order;

        // $sponsor_id == -1 if we are adding a new sponsor, otherwise editing an existing one
        if ($sponsor_id == -1) {
            $this->mps_add_sponsor($sponsor);
        } else {
            $this->mps_update_sponsor($sponsor_id, $sponsor);
        }

        wp_safe_redirect(wp_get_referer() . '&updated=true');
        exit;
    }

    private function mps_get_next_sponsor_sort_number($section_id)
    {

        global $wpdb;

        $result = $wpdb->get_row(
            "SELECT MAX(sort_order) as max FROM " . $wpdb->prefix . "ms_sponsors WHERE section_id=" . $section_id
        );
        if ($result) {
            return $result->max + 1;
        }
        return 0;
    }

    private function mps_get_next_slot_sort_number($sid, $pid)
    {

        global $wpdb;

        $result = $wpdb->get_row(
            "SELECT MAX(ordering) as max FROM " . $wpdb->prefix . "ms_slots WHERE session_id=" . $sid . " AND parent_id=" . $pid
        );
        if ($result) {
            return $result->max + 1;
        }
        return 0;
    }


    private function mps_add_sponsor($sponsor)
    {

        global $wpdb;

        $format = ['%d', '%s', '%s', '%s', '%d'];

        $success = $wpdb->insert($wpdb->prefix . 'ms_sponsors', $sponsor, $format);

        return $success;
    }

    private function mps_update_sponsor($sponsor_id, $sponsor)
    {

        global $wpdb;

        $format = ['%d', '%s', '%s', '%s', '%d'];

        $success = $wpdb->update($wpdb->prefix . 'ms_sponsors', $sponsor, ['id' => $sponsor_id], $format);

        return $success;
    }

    private function mps_add_sponsor_section($section)
    {

        global $wpdb;

        $format = ['%s', '%s', '%d'];

        $success = $wpdb->insert($wpdb->prefix . 'ms_sponsor_sections', $section, $format);

        return $success;
    }

    private function mps_update_sponsor_section($s_id, $section)
    {

        global $wpdb;

        $format = ['%s', '%s', '%d'];

        $success = $wpdb->update($wpdb->prefix . 'ms_sponsor_sections', $section, ['id' => $s_id], $format, ['%d']);

        return $success;
    }

    public function mps_reset_password_user_callback()
    {

        check_admin_referer('mps_reset_password_user');

        $uuid = sanitize_text_field($_POST['user_uuid']);

        if (! $this->auth->resetPassword($uuid)) {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'error' => 'true',
                        'error_message' => urlencode('Password reset failed')
                    ],
                    admin_url('admin.php?page=' . $this->plugin_name . '-users')
                )
            );
            exit;
        }

        wp_safe_redirect(
            add_query_arg(
                ['updated' => 'true'],
                admin_url('admin.php?page=' . $this->plugin_name . '-users')
            )
        );
        exit;
    }

    public function ms_pick_random_rater_callback()
    {
        global $wpdb;
        $ratings = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "ms_presentation_ratings");
        $count = count($ratings);
        $random_index = rand(0, $count - 1);
        switch ($this->auth->auth_method) {
            case 'crowd':
                $random_person = (array) $this->auth->getCrowdUserByUUID($ratings[$random_index]->uuid);
                break;
            case 'local':
                $random_person = (array) $this->auth->getUserByUUID($ratings[$random_index]->uuid);
                break;
        }
        echo json_encode(['name' => $random_person['name'], 'email' => $random_person['email']]);
        exit;
    }

    public function mps_edit_user_callback()
    {

        // Check that the request came from a valid admin page
        check_admin_referer('mps_edit_user');

        global $wpdb;

        $wp_user = wp_get_current_user();

        // Get the session id, if it's 0 then we're adding a new user as opposed to editing one.
        $user_uuid = $_POST['user_uuid'];

        // Don't bother with all the validation stuff if the user should be deleted
        if ($user_uuid != '0' && $_POST['delete']) {
            $wpdb->delete($wpdb->base_prefix . 'ms_users', ['uuid' => $user_uuid], ['%s']);
            mps_log('[' . $wp_user->user_email . '] User deleted (' . $user_uuid . ')');
            wp_safe_redirect(add_query_arg(['updated' => 'true'], admin_url('admin.php?page=' . $this->plugin_name . '-users')));
            exit;
        }

        // Make sure the user has a valid name
        if (trim($_POST['user_name']) == '' || (!isset($_POST['user_name']))) {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'error' => 'true',
                        'error_message' => urlencode('Invalid User Name')
                    ],
                    admin_url('admin.php?page=' . $this->plugin_name . '-users')
                )
            );
            exit;
        }


        $new_uuid = Meeting_Support_Auth::v4();

        $user = [];
        if ($user_uuid == '0') {
            $user['uuid'] = $new_uuid;
        }
        $user['name'] = sanitize_text_field($_POST['user_name']);
        $user['email'] = sanitize_email($_POST['user_email']);
        $user['is_active'] = isset($_POST['is_active']) ? '1' : '0';

        $format = [
            '%s',
            '%s',
            '%s',
            '%d'
        ];

        if ($user_uuid == '0') {
            // User ID is '0', adding a new user
            $password = mps_generate_password(8);

            // Generate a bcrypt hash to save to the database
            $user['password'] = password_hash($password, PASSWORD_BCRYPT);

            $success = $wpdb->insert($wpdb->base_prefix . 'ms_users', $user, $format);

            if ($success == false) {
                wp_safe_redirect(
                    add_query_arg(
                        [
                            'error' => 'true',
                            'error_message' => urlencode('Email address already in system')
                        ],
                        admin_url('admin.php?page=' . $this->plugin_name . '-users')
                    )
                );
                exit;
            } else {
                // Update existing submissions with the same email address to match the new UUID.
                $wpdb->update(
                    $wpdb->base_prefix . 'ms_pc_submissions',
                    ['author_uuid' => $user['uuid']],
                    ['author_email' => $user['email'], 'author_uuid' => '']
                );

                // Send mail to user with their new password
                $mail = new PHPMailer;
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->isHTML(false);
                $mail->setFrom(
                    mps_get_option('sender_email_address', 'ws@ripe.net'),
                    mps_get_option('sender_email_name', 'RIPE NCC Web Services')
                );
                $mail->addAddress($user['email']);

                $loader = new Twig_Loader_Filesystem(realpath(plugin_dir_path(__FILE__) . '../templates/mail'));
                $twig = new Twig_Environment($loader, []);

                $mailcontent = $twig->render('new_user.twig', [
                    'meeting_name' => mps_get_option('meeting_name'),
                    'user' => $user,
                    'password' => $password,
                    'login_url' => home_url('login')
                ]);

                $mail->Subject = 'New account created for ' . mps_get_option('meeting_name');
                $mail->Body  = $mailcontent;
                $mail_sent = $mail->send();

                mps_log('[' . $wp_user->user_email . '] User added (' . $user['name'] . '), password sent to ' . $user['email'] . ' -  Success: ' . ($mail_sent == 1 ? 'Yes' : 'No'));
            }
        } else {
            // User ID has been set, so we will add a new user
            $wpdb->update($wpdb->base_prefix . 'ms_users', $user, ['uuid' => $user_uuid], $format, ['%s']);
            mps_log('[' . $wp_user->user_email .'] User Edited (' . $user_uuid . ')');
        }

        wp_safe_redirect(
            add_query_arg(
                ['updated' => 'true'],
                admin_url('admin.php?page=' . $this->plugin_name . '-users')
            )
        );
        exit;
    }

    /**
     * Print the HTML for managing the rooms within the admin area
     * @return void
     */
    public function mps_rooms_setting_callback()
    {

        // Fetch the rooms in storage, use default rooms as backup
        $rooms = mps_get_option('rooms');
        foreach ($rooms as $id => $room) {
            echo '<input type="text" name="mps_rooms['.$id.'][short]" value="'.htmlspecialchars(stripslashes($room['short'])).'"/>';
            echo ' - ';
            echo '<input type="text" name="mps_rooms['.$id.'][long]" value="'.htmlspecialchars(stripslashes($room['long'])).'"/>';
            echo ' - ';
            echo '<input title="Background colour" type="color" name="mps_rooms['.$id.'][colour]" value="'.$room['colour'].'"/>';
            echo ' - ';
            echo '<input title="Text colour" type="color" name="mps_rooms['.$id.'][text_colour]" value="'.$room['text_colour'].'"/>';
            echo ' - ';
            if ($room['in_legend'] == '1') {
                echo '<input checked="checked" title="Show in legend" type="checkbox" name="mps_rooms['.$id.'][in_legend]"><br>';
            } else {
                echo '<input title="Show in legend" type="checkbox" name="mps_rooms['.$id.'][in_legend]"><br>';
            }
        }
        // Add an extra empty group at the bottom to add a new room
        echo '<input type="text" placeholder="key" name="mps_rooms['.( $id+1).'][short]"/>';
        echo ' - ';
        echo '<input type="text" placeholder="Room Name" name="mps_rooms['.( $id+1).'][long]"/>';
        echo ' - ';
        echo '<input title="Background colour" type="color" name="mps_rooms['.( $id+1 ).'][colour]" value="#FFFFFF"/>';
        echo ' - ';
        echo '<input title="Text colour" type="color" name="mps_rooms['. ( $id+1 ) .'][text_colour]"/>';
        echo ' - ';
        echo '<input title="Show in legend" type="checkbox" name="mps_rooms['. ( $id+1 ) .'][in_legend]"/><br>';
    }

    /**
     * Print the HTML for managing the intermission room
     * @return void
     */
    public function mps_intermission_config_setting_callback()
    {
        // Fetch the rooms in storage, use default rooms as backup
        $config = mps_get_option('intermission_config', array('colour' => '#FFFFFF'));
        // Add an extra empty group at the bottom to add a new room
        echo '<input type="color" name="mps_intermission_config[colour]" value="' . $config['colour'] .'"/> - Background<br>';
        echo '<input type="color" name="mps_intermission_config[text_colour]" value="' . $config['text_colour'] .'"/> - Text';
    }

    /**
     * Print the HTML for setting the authentication method
     * @return void
     */
    public function mps_auth_method_setting_callback()
    {

        $methods = ['local', 'crowd'];

        $allow_crowd = false;
        if (strpos($_SERVER['HTTP_HOST'], 'ripe.net')) {
            $allow_crowd = true;
        }

        // Fetch the current authentication method
        $current_method = $this->auth->auth_method;

        echo '<select name="mps_auth_method">';
        foreach ($methods as $method) {
            echo '<option ' . ($method == 'crowd' && $allow_crowd == false ? 'disabled' : '') . ' ';
            echo ($method == $current_method ? 'selected="selected"' : '') . ' value=' . $method . '>' . ucfirst($method) . '</option>';
        }
        echo '</select>';
    }


    /**
     * Print the HTML for a specified text field
     * @return void
     *
     * @param $config array
     */
    public function mps_text_field_setting_callback($config)
    {

        if (isset($config['default'])) {
            $current_value = get_option($config['name'], $config['default']);
        } else {
            $current_value = get_option($config['name']);
        }

        // Is a placeholder set?
        if (isset($config['placeholder'])) {
            $placeholder = 'placeholder="' . $config['placeholder'] . '"';
        } else {
            $placeholder = '';
        }
        echo '<input type="text" '. $placeholder .' name="'.$config['name'].'" value="' . $current_value . '">';
    }

    /**
     * Print the HTML for creating the WG Chairs pages
     * @return void
     *
     */
    public function mps_recreate_wg_chairs_area_callback($config)
    {
        $working_groups = $this->mps_get_wg_chairs_list();
        echo '<b>(Re)create pages for the RIPE Working Group Chairs</b>';
        echo ' <button id="expand-wg-chair-bios" type="button">Expand</button>';
        echo ' <button id="collapse-wg-chair-bios" type="button">Collapse</button>';
        echo '<br>';
        echo '<br>';
        echo '<div id="wg-chairs-pages">';
        foreach ($working_groups as $wg) {
            echo '<div class="working-group">';
            echo '<a target="_blank" href="' . $wg['url'] . '">' . $wg['nice_title'] . '</a>';
            echo '<ul>';
            foreach ($wg['chairs'] as $chair) {
                if (isset($chair['bio']['url'])) {
                    echo '<li><a target="_blank" href="' . $chair['bio']['url'] . '">' . $chair['title'] . '</a></li>';
                } else {
                    echo '<li>' . $chair['title'] . '</li>';
                }
            }
            echo '</ul>';
            echo '</div>';
        }
        echo '<button id="btn-flush-cache-wg-chairs-area" type="button">Flush Cache</button> ';
        echo '<button id="btn-create-wg-chairs-area" type="button">(Re)create pages</button>';
        echo '</div>';
    }


    private function mps_get_wg_chairs_list()
    {
        if (false === ($working_groups = get_transient('mps_get_working_group_chair_bios'))) {
            $url = 'https://www.ripe.net/participate/ripe/wg/@@all-working-groups?_nocache_';
            $data = file_get_contents($url);
            $schema = json_decode($data, true);
            $working_groups = $schema['working_groups'];

            // Let's tidy up the list a bit

            // Sort by WG Title
            usort($working_groups, function ($a, $b) {
                return strcasecmp($a['title'], $b['title']);
            });

            // Sort WG Chairs alphabetically
            foreach ($working_groups as &$wg) {
                sort($wg['chairs']);
            }
            // unset() to break reference
            unset($wg);

            foreach ($working_groups as $wg_k => $wg) {
                // If the chairs list is empty, don't show it
                if (empty($wg['chairs'])) {
                    unset($working_groups[$wg_k]);
                    continue;
                }

                // Add an extra key for a nicer title which doesn't include 'Working Group'
                $working_groups[$wg_k]['nice_title'] = trim(str_replace('Working Group', '', $wg['title']));

                // Iterate through the chairs in this WG
                foreach ($wg['chairs'] as $k => $chair) {
                    // Add a slug to link to the bio, but only if they have a bio.
                    // Strip <img> tags too
                    $allowed_tags = [
                        "a", "b", "br", "em", "hr", "i", "li", "ol", "p", "s", "strong", "span", "table", "tr", "td", "u", "ul"
                    ];
                    $allowed_tags_list = '';

                    foreach ($allowed_tags as $tag) {
                        $allowed_tags_list .= '<' . $tag . '>';
                    }

                    // Regex for empty <p></p> instances
                    $pattern = "/<p[^>]*><\\/p[^>]*>/";

                    if (! empty($chair['bio'])) {
                        // We can't reference $chair directly because we're creating a new key
                        $working_groups[$wg_k]['chairs'][$k]['slug'] = sanitize_title($chair['title']);

                        // Start making the cleaner bio text
                        $text = $chair['bio']['text'];
                        $text = strip_tags($chair['bio']['text'], $allowed_tags_list);
                        $text = preg_replace($pattern, '', $text);
                        $working_groups[$wg_k]['chairs'][$k]['bio']['text'] = $text;
                    }

                    // Create a base64 encoded image so we don't have to save anything to the filesystem
                    // We can't reference $chair directly because we're creating a new key
                    $working_groups[$wg_k]['chairs'][$k]['image_b64'] = base64_encode(file_get_contents($chair['url'] . '/image'));

                }
                // unset() to break reference()
                unset($chair);
            }
            // Cache it
            set_transient('mps_get_working_group_chair_bios', $working_groups);
        }
        return $working_groups;
    }

    /**
     * Print the HTML for creating the PCSS Area
     * @return void
     *
     */
    public function mps_create_pcss_area_callback()
    {
        $structure = mps_get_pcss_area_structure();
        $pages_available = 0;
        echo '<b>Create Pages for the Programme Committee Submission System (PCSS)</b><br>The PCSS is used by the PC to review proposed topics';
        echo '<br>';
        echo '<br>';
        foreach ($structure as $first_level) {
            // Looping through elements in first level
            echo '<label>';

            if (get_page_by_path(sanitize_title($first_level['name']))) {
                echo '<input type="checkbox" disabled="disabled" checked="checked">';
            } else {
                echo '<input class="checkbox-create-pcss-area" type="checkbox" data-path="' . sanitize_title($first_level['name']) . '" value="' . $first_level['name'] . '">';
                $pages_available++;
            }
            echo $first_level['name'];
            echo '</label>';
            echo '<br>';
            foreach ($first_level['children'] as $second_level) {
                echo '<label>';
                if ($page = get_page_by_path(sanitize_title($first_level['name']) . '/' . sanitize_title($second_level['name']))) {
                    echo '&nbsp; - &nbsp;<input type="checkbox" disabled="disabled" checked="checked">';
                } else {
                    echo '&nbsp; - &nbsp;<input class="checkbox-create-pcss-area" type="checkbox" data-path="' . sanitize_title($first_level['name']) . '/' . sanitize_title($second_level['name']) . '" value="' . $second_level['name'] . '">';
                    $pages_available++;
                }
                echo $second_level['name'];
                echo '</label>';
                echo '<br>';
            }
        }
        echo '<br>';
        if ($pages_available > 0) {
            echo '<button id="btn-create-pcss-area" type="button">Create PCSS Page(s)</button>';
        } else {
            echo '<button id="btn-create-pcss-area" disabled="disabled" type="button">Create PCSS Page(s)</button>';
        }
    }


    /**
     * Print the HTML for managing which submission types require an upload to be included
     * @return void
     *
     */
    public function mps_pc_required_uploads_callback()
    {

        $pc_config = pc_config();
        $submission_types = $pc_config['submission_types'];


        $file_mandatory_submission_types = (array) mps_get_option('pc_file_mandatory_submission_types', ['1', '2', '3', '4']);

        foreach ($submission_types as $submission_type_id => $submission_type) {
            echo '<label>';
            if (! in_array($submission_type_id, $file_mandatory_submission_types)) {
                echo '<input type="checkbox" name="mps_pc_file_mandatory_submission_types[]" value="' . $submission_type_id . '">';
            } else {
                echo '<input type="checkbox" checked="checked" name="mps_pc_file_mandatory_submission_types[]" value="' . $submission_type_id . '">';
            }
            echo $submission_type;
            echo '</label>';
            echo '<br>';
        }
    }

    /**
     * Print the HTML for a specified textarea
     * @return void
     *
     * @param $config array
     * @param $config['placeholder'] Placeholder to be used in the text field
     * @param $config['name'] Input name to be used in the text field
     */
    public function mps_textarea_field_setting_callback($config)
    {
        // Get current value
        $rows = "2";
        $pc_config = pc_config();
        $placeholder = '';

        if (isset($config['placeholder'])) {
            $placeholder = $config['placeholder'];
        }

        // Special config for certain textareas
        switch ($config['name']) {
            case 'mps_pc_message_sign_in':
                $default = $pc_config['messages']['sign_in_required']['en'];
                break;

            case 'mps_pc_accepted_template':
                $default = $pc_config['mail_templates']['acceptance']['body'];
                $rows = "10";
                break;

            case 'mps_pc_declined_template':
                $default = $pc_config['mail_templates']['rejection']['body'];
                $rows = "10";
                break;

            default:
                $default = '';
                break;
        }

        $current_value = get_option($config['name'], $default);

        echo '<textarea rows="' . $rows . '" name="' . $config['name'] . '" placeholder="' . $placeholder . '">';
        echo $current_value;
        echo '</textarea>';
    }


    /**
     * Print the HTML for the allowed file types
     * @return void
     *
     * @param $config array
     * @param $config['placeholder'] Placeholder to be used in the text field
     * @param $config['name'] Input name to be used in the text field
     */
    public function mps_allowed_file_types_setting_callback($config)
    {

        $defaults = [
            'png',
            'jpg',
            'ppt',
            'key',
            'pdf',
            'doc',
            'xls',
            'docx',
            'xlsx',
            'pptx',
            'odt',
            'odp',
            'txt',
            'zip'
        ];

        $current_value = get_option($config['name'], $defaults);

        // Is a placeholder set?
        if ($config['placeholder']) {
            $placeholder = 'placeholder="' . $config['placeholder'] . '"';
        } else {
            $placeholder = '';
        }
        $value = implode(', ', $current_value);
        echo '<input type="text" ' . $placeholder . ' name="' . $config['name'] . '" value="' . $value . '">';
    }

    /**
     * Print the HTML for the allowed submission types
     * @return void
     *
     * @param $config array
     * @param $config['placeholder'] Placeholder to be used in the text field
     * @param $config['name'] Input name to be used in the text field
     */
    public function mps_submission_types_setting_callback($config)
    {

        // Fetch the rooms in storage, use default rooms as backup
        $defaults = [
            'Plenary',
            'BoF',
            'Tutorial',
            'Workshop',
            'Lightning Talk'
        ];

        $current_value = get_option($config['name'], $defaults);

        // Is a placeholder set?
        if ($config['placeholder']) {
            $placeholder = 'placeholder="'.$config['placeholder'].'"';
        } else {
            $placeholder = '';
        }
        $value = implode(', ', $current_value);
        echo '<input type="text" ' . $placeholder .' name="' . $config['name'] . '" value="' . $value . '">';
    }

    /**
     * Print the HTML for a specified password field
     * @return void
     *
     * @param $config['name'] Input name to be used in the text field
     */
    public function mps_password_field_setting_callback($config)
    {

        // Fetch the rooms in storage, use default rooms as backup
        $current_value = get_option($config['name']);

        // Is a placeholder set?
        echo '<input type="password" name="'.$config['name'].'" value="' . $current_value . '">';
    }


    /**
     * Print the HTML for a specified checkbox field
     * @return void
     *
     * @param $config['name'] string Input name to be used for the checkbox
     */
    public function mps_checkbox_setting_callback($config)
    {

        // Fetch current value of the config, so we can check the box if it's already set
        $current_value = get_option($config['name']);

        if ($current_value != '') {
            echo '<input type="checkbox" name="'.$config['name'].'" checked="checked">';
        } else {
            echo '<input type="checkbox" name="'.$config['name'].'">';
        }
    }


    /**
     * Print the HTML for setting the [start|end] date of the agenda
     * @return void
     *
     * @param $start_or_end string To complete the entire variable name
     */
    public function mps_agenda_time_setting_callback($start_or_end)
    {

        // Minute increments
        $increments = mps_get_option('meeting_increments', 15);

        // Fetch the date from storage
        $date = mps_get_option('agenda_' . $start_or_end . '_time');

        echo '<input type="time" name="mps_agenda_' . $start_or_end . '_time" value="' . $date . '" step="' . $increments * 60 . '"/>';
        echo '<p><i>Must be in <span class="mps_meeting_increments_value">' . $increments . '</span> minute increments</i></p>';
    }

    /**
     * Print the HTML for setting the [start|end] date of the meeting
     * @return void
     *
     * @param $start_or_end string To complete the entire variable name
     */
    public function mps_meeting_date_setting_callback($start_or_end)
    {

        // Fetch the date from storage
        $date = mps_get_option('meeting_' . $start_or_end . '_date');

        echo '<input type="date" name="mps_meeting_' . $start_or_end . '_date" value="' . $date . '"/>';
    }

    /**
     * Print the HTML for setting the allow minute increments for the agenda
     * @return void
     *
     */
    public function mps_meeting_increments_setting_callback()
    {

        // Fetch the increments from storage, 15 minutes fallback
        $increments = mps_get_option('meeting_increments', 15);

        echo '<input type="range" name="mps_meeting_increments" value="' . $increments . '" min="5" max="30" step="5"/>';
        echo '<span class="fix mps_meeting_increments_value"></span>';
    }

    public function mps_add_pc_user_callback()
    {

        check_admin_referer('mps_add_pc_user');

        global $wpdb;

        $email = $_POST['new_user_email'];
        $access_level = (int) $_POST['new_user_access_level'];

        // Get the UUID for the email address given
        switch ($this->auth->auth_method) {
            case 'local':
                $user = $this->auth->getLocalUserByEmail($email);
                // If we didn't get a user back, then we can die.
                if (! $user) {
                    wp_safe_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-pc_users&error=true'));
                    exit;
                }
                // Is this user already in the pc_users table?
                $count = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->base_prefix . 'ms_pc_users WHERE uuid="' . $user['uuid'] . '"');
                if ($count != 0) {
                    wp_safe_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-pc_users&error=true'));
                    exit;
                }
                $wpdb->insert($wpdb->base_prefix . 'ms_pc_users', array('uuid' => $user['uuid'], 'access_level' => $access_level ));
                break;

            case 'crowd':
                $user = $this->auth->getCrowdUserByEmail($email);
                if (! $user) {
                    wp_safe_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-pc_users&error=true'));
                    exit;
                }
                // Is this user already in the pc_users table?
                $count = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->base_prefix . 'ms_pc_users WHERE uuid="' . $user['uuid'] . '"');
                if ($count != 0) {
                    wp_safe_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-pc_users&error=true'));
                    exit;
                }
                $wpdb->insert($wpdb->base_prefix . 'ms_pc_users', array('uuid' => $user['uuid'], 'access_level' => $access_level ));
                break;
        }

        wp_safe_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-pc_users&updated=true'));
        exit;
    }

    public function ms_update_pc_user_callback()
    {

        // TODO CSRF token

        global $wpdb;

        $uuid = $_POST['uuid'];
        $access_level = (int) $_POST['access_level'];

        $wpdb->update(
            $wpdb->base_prefix . 'ms_pc_users',
            array('access_level' => $access_level),
            array('uuid' => $uuid)
        );

        exit;
    }

    public function ms_get_sponsor_section_callback()
    {

        $section_id = (int) $_POST['id'];

        global $wpdb;

        $section = $wpdb->get_row(
            "
            SELECT * FROM " . $wpdb->prefix . "ms_sponsor_sections WHERE id = " . $section_id . "
            "
        );

        echo json_encode($section);
        exit;
    }


    public function ms_do_shortcode_callback()
    {

        $shortcode = $_POST['shortcode'];

        echo json_encode(do_shortcode($shortcode));
        exit;
    }

    public function ms_get_session_slots_callback()
    {

        $session_id = (int) $_POST['session_id'];

        echo json_encode(ms_get_session_slots($session_id));
        exit;
    }

    public function ms_get_slot_info_callback()
    {

        $slot_id = (int) $_POST['slot_id'];

        echo json_encode(ms_get_slot($slot_id));
        exit;
    }

    public function ms_get_presentation_callback()
    {
        $presentation_id = (int) $_POST['presentation_id'];
        echo json_encode(mps_get_presentation($presentation_id));
        exit;
    }

    public function ms_swap_slots_callback()
    {

        $slot_1 = (int) $_POST['slot_1'];
        $slot_2 = (int) $_POST['slot_2'];

        $slot_1 = ms_get_slot($slot_1);
        $slot_2 = ms_get_slot($slot_2);

        // Only do stuff if we have 2 good slots
        if ($slot_1 && $slot_2) {
            $this->ms_swap_slots($slot_1, $slot_2);
        }

        exit;
    }

    public function ms_delete_slot_callback()
    {

        $slot_id = (int) $_POST['slot_id'];

        if ($slot_id) {
            // Delete the slot
            $this->ms_delete_slot($slot_id);
            // Delete any slots which have just been orphaned
            $this->ms_delete_slot_children($slot_id);
        }

        exit;
    }

    public function ms_delete_slot($slot_id)
    {

        global $wpdb;

        $success = $wpdb->delete($wpdb->prefix . 'ms_slots', array('id' => $slot_id));

        return $success;
    }

    public function ms_delete_candidate_callback()
    {
        $candidate_id = (int) $_POST['candidate_id'];

        global $wpdb;

        $success = $wpdb->delete($wpdb->prefix . 'ms_pc_candidates', array('id' => $candidate_id));

        echo json_encode(array('success' => $success));
        exit;
    }

    public function ms_delete_speaker_callback()
    {
        $speaker_id = (int) $_POST['speaker_id'];

        global $wpdb;

        $success = $wpdb->delete($wpdb->prefix . 'ms_speakers', array('id' => $speaker_id));
        echo json_encode(array('success' => $success));

        exit;
    }

    public function ms_create_wg_chair_bios_callback()
    {
        // Flush the cache
        mps_log('Flushing cache for WG Chair Bios');
        delete_transient('mps_get_working_group_chair_bios');

        // If we're only flushing, stop there and return
        if ($_POST['flush_only'] == 'true') {
            wp_send_json_success();
        }

        mps_log('Creating/Updating WG Chair Bios pages');

        $parent_page_title = 'RIPE WG Chairs';

        // Try to get the page, if it doesn't exist, then create it.
        $parent_page = get_page_by_title($parent_page_title);
        if (! $parent_page) {
            // This page should be a child of 'Programme', so let's get that page
            $programme_page = get_page_by_title('Programme');
            if (! $programme_page) {
                mps_log("Programme Page doesn't exist, can't continue.");
                wp_send_json_error("Programme Page doesn't exist");
            }

            mps_log("Creating parent 'RIPE WG Chairs' page");
            // Page doesn't exist, let's make it.
            wp_insert_post([
                'post_title' => $parent_page_title,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_parent' => $programme_page->ID
            ]);

            $parent_page = get_page_by_title($parent_page_title);
        }

        $working_groups = $this->mps_get_wg_chairs_list();


        // Now we definitely have a parent page, we can make the speaker pages
        foreach ($working_groups as $wg) {
            foreach ($wg['chairs'] as $chair) {
                if (isset($chair['slug'])) {
                    mps_log('Creating/Updating WG Chair page "' . $chair['title'] . '"');
                    // If a page with this title already exists, update it. Otherwise create a new one
                    $page_id = 0;

                    $page = get_page_by_title($chair['title']);
                    if ($page) {
                        $page_id = $page->ID;
                    }

                    // Prepare the bio text a bit
                    $text = $chair['bio']['text'];
                    $image_html = '<p style="margin-bottom: 0;"><img class="size-medium alignleft" src="data:image/jpeg;base64, ' . $chair['image_b64'] . '" alt="' . $chair['title'] . '" /></p>';

                    // Stick it all together
                    $text = $image_html . $text;

                    $page_id = wp_insert_post([
                        'ID' => $page_id,
                        'post_title' => $chair['title'],
                        'post_status' => 'publish',
                        'post_type' => 'page',
                        'post_parent' => $parent_page->ID,
                        'post_content' => $text
                    ]);

                    mps_exclude_page_from_nav($page_id);
                    
                }
            }
        }

        $parent_page_content = 'RIPE Working Groups focus on specific topics that are of importance to the Internet community. Through discussion on the mailing lists and engagement at RIPE Meetings, working groups come together to develop policies, to track new developments, to promote collaboration and research, and to tackle various issues facing the community as a whole.

            Each working group has a dedicated session in the RIPE Meeting agenda for presentations on topics relevant to the working group\'s agenda.' . PHP_EOL;
        
        foreach ($working_groups as $wg) {
            $parent_page_content .= '<h2>' . $wg['nice_title'] . ' ';
            $parent_page_content .= '<a target="_blank" href="' . $wg['url'] . '"><i class="fa fa-info-circle"></i></a> ';
            $parent_page_content .= '<a target="_blank" title="Contact the Working Group Chairs" href="mailto:' . @$wg['email'] . '"><i class="fa fa-at"></i></a>';
            $parent_page_content .= '</h2> ';
            $parent_page_content .= '<div class="boot">';
            $parent_page_content .= '<div class="row">';
            foreach ($wg['chairs'] as $chair) {
                $parent_page_content .= '<div class="col-xs-2">';
                $parent_page_content .= '[caption align="alignleft" width="93"]';
                if (! empty($chair['slug'])) {
                    $parent_page_content .= '<a href="' . $chair['slug'] . '">';
                }
                $parent_page_content .= '<img class="size-full" src="data:image/png;base64, ' . $chair['image_b64'] . '" alt="' . $chair['title'] . '" width="93" height="128" /> ';
                if (! empty($chair['slug'])) {
                    $parent_page_content .= '</a>';
                    $parent_page_content .= '<a href="' . $chair['slug'] . '">';
                }
                $parent_page_content .= $chair['title'];
                if (! empty($chair['slug'])) {
                    $parent_page_content .= '</a>';
                }
                $parent_page_content .= '[/caption]';
                $parent_page_content .= '</div>';
            }
            $parent_page_content .= '</div>';
            $parent_page_content .= '</div>';
            $parent_page_content .= '<div class="clear"></div>';
        }

        wp_update_post([
            'ID' => $parent_page->ID,
            'post_content' => $parent_page_content
        ]);

        wp_send_json_success('Page updated');
    }

    public function ms_create_pcss_area_callback()
    {
        $pages_to_create = $_POST['pages'];
        if (! $pages_to_create) {
            echo json_encode(array('success' => false));
            exit;
        }
        // These are the pages that have been requested to be made. Let's make sure they don't exist and then create them if they don't.
        foreach ($pages_to_create as $path) {
            $check = get_page_by_path($path);
            if (! $check) {
                // Is the page a child of another page?
                if (strpos($path, '/') !== false) {
                    $is_child = true;
                } else {
                    $is_child = false;
                }
                // If is it a child, make sure its parent exists.
                if ($is_child) {
                    $parent_path = explode('/', $path)[0];
                    $parent_check = get_page_by_path($parent_path);

                    if (! $parent_check && ! in_array($parent_path, $pages_to_create)) {
                        // We can't make this page, since the parent doesn't exist and we don't want to create it.
                        continue;
                    }

                    if (! $parent_check && in_array($parent_path, $pages_to_create)) {
                        // Parent doesn't exist but we want to create it, let's do that now.
                        $parent_meta = mps_get_pcss_area_by_path($parent_path);
                        if ($parent_meta) {
                            // We got a parent, let's make it.
                            $parent_id = wp_insert_post(
                                array(
                                    'post_content' => $parent_meta['content'],
                                    'post_title' => $parent_meta['name'],
                                    'post_status' => 'publish',
                                    'post_type' => 'page',
                                )
                            );

                            if ($parent_id) {
                                // We made the page, exclude from navigation if we want to
                                if ($parent_meta['hide']) {
                                    mps_exclude_page_from_nav($parent_id);
                                }
                                $parent_check = get_page_by_path($parent_path);
                            } else {
                                echo json_encode(array('success' => false));
                                exit;
                            }
                        }
                        echo json_encode($parent_meta);
                        exit;
                    }

                    if ($parent_check) {
                        // Parent exists, we can make the page now
                        $child_meta = mps_get_pcss_area_by_path($path);

                        $page_id = wp_insert_post(
                            array(
                                'post_content' => $child_meta['content'],
                                'post_title' => $child_meta['name'],
                                'post_status' => 'publish',
                                'post_type' => 'page',
                                'post_parent' => $parent_check->ID
                            )
                        );

                        if ($page_id) {
                            if ($child_meta['hide']) {
                                mps_exclude_page_from_nav($page_id);
                            }
                        } else {
                            echo json_encode(array('success' => false));
                            exit;
                        }
                    }
                } else {
                    // This is a parent page
                    $page_meta = mps_get_pcss_area_by_path($path);

                    $page_id = wp_insert_post(
                        array(
                            'post_content' => $page_meta['content'],
                            'post_title' => $page_meta['name'],
                            'post_status' => 'publish',
                            'post_type' => 'page'
                        )
                    );

                    if ($page_meta['hide']) {
                        mps_exclude_page_from_nav($page_id);
                    }
                }
            }
        }
        echo json_encode(array('success' => true));
        exit;
    }

    public function ms_delete_slot_children($slot_id)
    {

        global $wpdb;

        $success = $wpdb->delete($wpdb->prefix . 'ms_slots', array('parent_id' => $slot_id));

        return $success;
    }

    public function ms_swap_slots($slot_1, $slot_2)
    {

        if (! $slot_1 || ! $slot_2) {
            return false;
        }

        global $wpdb;

        // We only want to swap the ordering if the parent_id and session_id are the same
        if ($slot_1->parent_id != $slot_2->parent_id || $slot_1->session_id != $slot_2->session_id) {
            return false;
        }

        // Give $slot_1 the ordering of $slot_2
        $wpdb->update($wpdb->prefix . 'ms_slots', array('ordering' => $slot_2->ordering), array('id' => $slot_1->id));

        // Give $slot_2 the ordering of $slot_1
        $wpdb->update($wpdb->prefix . 'ms_slots', array('ordering' => $slot_1->ordering), array('id' => $slot_2->id));

        return true;
    }

    public function ms_move_sponsor_up_callback()
    {

        $sponsor_id = (int) $_POST['id'];

        $sponsor_up = $this->ms_get_sponsor($sponsor_id);

        if (! $sponsor_up) {
            return false;
        }

        $current_order = $sponsor_up->sort_order;
        $new_order = $current_order - 1;

        $sponsor_down = $this->ms_get_sponsor_by_order($sponsor_up->section_id, $new_order);

        $this->ms_move_sponsor_down($sponsor_down);
        $this->ms_move_sponsor_up($sponsor_up);
        exit;
    }

    public function ms_move_sponsor_down_callback()
    {

        $sponsor_id = (int) $_POST['id'];

        $sponsor_down = $this->ms_get_sponsor($sponsor_id);

        if (! $sponsor_down) {
            return false;
        }

        $current_order = $sponsor_down->sort_order;
        $new_order = $current_order + 1;

        $sponsor_up = $this->ms_get_sponsor_by_order($sponsor_down->section_id, $new_order);

        $this->ms_move_sponsor_up($sponsor_up);
        $this->ms_move_sponsor_down($sponsor_down);
        exit;
    }


    public function ms_delete_sponsor_callback()
    {

        $sponsor_id = (int) $_POST['id'];

        $this->ms_delete_sponsor($sponsor_id);
        exit;
    }

    public function ms_get_sponsor_callback()
    {

        $sponsor_id = (int) $_POST['id'];

        $sponsor = $this->ms_get_sponsor($sponsor_id);

        echo json_encode($sponsor);
        exit;
    }


    private function ms_delete_sponsor($sponsor_id)
    {

        global $wpdb;

        $sponsor_id = (int) $sponsor_id;

        $wpdb->delete($wpdb->prefix . 'ms_sponsors', array('id' => $sponsor_id));

        return true;
    }

    private function ms_move_sponsor_up($sponsor)
    {

        global $wpdb;

        if (! $sponsor) {
            return false;
        }

        $new_order = (int) $sponsor->sort_order - 1;

        $wpdb->get_results(
            "
            UPDATE " . $wpdb->prefix . "ms_sponsors SET sort_order = " . $new_order . " WHERE id = " . $sponsor->id . "
            "
        );
    }

    private function ms_move_sponsor_down($sponsor)
    {

        global $wpdb;

        if (! $sponsor) {
            return false;
        }

        $new_order = (int) $sponsor->sort_order + 1;

        $wpdb->get_results(
            "
            UPDATE " . $wpdb->prefix . "ms_sponsors SET sort_order = " . $new_order . " WHERE id = " . $sponsor->id . "
            "
        );
    }

    private function ms_get_sponsor($id)
    {

        global $wpdb;

        $id = (int) $id;

        $sponsor = $wpdb->get_row(
            "
            SELECT * FROM " . $wpdb->prefix . "ms_sponsors WHERE id = " . $id . "
            "
        );

        return $sponsor;
    }

    private function ms_get_sponsor_by_order($section_id, $sort_order)
    {

        global $wpdb;

        $section_id = (int) $section_id;
        $sort_order = (int) $sort_order;

        $sponsor = $wpdb->get_row(
            "
            SELECT * FROM " . $wpdb->prefix . "ms_sponsors WHERE section_id = " . $section_id . " AND sort_order = " . $sort_order . "
            "
        );

        return $sponsor;
    }

    /**
     * Create the directory used for presentation uploads, return a slightly sanitise string, create a symbolic link too.
     *
     * @return string
     */
    public function mps_create_presentations_dir()
    {
        $wordpress_home_dir = get_home_path();
        $presentations_dir = $_POST['mps_presentations_dir'];

        // Does the specified directory end with a '/'? If not, let's put it on the end
        if (substr($presentations_dir, -1) != '/') {
            $presentations_dir .= '/';
        }

        // Create the directory if it doesn't already exist
        $tocreate = $wordpress_home_dir . $presentations_dir;
        if (! file_exists($tocreate)) {
            mkdir($tocreate);
        }

        // Now $tocreate exists, we can run realpath() against it
        $tocreate = realpath($tocreate);

        // TODO Get symbolic link working
        //symlink($tocreate . '/', $wordpress_home_dir . 'presentations/');

        return $presentations_dir;
    }

    /**
     * Validate the $string given to make sure it's a good time (HH:MM), in $increments minute increments
     * @param string $string
     * @return string
     */
    public function mps_validate_agenda_time($string)
    {

        $increments = mps_get_option('meeting_increments', 15);
        // Explode into an array
        $boom = explode(':', $string);

        // Are the minutes allowed?
        if (( $boom[1] ) && ( $boom[1] % $increments == 0 )) {
            return $string;
        } else {
            return $boom[0] . ':' . '00';
        }
    }


    /**
     * Clean the rooms posted in the form to make sure all the entries are clean, skip any entries which are missing long or short room name
     * @return array
     */
    public function mps_tidy_rooms()
    {

        $clean_rooms = [];
        $dirty_rooms = $_POST['mps_rooms'];
        foreach ($dirty_rooms as $id => $room) {
            if ($room['short'] != '' && $room['long'] != '') {
                // Remove illegal chars from short name
                $short = sanitize_html_class(str_replace(' ', '_', strtolower($room['short'])));

                $clean_rooms[$short]['short'] = $short;
                $clean_rooms[$short]['long'] = $room['long'];
                $clean_rooms[$short]['colour'] = $room['colour'];
                $clean_rooms[$short]['text_colour'] = $room['text_colour'];
                $clean_rooms[$short]['in_legend'] = isset($room['in_legend']) ? '1' : '0';
            }
        }
        return $clean_rooms;
    }

    public function mps_format_allowed_file_types()
    {

        $filetypes = [];
        $filetypes_string = strtolower($_POST['mps_allowed_file_types']);

        $boom = explode(', ', $filetypes_string);

        foreach ($boom as $filetype) {
            if (! in_array($filetype, $filetypes)) {
                if (trim($filetype != '')) {
                    $filetypes[] = $filetype;
                }
            }
        }

        return $filetypes;
    }


    /**
     * Print out the selected timezone, and calculate offsets to show back to the user in the dropdown list
     * @return void
     */
    public function mps_meeting_timezone_setting_callback()
    {

        // Get a list of all possible timezones (Could use ::EUROPE to get from Europe)
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        // Fetch the timezone from storage
        $current_timezone = mps_get_option('meeting_timezone', '');

        echo '<select name="mps_meeting_timezone">';
        foreach ($timezones as $timezone) {
            // Work out the offset
            $tz = new DateTimeZone($timezone);
            $offset = $tz->getOffset(new DateTime);
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_string = 'UTC' . $offset_prefix . gmdate('G', abs($offset));

            if ($timezone == $current_timezone) {
                echo '<option selected="selected" value="' . $timezone . '">' . $timezone . ' (' . $offset_string . ' )</option>' . PHP_EOL;
            } else {
                echo '<option value="' . $timezone . '">' . $timezone . ' (' . $offset_string . ' )</option>' . PHP_EOL;
            }
        }
        echo '</select>';

        if ($current_timezone != '') {
            // Echo the short version of the current timezone
            $dateTime = new DateTime();
            $dateTime->setTimeZone(new DateTimeZone($current_timezone));
            echo '<input id="mps_meeting_timezone_short" type="text" readonly value="' . $dateTime->format('T') . '"/>';
        }
    }

    /**
     * Print out the main settings page
     * @return void
     */
    public function mps_settings_page()
    {

        echo '<div class="wrap">';
        echo '<h1>Meeting Support</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields($this->plugin_name);
        do_settings_sections($this->plugin_name);
        submit_button();
        echo '</form>';

        // Show all MPS options in a nice table, if toggled
        if (mps_get_option('config_debug', false)) {
            $all_options = wp_load_alloptions();
            echo '<hr>';
            echo '<table class="form-table">';
            echo '<tbody>';
            foreach ($all_options as $name => $value) {
                if (substr($name, 0, 4) == 'mps_') {
                    echo '<tr>';
                    echo '<th scope="row">' . $name . '</th>';
                    // The $value might be serialised, let's check and make it prettier
                    if (is_serialized($value)) {
                        $value = unserialize($value);
                        $value = '<pre>' . print_r($value, true) . '</pre>';
                    }
                    echo '<td>' . $value . '</td>';
                    echo '</tr>';
                }
            }
            echo '</tbody>';
            echo '</table>';
        }
        echo '</div>';
    }



    private function mps_get_user_data($uuid)
    {

        global $wpdb;

        $user = $wpdb->get_row(
            "
            SELECT * FROM `" . $wpdb->base_prefix . "ms_users`
            WHERE `uuid` = '" . $uuid . "'
            "
        );

        return $user;
    }

    private function mps_get_all_users()
    {

        global $wpdb;

        $users = $wpdb->get_results(
            "
            SELECT uuid, name, email, last_login, is_active FROM `" . $wpdb->base_prefix . "ms_users`
            "
        );

        return $users;
    }

    private function mps_get_all_pc_users()
    {

        global $wpdb;

        $users = $wpdb->get_results(
            "
            SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_users`
            "
        );

        return $users;
    }


    public function mps_get_all_sponsor_sections()
    {

        global $wpdb;

        $sections = $wpdb->get_results(
            "
            SELECT * FROM `" . $wpdb->prefix . "ms_sponsor_sections`
            "
        );

        return $sections;
    }

    public function mps_get_section_sponsors($section_id)
    {

        $section_id = (int) $section_id;

        global $wpdb;

        $sponsors = $wpdb->get_results(
            "
            SELECT * FROM " . $wpdb->prefix . "ms_sponsors WHERE section_id = " . $section_id . " ORDER BY sort_order ASC
            "
        );

        return $sponsors;
    }

    public function mps_agenda_page()
    {
        require_once('views/agenda.php');
    }

    public function mps_users_page()
    {
        require_once('views/users.php');
    }

    public function mps_pc_users_page()
    {
        require_once('views/pc_users.php');
    }

    public function mps_sponsors_page()
    {
        require_once('views/sponsors.php');
    }

    public function mps_slots_page()
    {
        require_once('views/slots.php');
    }

    public function mps_presentations_page()
    {
        require_once('views/presentations.php');
    }

    public function mps_presentation_ratings_page()
    {
        require_once('views/presentation_ratings.php');
    }

    public function mps_videos_page()
    {
        require_once('views/videos.php');
    }

    public function mps_pc_elections_page()
    {
        require_once('views/pc_elections.php');
    }

    public function mps_speakers_page()
    {
        require_once('views/speakers.php');
    }

    public function mps_get_session_callback()
    {
        $session_id = $_POST['session_id'];
        echo json_encode(stripslashes_deep(ms_get_session_data($session_id)));
        exit;
    }

    public function mps_get_user_callback()
    {
        $user_id = $_POST['user_id'];
        echo json_encode(stripslashes_deep($this->mps_get_user_data($user_id)));
        exit;
    }

    public function ms_delete_pc_user_callback()
    {
        $uuid = $_POST['uuid'];
        global $wpdb;
        $wpdb->delete($wpdb->base_prefix . 'ms_pc_users', array( 'uuid' => $uuid ));
        echo 'Deleted';
        exit;
    }

    public function get_user_info($uuid)
    {

        $auth_method = $this->auth->auth_method;

        switch ($auth_method) {
            case 'local':
                return (array) $this->mps_get_user_data($uuid);
            break;

            case 'crowd':
                return $this->auth->getCrowdUserByUUID($uuid);
            break;
        }
    }

    public function ms_get_chat_log_callback()
    {
        $session_id = (int) $_POST['session_id'];
        $home_path = get_home_path();
        $chat_log_dir = $home_path . 'archive/chat/';
        $file = file_get_contents($chat_log_dir . $session_id . '.log');
        if ($file) {
            echo json_encode($file);
        } else {
            echo json_encode('');
        }
        exit;
    }

    public function ms_get_steno_log_callback()
    {
        $session_id = (int) $_POST['session_id'];
        $home_path = get_home_path();
        $steno_log_dir = $home_path . 'archive/steno/';
        $file = file_get_contents($steno_log_dir . $session_id . '.txt');
        if ($file) {
            echo json_encode($file);
        } else {
            echo json_encode('');
        }
        exit;
    }

    public function ms_get_video_callback()
    {
        $video_id = (int) $_POST['video_id'];
        $video = mps_get_video($this->auth->auth_method, $video_id);
        echo json_encode($video);
        exit;
    }

    public function ms_get_speaker_callback()
    {
        $speaker_id = (int) $_POST['speaker_id'];
        $speaker = mps_get_speaker($speaker_id);
        wp_send_json($speaker);
    }

    public function mps_edit_chat_log_callback()
    {
        $session_id = (int) $_POST['session_id'];
        $home_path = get_home_path();
        $chat_log_dir = $home_path . 'archive/chat/';
        if (! file_exists($chat_log_dir)) {
            // We need to create the directory
            mkdir($chat_log_dir, 0777, true);
        }
        $file = $chat_log_dir . $session_id . '.log';
        $chat_log = removeslashes($_POST['chat_content']);
        if (trim($chat_log) == '') {
            // Chat log is empty, delete the log file
            unlink($file);
        } else {
            file_put_contents($file, $chat_log);
        }
        wp_safe_redirect(
            add_query_arg(
                array(
                    'updated' => 'true'
                ),
                wp_get_referer()
            )
        );
        exit;
    }

    public function mps_edit_steno_log_callback()
    {
        $session_id = (int) $_POST['session_id'];
        $home_path = get_home_path();
        $steno_log_dir = $home_path . 'archive/steno/';
        if (! file_exists($steno_log_dir)) {
            // We need to create the directory
            mkdir($steno_log_dir, 0777, true);
        }
        $file = $steno_log_dir . $session_id . '.txt';
        $steno_log = removeslashes($_POST['steno_content']);
        if (trim($steno_log) == '') {
            // steno log is empty, delete the log file
            unlink($file);
        } else {
            file_put_contents($file, $steno_log);
        }
        wp_safe_redirect(
            add_query_arg(
                array(
                    'updated' => 'true'
                ),
                wp_get_referer()
            )
        );
        exit;
    }

    public function mps_edit_pc_candidate_callback()
    {
        $candidate_id = (int) isset($_POST['candidate_id']) ? $_POST['candidate_id'] : 0;
        $candidate = [];
        $candidate['name'] = sanitize_text_field($_POST['candidate_name']);
        global $wpdb;
        if ($candidate_id > 0) {
            // Editing an existing candidate
        } else {
            // Adding a new candidate
            $wpdb->insert($wpdb->prefix . 'ms_pc_candidates', $candidate);
        }
        wp_safe_redirect(
            add_query_arg(
                array(
                    'updated' => 'true'
                ),
                wp_get_referer()
            )
        );
        exit;
    }

    public function mps_edit_speaker_callback()
    {
        check_admin_referer('mps_edit_speaker');

        $speaker_id = (int) $_POST['speaker_id'];
        $speaker = [
            'name' => sanitize_text_field($_POST['speaker_name']),
            'uuid' => sanitize_title($_POST['speaker_uuid']),
            'slug' => sanitize_title($_POST['speaker_slug']),
            'tags' => sanitize_text_field($_POST['speaker_tags']),
            'allowed' => isset($_POST['speaker_allowed']) ? 1 : 0
        ];

        // Post processing of tags
        if ($speaker['tags'] == '') {
          // No tags were set
            $speaker['tags'] = json_encode([]);
        } else {
            // At least 1 tag was given
            $tags = explode(',', $speaker['tags']);
            // Trim whitespace from beginning and end of words
            $tags = array_map('trim', $tags);
            $speaker['tags'] = json_encode($tags);
        }

        // Check for conflicts
        $this->mps_check_speaker_conflicts($speaker_id, $speaker);

        global $wpdb;
        if ($speaker_id > 0) {
            // Editing an existing Speaker
            $wpdb->update(
                $wpdb->prefix . 'ms_speakers',
                $speaker,
                ['id' => $speaker_id]
            );
        } else {
            // Adding a new Speaker
            $speaker['bio_texts'] = json_encode([]);
            $speaker['bio_texts_draft'] = json_encode([]);
            $speaker['slug'] = mps_get_unique_speaker_slug($speaker['slug']);
            $wpdb->insert(
                $wpdb->prefix . 'ms_speakers',
                $speaker
            );
        }
        wp_safe_redirect(
            add_query_arg(
                array(
                    'updated' => 'true'
                ),
                wp_get_referer()
            )
        );
        exit;
    }

    public function mps_check_speaker_conflicts($speaker_id, $new_speaker)
    {

        $speakers = mps_get_all_speakers();

        foreach ($speakers as $speaker) {
            // Skip if we're comparing the same speaker
            if ($speaker->id == $speaker_id) {
                continue;
            }

            $error_message = '';

            // Checks
            // if ($speaker->slug == $new_speaker['slug']) {
            //     $error_message = 'A speaker with this slug already exists';
            // }
            if ($speaker->uuid == $new_speaker['uuid']) {
                $error_message = 'A speaker with this UUID already exists';
            }

            // Did we catch an error
            if ($error_message != '') {
                wp_safe_redirect(
                    add_query_arg(
                        array(
                            'error' => 'true',
                            'message' => urlencode($error_message),
                        ),
                        wp_get_referer()
                    )
                );
                exit;
            }
        }
    }

    public function mps_edit_speaker_bio_callback()
    {

        check_admin_referer('mps_edit_speaker_bio');

        $speaker_id = (int) $_POST['speaker_id'];
        $bio_language = sanitize_title($_POST['bio_language']);
        $bio = stripslashes($_POST['speaker_bio']);

        if ($speaker_id > 0) {
            // Get the speaker we're editing
            $speaker = mps_get_speaker($speaker_id);

            if (trim($bio) == '') {
                unset($speaker->bio_texts[$bio_language]);
            } else {
                $speaker->bio_texts[$bio_language] = $bio;
            }

            // Remove that language from the drafts
            unset($speaker->bio_texts_draft[$bio_language]);

            $speaker->bio_texts = json_encode($speaker->bio_texts);
            $speaker->bio_texts_draft = json_encode($speaker->bio_texts_draft);

            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'ms_speakers',
                [
                    'bio_texts' => $speaker->bio_texts,
                    'bio_texts_draft' => $speaker->bio_texts_draft
                ],
                ['id' => $speaker->id]
            );
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'updated' => 'true'
                ),
                wp_get_referer()
            )
        );
        exit;
    }

    public function mps_edit_video_callback()
    {
        $video_id = (int) $_POST['video_id'];

        $rename_file = false;

        $incomplete_videos = mps_get_option('incomplete_videos', []);

        if (isset($_POST['delete-video']) && $video_id > 0) {
            mps_delete_video($this->auth->auth_method, $video_id);
            wp_safe_redirect(
                add_query_arg(
                    [
                        'updated' => 'true'
                    ],
                    wp_get_referer()
                )
            );
            exit;
        }

        if (isset($_POST['rename_file']) && $video_id > 0) {
            // We need to rename the file, get the old video row to work out what we are renaming from.
            $old_video = mps_get_video($this->auth->auth_method, $video_id);
            $rename_file = true;
        }

        if (isset($_POST['incomplete_video'])) {
            $video_is_incomplete = true;
        } else {
            $video_is_incomplete = false;
        }

        // Flag the video as (in)complete
        if ($video_is_incomplete) {
            if (! in_array($video_id, $incomplete_videos)) {
                $incomplete_videos[] = $video_id;
                mps_update_option('incomplete_videos', $incomplete_videos);
            }
        } else {
            if (in_array($video_id, $incomplete_videos)) {
                $incomplete_videos= array_diff($incomplete_videos, [$video_id]);
                mps_update_option('incomplete_videos', $incomplete_videos);
            }
        }

        // Build the $video array for the database
        if ($this->auth->auth_method == 'crowd') {
            $video = [];
            $video['created'] = sanitize_text_field($_POST['created']);
            $video['presenter_name'] = sanitize_text_field($_POST['presenter_name']);
            $video['presentation_title'] = sanitize_text_field($_POST['presentation_title']);
            $video['session_id'] = (int) $_POST['presentation_session'];
            $video['presentation_id'] = (int) $_POST['presentation_slot'];
            $video['room'] = sanitize_text_field($_POST['room']);
            $video['status'] = sanitize_text_field($_POST['status']);
        } else {
            $video = [];
            $video['session_id'] = (int) $_POST['presentation_session'];
            $video['slot_id'] = (int) $_POST['presentation_slot'];
            $video['video_url'] = $_POST['video_url'];
            $video['locale'] = mps_get_current_locale();
        }

        mps_update_video($this->auth->auth_method, $video_id, $video);

        if ($rename_file) {
            $new_video = mps_get_video($this->auth->auth_method, $video_id);
            $archives_dir = get_home_path() . 'archive/video/';
            // Rename FLV
            rename($archives_dir . $old_video->filename, $archives_dir . $new_video->filename);
            // Rename MP4
            rename($archives_dir . str_replace('.flv', '.mp4', $old_video->filename), $archives_dir . str_replace('.flv', '.mp4', $new_video->filename));
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'updated' => 'true'
                ],
                wp_get_referer()
            )
        );
        exit;
    }
    /**
     * Show all parents, regardless of post status.
     * @param   array  $args  Original get_pages() $args.
     * @return  array  $args  Args set to also include posts with pending, draft, and private status.
     */
    public function ms_slug_show_all_parents($args)
    {
        $args['post_status'] = array( 'publish', 'pending', 'draft', 'private' );
        return $args;
    }
}
