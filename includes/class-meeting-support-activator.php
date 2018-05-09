<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.ripe.net
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Meeting_Support
 * @subpackage Meeting_Support/includes
 * @author     Oliver Payne <opayne@ripe.net>
 */
class Meeting_Support_Activator
{

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.0.0
   */
    public static function activate()
    {


        // Define default configs
        add_option('mps_rooms', [
            ['short' => 'main', 'long' => 'Main Room', 'colour' => '#FFFFFF', 'text_colour' => '#000000'],
            ['short' => 'side', 'long' => 'Side Room', 'colour' => '#FFFFFF', 'text_colour' => '#000000'],
            ['short' => 'tricolour', 'long' => 'Multiple Tutorials', 'colour' => '#FFFFFF', 'text_colour' => '#000000'],
            ['short' => 'gm', 'long' => 'General Meeting', 'colour' => '#FFFFFF', 'text_colour' => '#000000'],
            ['short' => 'terminal', 'long' => 'Other Room', 'colour' => '#FFFFFF', 'text_colour' => '#000000'],
        ]);

        // Create database tables
        self::createSessionsTable();
        self::createUsersTable();
        self::createPCUsersTable();
        self::createPCSubmissionsTable();
        self::createPCSubmissionRatingsTable();
        self::createPresentationsTable();
        self::createPresentationRatingsTable();
        self::createSlotsTable();
        self::createPCVotesTable();
        self::createPCCandidatesTable();
        self::createSponsorSectionsTable();
        self::createSponsorsTable();
        self::createVideosTable();

        mps_update_option('db_version', '1.0.0');
    }

    private static function createSessionsTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_sessions';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
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
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createUsersTable()
    {
        global $wpdb;
        $tableName = $wpdb->base_prefix . 'ms_users';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            uuid varchar(36) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            password varchar(255) NOT NULL,
            last_login datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            is_active tinyint(1) DEFAULT 0 NOT NULL,
            UNIQUE KEY uuid (uuid(36)),
            UNIQUE KEY uq_email (email),
            PRIMARY KEY (uuid)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createPCUsersTable()
    {
        global $wpdb;
        $tableName = $wpdb->base_prefix . 'ms_pc_users';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uuid varchar(40) NOT NULL,
            access_level tinyint NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createPCSubmissionsTable()
    {
        global $wpdb;
        $tableName = $wpdb->base_prefix . 'ms_pc_submissions';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
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
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createPCSubmissionRatingsTable()
    {
        global $wpdb;
        $tableName = $wpdb->base_prefix . 'ms_pc_submission_ratings';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uuid varchar(40) NOT NULL,
            submission_id int NOT NULL,
            rating_content tinyint NOT NULL,
            rating_presenter tinyint NOT NULL,
            rating_comment text NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createPresentationsTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_presentations';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title tinytext NOT NULL,
            author_name tinytext NOT NULL,
            author_affiliation tinytext NOT NULL,
            author_email tinytext NOT NULL,
            author_uuid varchar(40) NOT NULL,
            custom_override text NOT NULL,
            filename tinytext NOT NULL,
            submission_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_edited timestamp NOT NULL,
            session_id int NOT NULL,
            slot_id int NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createPresentationRatingsTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_presentation_ratings';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id int NOT NULL AUTO_INCREMENT,
            uuid varchar(40) NOT NULL,
            slot_id int NOT NULL,
            rating_content int NOT NULL,
            rating_presenter int NOT NULL,
            rating_comment text NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createSlotsTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_slots';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title text NOT NULL,
            content text NOT NULL,
            session_id mediumint(9) NOT NULL,
            ordering int NOT NULL,
            parent_id mediumint(2) NOT NULL,
            ratable tinyint(1) DEFAULT 1 NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createPCVotesTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_pc_votes';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uuid text NOT NULL,
            candidate text NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createPCCandidatesTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_pc_candidates';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name text NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createSponsorSectionsTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_sponsor_sections';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name text NOT NULL,
            text_colour text NOT NULL,
            is_grayscale tinyint(1) NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createSponsorsTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_sponsors';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            section_id int NOT NULL,
            name text NOT NULL,
            image_url text NOT NULL,
            link_url text NOT NULL,
            sort_order int NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function createVideosTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_videos';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id int NOT NULL,
            slot_id int NOT NULL,
            video_url text NOT NULL,
            locale text NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function createSpeakersTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ms_speakers';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name text NOT NULL,
            uuid text NOT NULL,
            slug text NOT NULL,
            bio_texts text  NOT NULL,
            bio_texts_draft text  NOT NULL,
            allowed tinyint(1) NOT NULL,
            UNIQUE KEY id (id)
        ) DEFAULT CHARSET=utf8;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Example data
        $bio_texts = json_encode(
            [
                'en' => 'Oliver is a Web Developer at RIPE NCC',
                'fr' => 'Oliver est un développeur web chez RIPE NCC',
                'ru' => 'Оливер - веб-разработчик в RIPE NCC'
            ]
        );

        $bio_texts_draft = json_encode(
            [
                'en' => 'This is the updated EN text for Oliver\'s Speaker Bio'
            ]
        );

        $wpdb->insert(
            $wpdb->prefix . 'ms_speakers',
            [
                "name" => "Oliver Payne",
                "uuid" => "80a4045c-6dc9-4902-a260-f1cfd9d1efcb",
                "slug" => "oliver-payne",
                "bio_texts" => $bio_texts,
                "bio_texts_draft" => $bio_texts_draft
            ]
        );
    }
}
