<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.ripe.net
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Meeting_Support
 * @subpackage Meeting_Support/includes
 * @author     Oliver Payne <opayne@ripe.net>
 */
class Meeting_Support
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Meeting_Support_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        // We always want a session
        if (! isset($_SESSION)) {
            session_start();
        }
        $this->plugin_name = 'meeting-support';
        $this->version = $this->get_git_version();

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }


    private static function get_git_version()
    {
        exec('cd ' . __DIR__ . ' && git rev-parse --short HEAD', $short_hash);
        return $short_hash[0];
    }


    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Meeting_Support_Loader. Orchestrates the hooks of the plugin.
     * - Meeting_Support_i18n. Defines internationalization functionality.
     * - Meeting_Support_Admin. Defines all hooks for the admin area.
     * - Meeting_Support_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The file which loads all 3rd party dependencies that are autoloaded
         * and generated from composer
         */

        // Try to auto composer install
        if (! file_exists(plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php')) {
            $plugin_dir = plugin_dir_path(dirname(__FILE__));
            shell_exec("cd $plugin_dir && php composer.phar install --no-interaction --prefer-dist --optimize-autoloader");
        }

        require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';

        /**
         * The file with global helper functions, used across multiple classes.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/globals.inc.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-meeting-support-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-meeting-support-i18n.php';

        /**
         * The class that handles all the authentication functionality.
         *
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-meeting-support-authentication.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-meeting-support-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-meeting-support-public.php';

        $this->loader = new Meeting_Support_Loader();

        $this->auth = Meeting_Support_Auth::getInstance();

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-meeting-support-shortcodes.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Meeting_Support_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Meeting_Support_i18n();

        $plugin_i18n->set_domain($this->get_plugin_name());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Meeting_Support_Admin($this->get_plugin_name(), $this->get_version(), $this->auth);

        // Run DB Migrations, if necessary

        // Versions should always be strings, PHP can compare them pretty well
        $plugin_version = $this->get_version();
        $db_version = mps_get_option('db_version', '0.0');

        // Does some sort of migration need to take place?
        $plugin_admin->run_migrations($db_version);


        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Add Meeting Support Admin panel
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_mps_main_menu');

        // Add Meeting Support (and subsections) to the top admin bar, if it's been toggled
        if (mps_get_option('admin_bar', false)) {
            $this->loader->add_action('admin_bar_menu', $plugin_admin, 'admin_plugin_top_bar', 999);
        }

        // Hook into the post handler
        $this->loader->add_action('admin_post_mps_edit_session', $plugin_admin, 'mps_edit_session_callback');
        $this->loader->add_action('admin_post_mps_edit_user', $plugin_admin, 'mps_edit_user_callback');
        $this->loader->add_action('admin_post_mps_reset_password_user', $plugin_admin, 'mps_reset_password_user_callback');
        $this->loader->add_action('admin_post_mps_add_pc_user', $plugin_admin, 'mps_add_pc_user_callback');
        $this->loader->add_action('admin_post_mps_edit_sponsor_section', $plugin_admin, 'mps_edit_sponsor_section_callback');
        $this->loader->add_action('admin_post_mps_edit_sponsor', $plugin_admin, 'mps_edit_sponsor_callback');
        $this->loader->add_action('admin_post_mps_edit_slot', $plugin_admin, 'mps_edit_slot_callback');
        $this->loader->add_action('admin_post_mps_edit_presentation', $plugin_admin, 'mps_edit_presentation_callback');
        $this->loader->add_action('admin_post_mps_export_presentation_ratings', $plugin_admin, 'mps_export_presentation_ratings_callback');
        $this->loader->add_action('admin_post_mps_edit_day_sponsor', $plugin_admin, 'mps_edit_day_sponsor_callback');
        $this->loader->add_action('admin_post_mps_edit_chat_log', $plugin_admin, 'mps_edit_chat_log_callback');
        $this->loader->add_action('admin_post_mps_edit_steno_log', $plugin_admin, 'mps_edit_steno_log_callback');
        $this->loader->add_action('admin_post_mps_edit_video', $plugin_admin, 'mps_edit_video_callback');
        $this->loader->add_action('admin_post_mps_edit_pc_candidate', $plugin_admin, 'mps_edit_pc_candidate_callback');
        $this->loader->add_action('admin_post_mps_edit_speaker', $plugin_admin, 'mps_edit_speaker_callback');
        $this->loader->add_action('admin_post_mps_edit_speaker_bio', $plugin_admin, 'mps_edit_speaker_bio_callback');



        // Admin AJAX requests
        $this->loader->add_action('wp_ajax_ms_get_session', $plugin_admin, 'mps_get_session_callback');
        $this->loader->add_action('wp_ajax_ms_get_user', $plugin_admin, 'mps_get_user_callback');
        $this->loader->add_action('wp_ajax_ms_delete_pc_user', $plugin_admin, 'ms_delete_pc_user_callback');
        $this->loader->add_action('wp_ajax_ms_update_pc_user', $plugin_admin, 'ms_update_pc_user_callback');
        $this->loader->add_action('wp_ajax_ms_get_sponsor_section', $plugin_admin, 'ms_get_sponsor_section_callback');
        $this->loader->add_action('wp_ajax_ms_move_sponsor_up', $plugin_admin, 'ms_move_sponsor_up_callback');
        $this->loader->add_action('wp_ajax_ms_move_sponsor_down', $plugin_admin, 'ms_move_sponsor_down_callback');
        $this->loader->add_action('wp_ajax_ms_delete_sponsor', $plugin_admin, 'ms_delete_sponsor_callback');
        $this->loader->add_action('wp_ajax_ms_get_sponsor', $plugin_admin, 'ms_get_sponsor_callback');
        $this->loader->add_action('wp_ajax_ms_do_shortcode', $plugin_admin, 'ms_do_shortcode_callback');
        $this->loader->add_action('wp_ajax_ms_get_session_slots', $plugin_admin, 'ms_get_session_slots_callback');
        $this->loader->add_action('wp_ajax_ms_get_slot_info', $plugin_admin, 'ms_get_slot_info_callback');
        $this->loader->add_action('wp_ajax_ms_swap_slots', $plugin_admin, 'ms_swap_slots_callback');
        $this->loader->add_action('wp_ajax_ms_delete_slot', $plugin_admin, 'ms_delete_slot_callback');
        $this->loader->add_action('wp_ajax_ms_get_presentation', $plugin_admin, 'ms_get_presentation_callback');
        $this->loader->add_action('wp_ajax_ms_pick_random_rater', $plugin_admin, 'ms_pick_random_rater_callback');
        $this->loader->add_action('wp_ajax_ms_get_day_sponsor', $plugin_admin, 'ms_get_day_sponsor_callback');
        $this->loader->add_action('wp_ajax_ms_get_chat_log', $plugin_admin, 'ms_get_chat_log_callback');
        $this->loader->add_action('wp_ajax_ms_get_steno_log', $plugin_admin, 'ms_get_steno_log_callback');
        $this->loader->add_action('wp_ajax_ms_get_video', $plugin_admin, 'ms_get_video_callback');
        $this->loader->add_action('wp_ajax_ms_delete_candidate', $plugin_admin, 'ms_delete_candidate_callback');
        $this->loader->add_action('wp_ajax_ms_create_pcss_area', $plugin_admin, 'ms_create_pcss_area_callback');
        $this->loader->add_action('wp_ajax_ms_create_wg_chair_bios', $plugin_admin, 'ms_create_wg_chair_bios_callback');
        $this->loader->add_action('wp_ajax_ms_get_speaker', $plugin_admin, 'ms_get_speaker_callback');
        $this->loader->add_action('wp_ajax_ms_delete_speaker', $plugin_admin, 'ms_delete_speaker_callback');

        $this->loader->add_filter('page_attributes_dropdown_pages_args', $plugin_admin, 'ms_slug_show_all_parents');
        $this->loader->add_filter('quick_edit_dropdown_pages_args', $plugin_admin, 'ms_slug_show_all_parents');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Meeting_Support_Public($this->get_plugin_name(), $this->get_version(), $this->auth);

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');


        $this->loader->add_action('init', $plugin_public, 'mps_custom_routes');
        $this->loader->add_action('query_vars', $plugin_public, 'mps_custom_query_vars');
        $this->loader->add_action('parse_request', $plugin_public, 'mps_custom_requests');

        $this->loader->add_action('wp_footer', $plugin_public, 'mps_public_ajax_url');

        $this->loader->add_action('the_content', $plugin_public, 'mps_add_translation_link');
        $this->loader->add_action('the_content', $plugin_public, 'mps_pcss_navigation');

        // Custom Post Metadata Boxes
        $this->loader->add_action('add_meta_boxes', $plugin_public, 'mps_draft_agenda_flag_meta_box_setup');
        $this->loader->add_action('save_post', $plugin_public, 'mps_draft_agenda_flag_meta_box_save', 10, 2);
        $this->loader->add_action('the_content', $plugin_public, 'mps_draft_agenda_warning_box', 10, 2);


        // Shortcodes executed in widgets
        add_filter('widget_text', 'do_shortcode');
        $this->loader->add_filter('body_class', $plugin_public, 'mps_add_blog_id_body_class');

        // Added query args
        $this->loader->add_filter('query_vars', $plugin_public, 'mps_add_custom_query_var');
        $this->loader->add_filter('rewrite_rules_array', $plugin_public, 'mps_add_rewrite_rules');

        // Custom page title on certain pages
        $this->loader->add_filter('the_title', $plugin_public, 'mps_custom_page_titles');

        // Public admin-post.php requests
        $this->loader->add_action('admin_post_nopriv_mps_user_login', $plugin_public, 'mps_user_login_callback');
        $this->loader->add_action('admin_post_mps_user_login', $plugin_public, 'mps_user_login_callback');

        $this->loader->add_action('admin_post_nopriv_mps_user_register', $plugin_public, 'mps_user_register_callback');
        $this->loader->add_action('admin_post_mps_user_register', $plugin_public, 'mps_user_register_callback');

        $this->loader->add_action('admin_post_nopriv_mps_user_update_profile', $plugin_public, 'mps_user_update_profile_callback');
        $this->loader->add_action('admin_post_mps_user_update_profile', $plugin_public, 'mps_user_update_profile_callback');

        $this->loader->add_action('admin_post_nopriv_mps_pc_submission', $plugin_public, 'mps_pc_submission_callback');
        $this->loader->add_action('admin_post_mps_pc_submission', $plugin_public, 'mps_pc_submission_callback');

        $this->loader->add_action('admin_post_nopriv_mps_contact_form', $plugin_public, 'mps_contact_form_callback');
        $this->loader->add_action('admin_post_mps_contact_form', $plugin_public, 'mps_contact_form_callback');

        $this->loader->add_action('admin_post_nopriv_mps_presentation_upload', $plugin_public, 'mps_presentation_upload_callback');
        $this->loader->add_action('admin_post_mps_presentation_upload', $plugin_public, 'mps_presentation_upload_callback');

        $this->loader->add_action('admin_post_nopriv_mps_presentation_delete', $plugin_public, 'mps_presentation_delete_callback');
        $this->loader->add_action('admin_post_mps_presentation_delete', $plugin_public, 'mps_presentation_delete_callback');

        $this->loader->add_action('admin_post_nopriv_mps_get_agenda_pdf', $plugin_public, 'mps_get_agenda_pdf_callback');
        $this->loader->add_action('admin_post_mps_get_agenda_pdf', $plugin_public, 'mps_get_agenda_pdf_callback');

        $this->loader->add_action('admin_post_nopriv_mps_get_agenda_ics', $plugin_public, 'mps_get_agenda_ics_callback');
        $this->loader->add_action('admin_post_mps_get_agenda_ics', $plugin_public, 'mps_get_agenda_ics_callback');

        $this->loader->add_action('admin_post_nopriv_mps_presentations_json', $plugin_public, 'mps_presentations_json_callback');
        $this->loader->add_action('admin_post_mps_presentations_json', $plugin_public, 'mps_presentations_json_callback');

        $this->loader->add_action('admin_post_nopriv_mps_schedule_json', $plugin_public, 'mps_schedule_json_callback');
        $this->loader->add_action('admin_post_mps_schedule_json', $plugin_public, 'mps_schedule_json_callback');

        $this->loader->add_action('admin_post_nopriv_mps_pc_election_vote', $plugin_public, 'mps_pc_election_vote_callback');
        $this->loader->add_action('admin_post_mps_pc_election_vote', $plugin_public, 'mps_pc_election_vote_callback');

        $this->loader->add_action('admin_post_nopriv_mps_get_pathable_agenda_items', $plugin_public, 'mps_get_pathable_agenda_items_callback');
        $this->loader->add_action('admin_post_mps_get_pathable_agenda_items', $plugin_public, 'mps_get_pathable_agenda_items_callback');

        $this->loader->add_action('admin_post_nopriv_mps_get_agenda_items', $plugin_public, 'mps_get_agenda_items_callback');
        $this->loader->add_action('admin_post_mps_get_agenda_items', $plugin_public, 'mps_get_agenda_items_callback');

        $this->loader->add_action('admin_post_nopriv_mps_update_speaker_bio', $plugin_public, 'mps_update_speaker_bio_callback');
        $this->loader->add_action('admin_post_mps_update_speaker_bio', $plugin_public, 'mps_update_speaker_bio_callback');



        // Public admin-ajax.php requests
        $this->loader->add_action('wp_ajax_get_submission_info', $plugin_public, 'mps_get_submission_info_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_submission_info', $plugin_public, 'mps_get_submission_info_callback');

        $this->loader->add_action('wp_ajax_set_sub_final_decision', $plugin_public, 'mps_set_sub_final_decision_callback');
        $this->loader->add_action('wp_ajax_nopriv_set_sub_final_decision', $plugin_public, 'mps_set_sub_final_decision_callback');

        $this->loader->add_action('wp_ajax_set_my_submission_rating', $plugin_public, 'mps_set_my_submission_rating_callback');
        $this->loader->add_action('wp_ajax_nopriv_set_my_submission_rating', $plugin_public, 'mps_set_my_submission_rating_callback');

        $this->loader->add_action('wp_ajax_get_my_submission_rating', $plugin_public, 'mps_get_my_submission_rating_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_my_submission_rating', $plugin_public, 'mps_get_my_submission_rating_callback');

        $this->loader->add_action('wp_ajax_get_submission_ratings_html', $plugin_public, 'mps_get_submission_ratings_html_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_submission_ratings_html', $plugin_public, 'mps_get_submission_ratings_html_callback');

        $this->loader->add_action('wp_ajax_delete_submission_rating', $plugin_public, 'mps_delete_submission_rating_callback');
        $this->loader->add_action('wp_ajax_nopriv_delete_submission_rating', $plugin_public, 'mps_delete_submission_rating_callback');

        $this->loader->add_action('wp_ajax_get_mass_mail_info', $plugin_public, 'mps_get_mass_mail_info_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_mass_mail_info', $plugin_public, 'mps_get_mass_mail_info_callback');

        $this->loader->add_action('wp_ajax_get_mass_mail_template', $plugin_public, 'mps_get_mass_mail_template_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_mass_mail_template', $plugin_public, 'mps_get_mass_mail_template_callback');

        $this->loader->add_action('wp_ajax_send_mass_mail', $plugin_public, 'mps_send_mass_mail_callback');
        $this->loader->add_action('wp_ajax_nopriv_send_mass_mail', $plugin_public, 'mps_send_mass_mail_callback');

        $this->loader->add_action('wp_ajax_check_rate_capabilities', $plugin_public, 'mps_check_rate_capabilities_callback');
        $this->loader->add_action('wp_ajax_nopriv_check_rate_capabilities', $plugin_public, 'mps_check_rate_capabilities_callback');

        $this->loader->add_action('wp_ajax_get_my_slot_rating', $plugin_public, 'mps_get_my_slot_rating_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_my_slot_rating', $plugin_public, 'mps_get_my_slot_rating_callback');

        $this->loader->add_action('wp_ajax_set_my_slot_rating', $plugin_public, 'mps_set_my_slot_rating_callback');
        $this->loader->add_action('wp_ajax_nopriv_set_my_slot_rating', $plugin_public, 'mps_set_my_slot_rating_callback');

        $this->loader->add_action('wp_ajax_ms_get_session_slots', $plugin_public, 'ms_get_session_slots_callback');
        $this->loader->add_action('wp_ajax_nopriv_ms_get_session_slots', $plugin_public, 'ms_get_session_slots_callback');

        $this->loader->add_action('wp_ajax_get_sub_archive_html', $plugin_public, 'mps_get_sub_archive_html_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_sub_archive_html', $plugin_public, 'mps_get_sub_archive_html_callback');

        $this->loader->add_action('wp_ajax_set_submission_tags', $plugin_public, 'mps_set_submission_tags_callback');
        $this->loader->add_action('wp_ajax_nopriv_set_submission_tags', $plugin_public, 'mps_set_submission_tags_callback');

        $this->loader->add_action('wp_ajax_get_submission_tags', $plugin_public, 'mps_get_submission_tags_callback');
        $this->loader->add_action('wp_ajax_nopriv_get_submission_tags', $plugin_public, 'mps_get_submission_tags_callback');

        $this->loader->add_action('wp_ajax_delete_presentation', $plugin_public, 'mps_ajax_delete_presentation_callback');
        $this->loader->add_action('wp_ajax_nopriv_delete_presentation', $plugin_public, 'mps_ajax_delete_presentation_callback');

        $this->loader->add_action('wp_ajax_upload_presentation', $plugin_public, 'mps_ajax_upload_presentation_callback');
        $this->loader->add_action('wp_ajax_nopriv_upload_presentation', $plugin_public, 'mps_ajax_upload_presentation_callback');
    }



    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Meeting_Support_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
