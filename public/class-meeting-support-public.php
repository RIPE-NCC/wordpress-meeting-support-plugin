<?php

/**
 *
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.ripe.net
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/public
 * @author     Oliver Payne <opayne@ripe.net>
 */


use Carbon\Carbon;
use cogpowered\FineDiff\Diff;

class Meeting_Support_Public
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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version, $auth)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->auth = $auth;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style(
            $this->plugin_name . '-bootstrap-css',
            plugin_dir_url(__FILE__) . 'css/bootstrap-iso.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-toastr',
            plugin_dir_url(__FILE__) . 'css/toastr.min.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-fontawesome',
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
            $this->plugin_name . '-flowplayer-css',
            plugin_dir_url(__FILE__) . 'flowplayer-6.0.5/skin/minimalist.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-flowplayer-quality-selector-css',
            plugin_dir_url(__FILE__) . 'css/flowplayer.quality-selector.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/meeting-support-public.css',
            [],
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script(
            $this->plugin_name . '-datatables-js',
            plugin_dir_url(__FILE__) . 'js/datatables.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-sparkline-js',
            plugin_dir_url(__FILE__) . 'js/jquery.sparkline.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-bootstrap-js',
            plugin_dir_url(__FILE__) . 'js/bootstrap.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-flowplayer-js',
            plugin_dir_url(__FILE__) . 'flowplayer-6.0.5/flowplayer.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-toastr-js',
            plugin_dir_url(__FILE__) . 'js/toastr.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-select2-js',
            plugin_dir_url(__FILE__) . 'js/select2.full.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-flowplayer-js-hlsjs',
            plugin_dir_url(__FILE__) . 'js/flowplayer.hlsjs.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/meeting-support-public.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }

    public function mps_user_login_callback()
    {

        // Check that the request came from a valid page
        $nonce = $_REQUEST['_wpnonce'];

        if (! wp_verify_nonce($nonce, 'mps_user_login')) {
            die('Security check');
        }

        // We should start a session if it isn't already running
        if (! session_id()) {
            session_start();
        }

        // Make sure we were actually given a username and password
        $email = sanitize_email($_REQUEST['email']);
        $password = $_REQUEST['password'];

        if (! $email || ! $password) {
            mps_flash('mps_login', __('Email or password missing', 'meeting-support'), 'danger');
            wp_safe_redirect(home_url('/login/'));
            exit;
        }

        // Let's see if there's a user with that email in the database
        $user = $this->auth->getLocalUserByEmail($email);
        if ($user && password_verify($password, $user['password']) && $user['is_active']) {
            $_SESSION['user'] = (array) $user;
            mps_log('User Login (' . $user['email'] . ')');

            // We don't really want the hashed password in the session
            unset($_SESSION['user']->password);

            // Update the last_login time
            $this->auth->updateLastLogin($user['uuid']);

            if ($_SESSION['return_to']) {
                wp_safe_redirect($_SESSION['return_to']);
                exit;
            } else {
                wp_safe_redirect(home_url());
                exit;
            }
        }

        if ($user && (! $user['is_active'] )) {
            mps_log('User Attempted Login FAILED (Not active) (' . $user['email'] . ')');
            mps_flash('mps_login', __('Account not active', 'meeting-support'), 'danger');
            wp_safe_redirect(home_url('/login/'));
            exit;
        }

        mps_flash('mps_login', __('Invalid login credentials', 'meeting-support'), 'danger');
        wp_safe_redirect(home_url('/login/'));
        exit;
    }

    public function mps_pc_election_vote_callback()
    {
        // Don't do anything if the user isn't logged in
        if (! $this->auth->user) {
            mps_flash('mps_pc_election_vote', __('You must be logged in to vote', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        $max_votes = mps_get_option('max_pc_votes', 2);

        $candidates = $_POST['candidates'];

        // Don't do anything if the user has voted for more candidates than allowed
        if (count($_POST['candidates']) > $max_votes) {
            mps_flash('mps_pc_election_vote', __('You can only vote for a maximum of ' . $max_votes . ' candidates', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        $uuid = $this->auth->user['uuid'];
        mps_clear_pc_votes_for_uuid($uuid);
        foreach ($candidates as $candidate) {
            mps_add_pc_vote_for_uuid($uuid, $candidate);
            mps_log($uuid.' ['.$_SERVER['REMOTE_ADDR'].'] voted for ' . $candidate);
        }


        mps_flash('mps_pc_election_vote', 'Thank you for voting', 'success', true);
        wp_safe_redirect(wp_get_referer());
        exit;
    }

    public function mps_check_rate_capabilities_callback()
    {
        /**
         * Check to see if the user is allowed to rate a slot, if they are not logged in then throw an error message
         */
        $auth = $this->auth;
        if ($auth->user) {
            echo json_encode(array('logged_in' => true));
        } else {
            echo json_encode(
                array(
                'logged_in' => false,
                'message' => __('You need to be logged in to rate presentations. Click OK to continue to the login page.', 'meeting-support'),
                'login_url' => $auth->getLoginLink()
                )
            );
        }
            exit;
    }

    public function mps_get_my_slot_rating_callback()
    {
        $slot_id = (int) $_POST['slot_id'];
        $my_uuid = $this->auth->user['uuid'];
        $rating = mps_get_slot_rating_for_uuid($slot_id, $my_uuid);
        echo json_encode($rating);
        exit;
    }


    public function mps_get_sub_archive_html_callback()
    {
        $granularity = new cogpowered\FineDiff\Granularity\Word;
        $pc_config = pc_config();
        $statuses = $pc_config['submission_status'];

        $archive_id = (int) $_POST['archive_id'];
        if (isset($_POST['diff_latest'])) {
            $archive = mps_get_submission($archive_id);
            $previous_archive = mps_get_latest_submission_archive($archive_id);
        } else {
            $archive = mps_get_submission_archive($archive_id);
            $previous_archive = mps_get_previous_submission_archive($archive);
        }

        $upload_dir = wp_upload_dir();
        $submissions_dir = $upload_dir['baseurl'] . '/submissions/';

        $output = '';
        $output .= '<table class="table">';
        $output .= '<thead>';
        $output .= '</thead>';
        $output .= '<tbody>';

        if (! $previous_archive || $previous_archive->submission_title != $archive->submission_title) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(@$previous_archive->submission_title), sanitize_text_field($archive->submission_title));
        } else {
            $section_text = sanitize_text_field($archive->submission_title);
        }
        $output .= '<tr>';
        $output .= '<td><b>Submission Title</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->author_name != $archive->author_name) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(@$previous_archive->author_name), sanitize_text_field($archive->author_name));
        } else {
            $section_text = sanitize_text_field($archive->author_name);
        }
        $output .= '<tr>';
        $output .= '<td><b>Author Name</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->author_affiliation != $archive->author_affiliation) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(@$previous_archive->author_affiliation), sanitize_text_field($archive->author_affiliation));
        } else {
            $section_text = sanitize_text_field($archive->author_affiliation);
        }
        $output .= '<tr>';
        $output .= '<td><b>Author Affiliation</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->submission_status != $archive->submission_status) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(ucfirst(@$statuses[@$previous_archive->submission_status])), sanitize_text_field(ucfirst($statuses[$archive->submission_status])));
        } else {
            $section_text = sanitize_text_field(ucfirst($statuses[$archive->submission_status]));
        }

        $output .= '<tr>';
        $output .= '<td><b>Status</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->submission_type != $archive->submission_type) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(ms_get_submission_type_name(@$previous_archive->submission_type), ms_get_submission_type_name($archive->submission_type));
        } else {
            $section_text = ms_get_submission_type_name($archive->submission_type);
        }
        $output .= '<tr>';
        $output .= '<td><b>Submission Type</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->submission_abstract != $archive->submission_abstract) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(@$previous_archive->submission_abstract), sanitize_text_field($archive->submission_abstract));
        } else {
            $section_text = sanitize_text_field($archive->submission_abstract);
        }
        $output .= '<tr>';
        $output .= '<td><b>Submission Abstract</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->submission_url != $archive->submission_url) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(@$previous_archive->submission_url), sanitize_text_field($archive->submission_url));
        } else {
            $section_text = sanitize_text_field($archive->submission_url);
        }
        $output .= '<tr>';
        $output .= '<td><b>Submission URL</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->final_decision != $archive->final_decision) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(@$previous_archive->final_decision), sanitize_text_field($archive->final_decision));
        } else {
            $section_text = sanitize_text_field($archive->final_decision);
        }
        $output .= '<tr>';
        $output .= '<td><b>Final Decision</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->author_comments != $archive->author_comments) {
            $diff = new Diff($granularity);
            $section_text = $diff->render(sanitize_text_field(@$previous_archive->author_comments), sanitize_text_field($archive->author_comments));
        } else {
            $section_text = sanitize_text_field($archive->author_comments);
        }
        $output .= '<tr>';
        $output .= '<td><b>Author Comments</b></td>';
        $output .= '<td>' . $section_text . '</td>';
        $output .= '</tr>';

        if (! $previous_archive || $previous_archive->filename != $archive->filename) {
            $diff = new Diff($granularity);
            $section_text = '<ins>' . sanitize_text_field($archive->filename) . '<ins>';
        } else {
            $section_text = sanitize_text_field($archive->filename);
        }
        $output .= '<tr>';
        $output .= '<td><b>Submission File</b></td>';
        $output .= '<td><a target="_blank" href="' . $submissions_dir .  $archive->filename . '">' . $section_text . '</a></td>';
        $output .= '</tr>';

        $output .= '</tbody>';
        $output .= '</table>';
        echo $output;
        exit;
    }

    public function get_my_pcss_notifications_callback()
    {
        /**
         * Function to see if there have been any changes in the last x seconds of which the current PCSS user should be aware
         */

        $timeframe = (int) $_POST['poll_interval'];

        if (! pcss_user_can('view_all_submissions')) {
            exit;
        }

        $notifications = [];

        // Have there been any new submissions in the past x seconds?
        $new_submissions = mps_get_new_submissions_since($timeframe);
        if ($new_submissions) {
            $notifications['new_submissions'] = $new_submissions;
        }

        // Have there been any updated submissions in the past x seconds?
        $updated_submissions = mps_get_updated_submissions_since($timeframe);
        if ($updated_submissions) {
            $notifications['updated_submissions'] = $updated_submissions;
        }

        // Have there been any ratings in the past x seconds?
        $new_ratings = mps_get_pc_submission_ratings_since($timeframe);
        if ($new_ratings) {
            $notifications['new_ratings'] = $new_ratings;
        }

        echo json_encode($notifications);
        exit;
    }

    public function mps_presentations_json_callback()
    {
        $sessions = mps_get_all_sessions();

        // Let's set to the meeting timezone
        $reset = date_default_timezone_get();
        $tz_full = mps_get_option('meeting_timezone');
        date_default_timezone_set($tz_full);
        $dateTime = new DateTime();
        $dateTime->setTimeZone(new DateTimeZone($tz_full));
        $tz_short = $dateTime->format('T');

        // Do we want to show lots of details?
        if (isset($_GET['details'])) {
            $details = true;
        } else {
            $details = false;
        }

        if (isset($_GET['intermissions'])) {
            $show_intermissions = true;
        } else {
            $show_intermissions = false;
        }

        $return = [];

        if ($details) {
            // Meeting info
            $return['meeting'] = [];
            $return['meeting']['name'] = mps_get_option('meeting_name');
            $return['meeting']['startdate'] = date("j F Y", strtotime(mps_get_option('meeting_start_date')));
            $return['meeting']['timezone'] = array('abbr' => $tz_short, 'full' => $tz_full);

            // Sessions info
            foreach ($sessions as $session) {
                // Dont display breaks/lunches in the feed
                if ($show_intermissions == false && $session->is_intermission == 1) {
                    continue;
                }

                $start_time = strtotime($session->start_time);
                $end_time =  strtotime($session->end_time);

                $return['sessions'][date('l', $start_time)][] = array(
                    'id' => $session->id,
                    'start_unix' => $start_time,
                    'end_unix' => $end_time,
                    'start' => intval(date('Hi', $start_time)), //can pass in a unix timestamp no problem, again for legacy compatibility
                    'end' => intval(date('Hi', $end_time)),
                    'room' => $session->room,
                    'name' => trim(stripslashes($session->name)),
                    'comment' => '', // legacy key
                    'link' => $session->url,
                );
            }

            // Presentations (actually slots) info
            $return['presentations'] = [];
            foreach ($sessions as $session) {
                $slots = ms_get_session_slots($session->id);
                foreach ($slots as $slot) {
                    // Don't show parent slots
                    $children = ms_get_slot_children($slot->id);
                    if (count($children) > 0) {
                        continue;
                    }
                    $presentations = ms_get_slot_presentations($slot->id);
                    if (empty($presentations)) {
                        $presentername = '';
                    } else {
                        $presentername = $presentations[0]->author_name;
                    }
                    //var_dump($presentations);
                    $return['presentations'][] = array(
                       'id'=> $slot->id,
                       'day'=> date('l', strtotime($session->start_time)),
                       'session'=> stripslashes($session->name),
                       'sessionid'=> $session->id,
                       'presenter'=> $presentername,
                       'title'=> str_replace('?', '', $slot->title),
                       'date'=> '',
                       'file'=> '',
                    );
                }
                unset($slots);
            }
        } else {
            $presentations = mps_get_all_presentations();

            foreach ($presentations as $presentation) {
                //var_dump($presentation);
                $files = json_decode($presentation->filename);
                $session = ms_get_session_data($presentation->session_id);
                foreach ($files as $file) {
                    $return[] = array(
                        'id' => $presentation->id,
                        'day' => date('l', strtotime($session->start_time)),
                        'session' => stripslashes($session->name),
                        'sessionid' => $presentation->session_id,
                        'presenter' => $presentation->author_name,
                        'title' => $presentation->title,
                        'date' => date('Y-m-d', strtotime($presentation->submission_date)),
                        'file' => $file
                    );
                }
            }
        }
        date_default_timezone_set($reset);
        wp_send_json($return);
    }

    private function get_session_presentations($session)
    {
        $result = [];
        $slots = ms_get_session_slots($session->id);

        foreach ($slots as $slot) {
            // Don't show parent slots
            $children = ms_get_slot_children($slot->id);
            if (count($children) > 0) {
                continue;
            }

            $presentations = ms_get_slot_presentations($slot->id);
            if (empty($presentations)) {
                $presentername = '';
            } else {
                $presentername = $presentations[0]->author_name;
            }

            //var_dump($presentations);
            $result[] = [
                'id'=> $slot->id,
                'day'=> date('l', strtotime($session->start_time)),
                'session'=> stripslashes($session->name),
                'sessionid'=> $session->id,
                'presenter'=> $presentername,
                'title'=> str_replace('?', '', $slot->title),
                'date'=> '',
                'file'=> '',
            ];
        }

        return $result;
    }

    public function mps_schedule_json_callback()
    {
        // Let's set to the meeting timezone
        $tz = mps_get_option('meeting_timezone');
        date_default_timezone_set($tz);

        $schedule = [];

        $schedule['meeting'] = [
            'name' => mps_get_option('meeting_name'),
            'timezone' => $tz,
        ];

        $schedule['sessions'] = [];
        $schedule['presentations'] = [];

        $sessions = mps_get_all_sessions();

        foreach ($sessions as $session) {
            // Sessions info, skip intermissions
            if ($session->is_intermission != 1) {
                $schedule['sessions'][] = [
                    'id' => $session->id,
                    'start_unix' => strtotime($session->start_time),
                    'end_unix' => strtotime($session->end_time),
                    'room' => $session->room,
                    'name' => trim(stripslashes($session->name)),
                    'link' => $session->url,
                ];
            }

            // Presentations (actually slots) info
            $presentations = $this->get_session_presentations($session);
            if ($presentations) {
                $schedule['presentations'] = array_merge($schedule['presentations'], $presentations);
            }
        }

        wp_send_json($schedule);
    }

    public function mps_draft_agenda_flag_meta_box_setup()
    {
        add_meta_box(
            'mps-draft-agenda-flag',
            'Draft Agenda',
            array($this, 'mps_draft_agenda_flag_meta_box'),
            'page',
            'side',
            'default'
        );
    }

    public function mps_draft_agenda_warning_box($content)
    {
        /**
         * if the meeting hasn't ended, start pages with a is_draft_agenda metadata flag with a warning
         */

        $meeting_end_date = mps_get_option('meeting_end_date');
        $now = Date('Y-m-d');
        if (strtotime($meeting_end_date) < strtotime($now)) {
            return $content;
        }

        global $post;

        $alert_box = '<div class="boot"><div class="alert alert-warning">' . __('This is a draft agenda: changes are still being made.', 'meeting-support') . '</div></div>';

        if ($post->post_type == 'page') {
            $is_draft = get_post_meta($post->ID, 'is_draft_agenda', true);
            if ($is_draft == 'on') {
                $content = $alert_box . $content;
            }
        }
        return $content;
    }

    public function mps_draft_agenda_flag_meta_box($object, $box)
    {

        wp_nonce_field(basename(__FILE__), 'mps_draft_agenda_flag_nonce');
        $is_draft = get_post_meta($object->ID, 'is_draft_agenda', true);
                echo '<p>';
        echo '<label for="mps_draft_agenda_flag" class="selectit">';
        echo '<input type="checkbox" name="mps_draft_agenda_flag" ' . ($is_draft == 'on' ? 'checked="checked"'  : '') . ' id="mps_draft_agenda_flag"> This is a Draft Agenda</label>';
        echo '</p>';
    }

    public function mps_draft_agenda_flag_meta_box_save($post_id, $post)
    {
        if (!isset($_POST['mps_draft_agenda_flag_nonce']) || !wp_verify_nonce($_POST['mps_draft_agenda_flag_nonce'], basename(__FILE__))) {
            return $post_id;
        }

        $post_type = get_post_type_object($post->post_type);

        if (!current_user_can($post_type->cap->edit_post, $post_id)) {
            return $post_id;
        }

        $new_meta_value = ( isset($_POST['mps_draft_agenda_flag']) ? sanitize_html_class($_POST['mps_draft_agenda_flag']) : '' );

        $meta_key = 'is_draft_agenda';

        $meta_value = get_post_meta($post_id, $meta_key, true);

        if ($new_meta_value && '' == $meta_value) {
            add_post_meta($post_id, $meta_key, $new_meta_value, true);
        } elseif ($new_meta_value && $new_meta_value != $meta_value) {
            update_post_meta($post_id, $meta_key, $new_meta_value);
        } elseif ('' == $new_meta_value && $meta_value) {
            delete_post_meta($post_id, $meta_key, $meta_value);
        }
    }

    public function mps_set_submission_tags_callback()
    {
        $sub_id = (int) $_POST['submission_id'];
        $tags = (array) $_POST['tags'];

        $response = mps_set_submission_tags($sub_id, $tags);

        echo json_encode($tags);
        exit;
    }

    public function mps_get_submission_tags_callback()
    {

        $sub_id = (int) $_POST['submission_id'];
        $tags = mps_get_submission_tags($sub_id);
        echo json_encode($tags);
        exit;
    }

    public function mps_set_my_slot_rating_callback()
    {
        /**
         * update or add a rating for a user to a slot
         */

        global $wpdb;

        if (! $this->auth->user) {
            // User not logged in
            echo json_encode(array('success' => false, 'message' => 'You are not logged in'));
            exit;
        }

        // TODO check if the slot is actually rateable
        $slot_id = (int) $_POST['slot_id'];
        $my_uuid = $this->auth->user['uuid'];

        $rating = mps_get_slot_rating_for_uuid($slot_id, $my_uuid);

        if (isset($_POST['delete_rating']) && $rating) {
            $wpdb->delete($wpdb->prefix . 'ms_presentation_ratings', array('uuid' => $my_uuid, 'slot_id' => $slot_id));
            echo json_encode(array('success' => true, 'button_html' => ms_get_my_presentation_rating_button($slot_id, $this->auth)));
            exit;
        }

        if (isset($_POST['delete_rating']) && ! $rating) {
            echo json_encode(array('success' => true, 'button_html' => ms_get_my_presentation_rating_button($slot_id, $this->auth)));
            exit;
        }

        // Rating validation, thanks Ondřej!
        $rating_content = filter_var($_POST['ratingcontent'], FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0,
                'min_range' => 0,
                'max_range' => 5
            )
        ));

        $rating_presenter = filter_var($_POST['ratingpresenter'], FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0,
                'min_range' => 0,
                'max_range' => 5
            )
        ));

        // Create array suitable for database
        $data = array(
            'uuid' => $my_uuid,
            'slot_id' => $slot_id,
            'rating_content' => $rating_content,
            'rating_presenter' => $rating_presenter,
            'rating_comment' => escape_multiline_text($_POST['ratingcomments'])
        );

        if ($rating) {
            // Update
            $wpdb->update(
                $wpdb->prefix . 'ms_presentation_ratings',
                $data,
                array('uuid' => $my_uuid, 'slot_id' => $slot_id)
            );
        } else {
            // Insert
            $wpdb->insert(
                $wpdb->prefix . 'ms_presentation_ratings',
                $data
            );
        }

        echo json_encode(array('success' => true, 'button_html' => ms_get_my_presentation_rating_button($slot_id, $this->auth)));
        exit;
    }

    public function mps_user_register_callback()
    {

        // Check that the request came from a valid page
        $nonce = $_REQUEST['_wpnonce'];

        if (! wp_verify_nonce($nonce, 'mps_user_register')) {
            die('Security check');
        }

        $name = sanitize_text_field(stripslashes($_POST['name']));
        $email = sanitize_email($_POST['email']);
        $captcha = $_POST['captcha'];

        // Make sure we have what we need
        if (! $name || ! $email || ! $captcha) {
            mps_flash('mps_user_register', __('Missing required fields', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // Does this email address already exist in the database? If so, we shouldn't add it again.
        $user = $this->auth->getLocalUserByEmail($email);
        if ($user) {
            mps_flash('mps_user_register', __('This user already exists', 'meeting-support') . '. <a href="/login/">' . __('Sign in', 'meeting-support') . '</a>', 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // Is the CAPTCHA good?
        if ($_SESSION['phrase'] != $captcha) {
            $_SESSION['old_post'] = $_POST;
            mps_flash('mps_user_register', __('Incorrect Verification Text', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        $this->create_local_user($name, $email);
        mps_flash('mps_user_register', __('User successfully registered; please check your email for login credentials', 'meeting-support'), 'success');
        wp_safe_redirect(wp_get_referer());
        exit;
    }

    public function mps_get_agenda_ics_callback()
    {

        $timezone = mps_get_option('meeting_timezone');
        $meeting_name = mps_get_option('meeting_name');

        $filename = strtolower(sanitize_file_name($meeting_name . '-sessions.ics'));

        $sessions = mps_get_all_sessions();

        $config = array(
            'unique_id' => sanitize_file_name($meeting_name),
            'filename' => $filename,
            'TZID' => $timezone
            );
        $v = new kigkonsult\iCalcreator\vcalendar($config);
        $v->parse();

        $v->setProperty('method', 'PUBLISH');
        $v->setProperty('X-WR-CALNAME', $meeting_name . ' Agenda');
        $v->setConfig("nl", "\r\n");
        $v->setConfig("filename", $filename);
        $v->setConfig("TZID", $timezone);


        foreach ($sessions as $session) {
            // Don't include intermissions in the iCal file
            $vevent = &$v->newComponent('vevent');
            $vevent->setProperty('dtstart', $session->start_time);
            $vevent->setProperty('dtend', $session->end_time);
            // Only set the location if it's not an intermission
            if (! $session->is_intermission) {
                $vevent->setProperty('LOCATION', get_real_room_name($session->room));
            }
            $vevent->setProperty('summary', stripslashes($session->name));
        }

        return $v->returnCalendar();
    }

    public function mps_get_agenda_pdf_callback()
    {

        $options = new Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf\Dompdf($options);

        // Allow self signed and bad ssl certs, in case we're on dev machine
        $context = stream_context_create([
            'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed'=> true
            ]
            ]);
        $dompdf->setHttpContext($context);

        $home_url = home_url();

        $meeting_logo = esc_url($home_url . mps_get_option('meeting_logo_url'));

        // Build the HTML to use in the PDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <link type="text/css" rel="stylesheet" href="' . $home_url . '/wp-content/plugins/meeting-support-plugin/public/css/meeting-support-public.css' . '"/>
            <style>
                html {
                    font-family: Helvetica, Arial, sans-serif;
                }
                td, tr {
                    height: 10px;
                    line-height: 10px;
                    font-size: 12px;

                }
                table {
                    border-width: 1px 1px 1px 1px;
                    border-color: white white white white;
                    border-collapse: collapse;
                }
                div.legenditem {
                    display: inline-block;
                }
            </style>
        </head>
        <body>
            <img width="100%" src="' . $meeting_logo . '"/>
            <br>
            <br>
            ' . do_shortcode('[session_table showsponsors="true"]') . '
            <br>
            ' . do_shortcode('[session_table_legend]') . '
        </body>
        </html>
        ';

        // Dynamic name for given pdf
        $meeting_name = sanitize_file_name(mps_get_option('meeting_name'));
        $agenda_filename = $meeting_name . '-agenda';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();


        $dompdf->stream($agenda_filename, array('Attachment' => 1));
    }



    public function ms_get_session_slots_callback()
    {

        $session_id = (int) $_POST['session_id'];

        echo json_encode(ms_get_session_slots($session_id));
        exit;
    }

    private function create_local_user($name, $email)
    {

        global $wpdb;

        $new_uuid = Meeting_Support_Auth::v4();
        $password = mps_generate_password(8);

        $user = [];
        $user['uuid'] = $new_uuid;
        $user['name'] = sanitize_text_field($name);
        $user['email'] = sanitize_email($email);
        $user['is_active'] = '1';
        $user['password'] = password_hash($password, PASSWORD_BCRYPT);

        $format = [
            '%s',
            '%s',
            '%s',
            '%d'
        ];

        // Insert user into database
        $wpdb->insert($wpdb->base_prefix . 'ms_users', $user, $format);

        // Update existing submissions with the same email address to match the new UUID.
        $wpdb->update(
            $wpdb->base_prefix . 'ms_pc_submissions',
            array('author_uuid' => $user['uuid']),
            array('author_email' => $user['email'], 'author_uuid' => '')
        );

        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(false);
        $mail->setFrom(
            mps_get_option('sender_email_address', 'ws@ripe.net'),
            mps_get_option('sender_email_name', 'RIPE NCC Web Services')
        );

        $loader = new Twig_Loader_Filesystem(realpath(plugin_dir_path(__FILE__) . '../templates/mail'));
        $twig = new Twig_Environment($loader, []);

        $mailcontent = $twig->render('new_user.twig', [
            'meeting_name' => mps_get_option('meeting_name'),
            'user' => $user,
            'password' => $password,
            'login_url' => home_url('login')
        ]);

        $mail->addAddress($user['email']);
        $mail->Subject = 'New account created for ' . mps_get_option('meeting_name');
        $mail->Body = $mailcontent;

        $mail_sent = $mail->send();

        mps_log('User registered (' . $user['name'] . '), password sent to ' . $user['email'] . ' -  Success: ' . ($mail_sent == 1 ? 'Yes' : 'No'));
    }

    public function mps_contact_form_callback()
    {

        $nonce = $_REQUEST['_wpnonce'];

        if (! wp_verify_nonce($nonce, 'mps_contact_form')) {
            die('Security check');
        }

        $name = sanitize_text_field(stripslashes($_POST['sender_name']));
        $email = sanitize_email($_POST['email']);
        $subject = stripslashes(sanitize_text_field($_POST['subject']));
        $message = stripslashes(escape_multiline_text($_POST['message']));
        $captcha = sanitize_text_field($_POST['captcha']);


        $sender_email_address = mps_get_option('sender_email_address', 'ws@ripe.net');
        $meeting_organiser_email_address = mps_get_option('meeting_organiser_email', 'ws@ripe.net');

        // Make sure we have required fields
        if (! $name || ! $email || ! $subject || ! $message) {
            $_SESSION['old_post'] = $_POST;
            mps_flash('mps_contact_form', __('Missing required fields', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // We need a captcha field if the user isn't logged in
        if (! $this->auth->user) {
            // Is the CAPTCHA good?
            if ($_SESSION['phrase'] != $captcha) {
                $_SESSION['old_post'] = $_POST;
                mps_flash('mps_contact_form', __('Incorrect Verification Text', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        // Append some stuff the the message body
        $message .= PHP_EOL;
        $message .= PHP_EOL;
        $message .= $_SERVER['REMOTE_ADDR'];
        $message .= PHP_EOL;


        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom($email, $name);
        $mail->addAddress($sender_email_address);
        $mail->addAddress($meeting_organiser_email_address);
        $mail->isHTML(false);

        $mail->Subject = '[' . mps_get_option('meeting_name', 'Meeting') . ' Contact Form] ' . $email;
        $mail->Body = $message;

        $mail->send();

        mps_flash('mps_contact_form', __('Contact form successfully submitted', 'meeting-support'));

        mps_log('Contact Form submitted (' . $_SERVER['REMOTE_ADDR'] . ')');

        wp_safe_redirect(wp_get_referer());
        exit;
    }

    public function mps_user_update_profile_callback()
    {
        $nonce = $_REQUEST['_wpnonce'];

        if (! wp_verify_nonce($nonce, 'mps_user_update_profile')) {
            die('Security check');
        }

        global $wpdb;

        // Are we updating the profile info or changing the password?

        if ($_POST['update_info']) {
            // Updating the profile, do we have valid name and email values?
            $name = sanitize_text_field(stripslashes($_POST['user_name']));
            $email = sanitize_email($_POST['user_email']);

            if (! $name || ! $email) {
                mps_flash('mps_user_update_profile', __('Name or Email missing', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

             // Do we have a valid email?
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                mps_flash('mps_user_update_profile', __('Invalid Email Address', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $wpdb->update(
                $wpdb->base_prefix . 'ms_users',
                array('name' => $name, 'email' => $email),
                array( 'uuid' => $this->auth->user['uuid'] ),
                array( '%s', '%s' ),
                array( '%s' )
            );

            mps_log('Profile Updated (' . $this->auth->user['email'] . ')');
            mps_flash('mps_user_update_profile', __('Profile Updated', 'meeting-support'));
        }


        if ($_POST['update_password']) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $new_password_confirmation = $_POST['new_password_confirmation'];

            if (! $current_password || ! $new_password || ! $new_password_confirmation) {
                mps_flash('mps_user_update_profile', __('Missing fields for password change', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            if ($new_password != $new_password_confirmation) {
                mps_flash('mps_user_update_profile', __('New Password and New Password Confirmation do not match', ' meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            if ($current_password == $new_password) {
                mps_flash('mps_user_update_profile', __('You cannot change your password to your current password', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $min_password_length = 8;
            if (strlen($new_password) < $min_password_length) {
                mps_flash('mps_user_update_profile', sprintf(__('Your new password must be at least %d characters long', $min_password_length, 'meeting-support'), $min_password_length), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            if (! password_verify($current_password, $this->auth->user['password'])) {
                mps_flash('mps_user_update_profile', __('Your old password is incorrect', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            if (version_compare(PHP_VERSION, '5.3.7') >= 0) {
                $hash_prefix = '$2y$';
            } else {
                $hash_prefix = '$2a$';
            }

            // Passes all valiation, let's update the user password
            $wpdb->update(
                $wpdb->base_prefix . 'ms_users',
                array('password' => password_hash($new_password, PASSWORD_BCRYPT)),
                array( 'uuid' => $this->auth->user['uuid'] ),
                array( '%s' ),
                array( '%s' )
            );
            mps_log('Password changed (' . $this->auth->user['email'] . ')');

            mps_flash('mps_user_update_profile', __('Password Updated', 'meeting-support'));
        }

        wp_safe_redirect(wp_get_referer());
        exit;
    }


    public function mps_get_submission_info_callback()
    {

        $pc_config = pc_config();
        $statuses = $pc_config['submission_status'];
        $subid = intval($_POST['subid']);
        $submission = mps_get_submission($subid);
        // let's not expose everything...
        unset($submission->author_uuid);
        $submission->final_decision = stripslashes($submission->final_decision);
        $submission->author_comments = stripslashes($submission->author_comments);
        $submission->submission_abstract = stripslashes($submission->submission_abstract);
        $submission->author_name = stripslashes($submission->author_name);
        $submission->author_affiliation = stripslashes($submission->author_affiliation);
        $submission->submission_status_name = ucfirst($statuses[$submission->submission_status]);
        $submission->ratinginfo = ms_get_rating_info($subid);
        echo json_encode($submission);
        die();
    }

    public function mps_set_sub_final_decision_callback()
    {

        if (! pcss_user_can('chair_actions', $this->auth)) {
            echo 'Error';
            die();
        }

        global $wpdb;
        $subid = intval($_POST['subid']);
        $fields = $_POST['fields'];
        $sub_status = intval($fields['status']);
        $final_decision = escape_multiline_text($fields['final_decision']);

        $wpdb->update(
            $wpdb->base_prefix . 'ms_pc_submissions',
            array(
                'submission_status' => $sub_status,
                'final_decision' => $final_decision
                ),
            array( 'id' => $subid ),
            array(
                '%d',
                '%s'
                ),
            array( '%d' )
        );
        die();
    }

    public function mps_set_my_submission_rating_callback()
    {
        if (! pcss_user_can('rate_submission', $this->auth)) {
            die();
        }

        global $wpdb;
        $subid = intval($_POST['subid']);
        $fields = $_POST['fields'];
        $rating_content = intval($fields['content']);
        $rating_presenter = intval($fields['presenter']);
        $rating_comments = escape_multiline_text($fields['comments']);

        $auth = $this->auth;
        // check if the user has already made a rating
        $rating = ms_get_rating($auth->user['uuid'], $subid);

        $timestamp = date('Y-m-d H:i:s');


        if ($rating) { //rating from this uuid for this subid already exists, update
            $wpdb->update(
                $wpdb->base_prefix . 'ms_pc_submission_ratings',
                array( // array to send
                    'uuid' => $auth->user['uuid'],
                    'submission_id' => $subid,
                    'rating_content' => $rating_content,
                    'rating_presenter' => $rating_presenter,
                    'rating_comment' => $rating_comments,
                    'timestamp' => $timestamp
                ),
                array('id' => $rating->id), //where
                array( //validation
                    '%s',
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                ),
                array('%d') //where validation
            );
            mps_log('Editing submission rating for submission #' . $subid);

            echo $rating->id;
        } else { //no rating from this uuid for this subid yet, insert
            $wpdb->insert(
                $wpdb->base_prefix.'ms_pc_submission_ratings',
                array(
                    'uuid' => $auth->user['uuid'],
                    'submission_id' => $subid,
                    'rating_content' => $rating_content,
                    'rating_presenter' => $rating_presenter,
                    'rating_comment' => $rating_comments,
                    'timestamp' => $timestamp
                ),
                array(
                    '%s',
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                )
            );
            mps_log('Adding submission rating for submission #' . $subid);
            echo $wpdb->insert_id;
        }
        die();
    }

    public function mps_get_submission_ratings_html_callback()
    {
        $subid = intval($_POST['subid']);
        $submission = mps_get_submission($subid);
        $archived_submissions = mps_get_submission_archives($subid);
        $submissions_to_list = array_merge(array($submission), $archived_submissions);
        if ($submission->updated_date != '0000-00-00 00:00:00') {
            $submission_date = new Carbon($submission->updated_date);
        } else {
            $submission_date = new Carbon($submission->submission_date);
        }
        $ratings = ms_get_ratings($subid);
        $output = '';

        $ratings = array_reverse($ratings);

        $auth = $this->auth;

        $output .= '<div class="clear"></div>';
        $output .= '<br />';
        $output .= '<table class="table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<td><b>' . __('Name', 'meeting-support') . '</b></td>';
        $output .= '<td><b>' . __('Content Rating', 'meeting-support') . '</b></td>';
        $output .= '<td><b>' . __('Presenter Rating', 'meeting-support') . '</b></td>';
        $output .= '<td><b>' . __('Rated', 'meeting-support') . '</b></td>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        $output .= '<tr><td style="background-color:grey; padding:1px;" colspan="4"></td></tr>';

        if ($ratings) {
            foreach ($ratings as $rating) {
                $rating_date = new Carbon($rating->timestamp);
                $timestamp_text = $rating_date->diffForHumans();
                foreach ($submissions_to_list as $stl_id => $submission_to_list) {
                    if (isset($submission_to_list->updated_date)) {
                        if ($submission_to_list->updated_date == '0000-00-00 00:00:00') {
                            $stl_date = new Carbon($submission_to_list->submission_date);
                        } else {
                            $stl_date = new Carbon($submission_to_list->updated_date);
                        }
                    } else {
                        $stl_date = new Carbon($submission_to_list->timestamp);
                    }
                    if ($stl_date > $rating_date) {
                        $output .= '<tr><td class="warning submission-updated" colspan="4">Submission Updated '. $stl_date->diffForHumans() . ' (' . $stl_date->format('Y-m-d H:i') . ')</td></tr>';
                        $output .= '<tr><td style="background-color:grey; padding:1px;" colspan="4"></td></tr>';
                        $output .= '<tr><td style="padding:1px;" colspan="4"></td></tr>';
                        unset($submissions_to_list[$stl_id]);
                    }
                }

                if ($auth->auth_method == 'local') {
                    $details = (array) $auth->getUserByUUID($rating->uuid);
                } else {
                    $details = $auth->getCrowdUserByUUID($rating->uuid);
                }
                $output .= '<tr>';
                $output .= '<td><a href="mailto:' . $details['email'] . '":>' . $details['name'] . '</a></td>';
                $output .= '<td>' . ($rating->rating_content > 0 ? $rating->rating_content : "<i>" . __('None', 'meeting-support') . "</i>") . '</td>';
                $output .= '<td>' . ($rating->rating_presenter > 0 ? $rating->rating_presenter : "<i>" . __('None', 'meeting-support') . "</i>") . '</td>';
                $output .= '<td>' . $timestamp_text . '</td>';
                $output .= '</tr>';
                if ($rating->rating_comment != '') {
                    $output .= '<tr>';
                    $output .= '<td colspan="4">' . stripslashes(nl2br($rating->rating_comment)) . '</td>';
                    $output .= '</tr>';
                }
                $output .= '<tr><td style="background-color:grey; padding:1px;" colspan="4"></td></tr>';
            }
                // If there are leftover unmentioned submissions, they're obviously older than any comments. List here
            foreach ($submissions_to_list as $submission_to_list) {
                if (isset($submission_to_list->updated_date)) {
                    if ($submission_to_list->updated_date == '0000-00-00 00:00:00') {
                        $stl_date = new Carbon($submission_to_list->submission_date);
                    } else {
                        $stl_date = new Carbon($submission_to_list->updated_date);
                    }
                } else {
                    $stl_date = new Carbon($submission_to_list->timestamp);
                }
                $output .= '<tr><td class="warning submission-updated" colspan="4">Submission Updated '. $stl_date->diffForHumans() . ' (' . $stl_date->format('Y-m-d H:i') . ')</td></tr>';
                $output .= '<tr><td style="background-color:grey; padding:1px;" colspan="4"></td></tr>';
                $output .= '<tr><td style="padding:1px;" colspan="4"></td></tr>';
            }
        } else {
            // No ratings, let's print out submission history though
            foreach ($submissions_to_list as $stl_id => $submission_to_list) {
                if (isset($submission_to_list->updated_date)) {
                    if ($submission_to_list->updated_date == '0000-00-00 00:00:00') {
                        $stl_date = new Carbon($submission_to_list->submission_date);
                    } else {
                        $stl_date = new Carbon($submission_to_list->updated_date);
                    }
                } else {
                    $stl_date = new Carbon($submission_to_list->timestamp);
                }
                $output .= '<tr><td class="warning submission-updated" colspan="4">Submission Updated '. $stl_date->diffForHumans() . ' (' . $stl_date->format('Y-m-d H:i') . ')</td></tr>';
                $output .= '<tr><td style="background-color:grey; padding:1px;" colspan="4"></td></tr>';
                $output .= '<tr><td style="padding:1px;" colspan="4"></td></tr>';
                unset($submissions_to_list[$stl_id]);
            }
            //$output .= __('No ratings', 'meeting-support');
        }

        $output .= '</tbody>';
        $output .= '</table>';
        echo $output;
        die();
    }

    public function mps_delete_submission_rating_callback()
    {
        $ratingid = intval($_POST['ratingid']);
        if (pcss_user_can('rate_submission', $this->auth)) {
            ms_delete_rating($ratingid);
        }
        echo 'deleted';
        die();
    }


    public function mps_get_my_submission_rating_callback()
    {
        $subid = intval($_POST['subid']);
        $rating = ms_get_rating(null, $subid);
        if (! $rating) {
            return json_encode([]);
        }
        $return['id'] = $rating->id;
        $return['content'] = $rating->rating_content;
        $return['presenter'] = $rating->rating_presenter;
        $return['comment'] = trim(stripslashes($rating->rating_comment));
        echo json_encode($return);
        die();
    }

    public function mps_get_mass_mail_template_callback()
    {
        // Return a json encoded array of subject and body, which has also been dynamically generated to use the currently logged in users name in the body
        $template = $_POST['template'];
        $return = ms_get_mail_template($template);
        echo json_encode($return);
        die();
    }

    public function mps_get_mass_mail_info_callback()
    {
        // Get a json encoded string from post and return an HTML table with the send submissions back
        $pc_config = pc_config();
        $statuses = $pc_config['submission_status'];
        $submissions = $_POST['submissions'];
        $return = '';
        $return .= '<table class="table table-condensed table-striped table-bordered">';
        $return .= '<thead>';
        $return .= '<tr>';
        $return .= '<th>' . __('ID', 'meeting-support') . '</th>';
        $return .= '<th>' . __('Name', 'meeting-support') . '</th>';
        $return .= '<th>' . __('Email Address', 'meeting-support') . '</th>';
        $return .= '<th>' . __('Submission Title', 'meeting-support') . '</th>';
        $return .= '<th>' . __('Status', 'meetingname') . '</th>';
        $return .= '</tr>';
        $return .= '</thead>';
        $return .= '<tbody>';
        foreach ($submissions as $submission) {
            $subinfo = mps_get_submission($submission);
            $return .= '<tr>';
            $return .= '<td>' . $submission . '</td>';
            $return .= '<td>' . sanitize_text_field($subinfo->author_name) . '</td>';
            $return .= '<td>' . sanitize_email($subinfo->author_email) . '</td>';
            $return .= '<td>' . sanitize_text_field(stripslashes($subinfo->submission_title)) . '</td>';
            $return .= '<td>' . sanitize_text_field(ucfirst($statuses[$subinfo->submission_status])) . '</td>';
            $return .= '</tr>';
        }
        $return .= '</tbody>';
        $return .= '</table>';
        echo $return;
        die();
    }

    public function mps_pc_submission_callback()
    {

        global $wpdb;

        $loader = new Twig_Loader_Filesystem(realpath(plugin_dir_path(__FILE__) . '../templates/mail'));
        $twig = new Twig_Environment($loader, []);

        $nonce = $_REQUEST['_wpnonce'];

        if (! wp_verify_nonce($nonce, 'mps_pc_submission')) {
            die('Security check');
        }

        if (! empty($this->auth->user)) {
            $uuid = $this->auth->user['uuid'];
        } else {
            $uuid = '';
        }

        $submission_id = (int) $_POST['submission_id'];

        $submission = false;
        $file = false;
        $filename = '';

        if ($submission_id > 0) {
            if (empty($this->auth->user)) {
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $submission = mps_get_submission($submission_id);

            if (! $submission) {
                mps_flash('mps_pc_submission', __('Submission not found', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            if ($submission->author_uuid != $uuid && ( ! pcss_user_can('edit_submission', $this->auth) )) {
                mps_flash('mps_pc_submission', __('You do not have permission to edit that session', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            if (isset($_POST['delete'])) {
                // Only continue if we've got a good request
                if ($submission->author_uuid != $uuid && ( ! pcss_user_can('delete_submission', $this->auth) )) {
                    mps_flash('mps_pc_submission', __('You do not have permission to delete that session', 'meeting-support'), 'danger');
                    wp_safe_redirect(wp_get_referer());
                    exit;
                }

                $wpdb->delete($wpdb->base_prefix . 'ms_pc_submissions', array( 'id' => $submission_id ));
                mps_log($this->auth->user['email'] . ' deleted submission #' . $submission_id);
                mps_flash('mps_pc_submission', __('Submission deleted', 'meeting-support'), 'success');
                wp_safe_redirect(home_url('submit-topic/your-submissions'));
                exit;
            }
        }

        if ($_FILES['submissionupload']['name']) {
            $file = $_FILES['submissionupload'];
        }

        // What sort of request are we dealing with?
        $submission_type = trim(stripslashes($_POST['subtype']));
        $author_name = sanitize_text_field(trim(stripslashes($_POST['authorname'])));
        $author_affiliation = sanitize_text_field(trim(stripslashes($_POST['authoraffiliation'])));
        $author_email = trim(sanitize_email(stripslashes($_POST['authoremail'])));
        $submission_title = sanitize_text_field(trim(stripslashes($_POST['submissiontitle'])));
        $submission_abstract = escape_multiline_text($_POST['submissionabstract']);
        $author_comments = escape_multiline_text($_POST['authorcomments']);
        $submission_url = trim(stripslashes($_POST['submissionurl']));
        if (isset($_POST['captcha'])) {
            $captcha = $_POST['captcha'];
        }

        // Is the CAPTCHA good?
        if (! $this->auth->user && $_SESSION['phrase'] != $captcha) {
            mps_flash('mps_pc_submission', __('Incorrect Verification Text', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // Let's make sure we've got everything we need to process a request
        if (! $submission_type || ! $author_name || ! $author_email || ! $submission_title) {
            mps_flash('mps_pc_submission', __('Missing required fields', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // Make sure there is a file if there should be one.
        $file_mandatory_submission_types = (array) mps_get_option('pc_file_mandatory_submission_types', []);

        if (! $file && in_array($submission_type, $file_mandatory_submission_types) && ! $submission) {
            mps_flash('mps_pc_submission', __('A file is required for this type of Submission', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // Check filesize
        if ($file && ($file['size'] >= getMaximumFileUploadSize() )) {
            mps_flash('mps_pc_submission', __('Filesize too large', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // Check file extension
        if ($file && ! in_array(pathinfo(strtolower($file['name']), PATHINFO_EXTENSION), mps_get_option('allowed_file_types', []))) {
            mps_flash('mps_pc_submission', __('Forbidden file extension', 'meeting-support'), 'danger');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        $upload_dir = wp_upload_dir();
        $submissions_dir = $upload_dir['basedir'] . '/submissions/';

        // Make sure the /submissions directory is there
        if (! file_exists($submissions_dir)) {
            mkdir($submissions_dir);
        }

        // Handle the file upload and give us a nice name to put in the database
        if ($file) {
            // if there is a file upload and we're updating a submission, we need to make a backup of the old file for archiving purposes
            if ($submission) {
                $archive_filename = wp_unique_filename($submissions_dir, $submission->filename);
                copy($submissions_dir . $submission->filename, $submissions_dir . $archive_filename);
            }
            $filename = sanitize_file_name($file['name']);
            $filename = wp_unique_filename($submissions_dir, $filename);
            move_uploaded_file($file['tmp_name'], $submissions_dir . $filename);
        } else {
            if ($submission) {
                $filename = $submission->filename;
                $archive_filename = $submission->filename;
            }
        }

        // If we've got this far, we've passed all validation. Let's update/store
        if ($submission) {
            // Updating an existing submission
            $wpdb->update(
                $wpdb->base_prefix . 'ms_pc_submissions',
                array(
                    'submission_type' => $submission_type,
                    'submission_title' => $submission_title,
                    'submission_abstract' => $submission_abstract,
                    'submission_url' => $submission_url,
                    'author_name' => $author_name,
                    'author_affiliation' => $author_affiliation,
                    'author_email' => $author_email,
                    'author_comments' => $author_comments,
                    'filename' => $filename,
                    'updated_date' => date('Y-m-d H:i:s')
                    ),
                array(
                    'id' => $submission->id
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                ),
                array(
                    '%d'
                )
            );

            // If we are updating a submission, we also need to store the old data in the pc_submissions_archive table
            if ($submission->updated_date == '0000-00-00 00:00:00') {
                $archive_timestamp = $submission->submission_date;
            } else {
                $archive_timestamp = $submission->updated_date;
            }

            $wpdb->insert(
                $wpdb->base_prefix . 'ms_pc_submissions_archive',
                array(
                    'submission_id' => $submission->id,
                    'submission_type' => $submission->submission_type,
                    'submission_title' => $submission->submission_title,
                    'submission_abstract' => $submission->submission_abstract,
                    'submission_url' => $submission->submission_url,
                    'submission_status' => $submission->submission_status,
                    'filename' => $submission->filename,
                    'author_name' => $submission->author_name,
                    'author_affiliation' => $submission->author_affiliation,
                    'author_email' => $submission->author_email,
                    'author_uuid' => $submission->author_email,
                    'author_comments' => $submission->author_comments,
                    'final_decision' => $submission->final_decision,
                    'timestamp' => $archive_timestamp
                )
            );

            // Send an email to PC & WS
            $meetingname = mps_get_option('meeting_name', 'Meeting');
            $mailsubject = '[' . $meetingname . ' PC Submission System] ' . $author_name;
            $mailcontent = $twig->render('updated_pc_submission.twig', [
                'meeting_name' => $meetingname,
                'author_name' => $author_name,
                'submission_id' => $submission_id,
                'submission_type' => ms_get_submission_type_name($submission_type),
                'edited_timestamp' => date('Y-m-d H:i:s') . " (" . date_default_timezone_get() . ")",
                'submission_title' => $submission_title,
                'author_email' => $author_email,
                'submission_abstract' => $submission_abstract,
                'author_comments' => $author_comments
            ]);

            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom(mps_get_option('sender_email_address'), mps_get_option('sender_email_name'));
            $mail->addAddress(mps_get_option('pc_email_address'));
            $mail->addBCC(mps_get_option('sender_email_address'));
            $mail->isHTML(false);
            $mail->Subject = $mailsubject;
            $mail->Body = $mailcontent;

            $mail->send();

            mps_log($this->auth->user['email'] . ' updated submission #' . $submission_id);
        } else {
            // Adding a new submission
            // If the user isn't logged in, let's try and match the email address given with an existing user to give ownership
            if ($uuid == '') {
                $user = (array) $this->auth->getUserByEmail($author_email);
                if ($user) {
                    $uuid = $user['uuid'];
                }
            }

            // Inserting a brand new submission
            $wpdb->insert(
                $wpdb->base_prefix . 'ms_pc_submissions',
                array(
                    'submission_type' => $submission_type,
                    'submission_title' => $submission_title,
                    'submission_abstract' => $submission_abstract,
                    'submission_url' => $submission_url,
                    'submission_status' => '1',
                    'author_name' => $author_name,
                    'author_affiliation' => $author_affiliation,
                    'author_email' => $author_email,
                    'author_comments' => $author_comments,
                    'author_uuid' => $uuid,
                    'filename' => $filename,
                    'submission_date' => date('Y-m-d H:i:s'),
                    'updated_date' => date('Y-m-d H:i:s'),
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                )
            );


            // Send an email to PC & WS
            $meetingname = mps_get_option('meeting_name', 'Meeting');

            $mailsubject = '[' . $meetingname . ' PC Submission System] ' . $author_name;
            // Create body from template
            $mailcontent = $twig->render('new_pc_submission.twig', [
                'meeting_name' => $meetingname,
                'submission_type' => ms_get_submission_type_name($submission_type),
                'current_time' => date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ')',
                'submission_title' => $submission_title,
                'author_name' => $author_name,
                'author_email' => $author_email,
                'submission_abstract' => $submission_abstract,
                'author_comments' => $author_comments
            ]);

            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom(mps_get_option('sender_email_address'), mps_get_option('sender_email_name'));
            $mail->addAddress(mps_get_option('pc_email_address'));
            $mail->addBCC(mps_get_option('sender_email_address'));
            $mail->isHTML(false);
            $mail->Subject = $mailsubject;
            $mail->Body = $mailcontent;
            $mail->send();

            // Send an email to the author
            $mailsubject = $meetingname . ' Submission Received -- [' . $submission_title . ']';
            $mailcontent = $twig->render('new_pc_submission_thankyou.twig', [
                'author_name' => $author_name,
                'submission_title' => $submission_title,
                'meeting_name' => $meetingname,
            ]);

            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom(mps_get_option('sender_email_address'), mps_get_option('sender_email_name'));
            $mail->addReplyTo(mps_get_option('pc_email_address'));
            $mail->addAddress($author_email);
            $mail->isHTML(false);
            $mail->Subject = $mailsubject;
            $mail->Body = $mailcontent;
            $mail->send();

            // Send an email to the WG Chairs, if set in the MPS config area
            $wg_chair_email = mps_get_option('wg_chair_email_address');
            $send_to_wg_chair = mps_get_option('send_new_submission_to_wg_chairs');

            if ($send_to_wg_chair == true && $wg_chair_email == false) {
                mps_log('Mails will not be sent to WG Chair as no email address has been set');
            }

            if ($send_to_wg_chair && $wg_chair_email) {
                $subject = '[' . $meetingname . ' PC Submission System] ' . $author_name;
                // Create body from template
                $body = $twig->render('new_pc_submission.twig', [
                    'meeting_name' => $meetingname,
                    'submission_type' => ms_get_submission_type_name($submission_type),
                    'current_time' => date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ')',
                    'submission_title' => $submission_title,
                    'author_name' => $author_name,
                    'author_email' => $author_email,
                    'submission_abstract' => $submission_abstract,
                    'author_comments' => $author_comments
                ]);

                // Mailer instance
                $mail = new PHPMailer();
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->setFrom(mps_get_option('sender_email_address'), mps_get_option('sender_email_name'));
                $mail->addReplyTo($wg_chair_email);
                $mail->addAddress($wg_chair_email);
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->send();
            }

            mps_log($this->auth->user['email'] . ' added submission #' . $wpdb->insert_id);
        }

        if ($submission) {
            mps_flash('mps_pc_submission', 'Submission successfully updated', 'success');
            wp_safe_redirect(home_url('/submit-topic/submission-form/?edit_submission=' . $submission->id));
        } else {
            if (! empty($this->auth->user)) {
                wp_safe_redirect(home_url('/submit-topic/your-submissions/'));
            } else {
                mps_flash('mps_pc_submission', 'Submission successfully sent', 'success');
                wp_safe_redirect(home_url('/submit-topic/submission-form/'));
            }
        }

        exit;
    }

    public function mps_ajax_delete_presentation_callback()
    {
        /**
         * ajax request to delete presentation
         */

        check_ajax_referer('ajax-nonce-mps', 'security');

        // If they got this far then it's a safe ajax request
        $presentation_id = (int) $_POST['presentation_id'];

        $presentation = mps_get_presentation($presentation_id);

        if (! $presentation) {
            wp_send_json_error(['message' => __('Presentation does not exist')]);
        }

        if ($this->auth->user['uuid'] != $presentation->author_uuid || ! $this->auth->user) {
            wp_send_json_error(['message' => __('You do not have permission to delete this presentation')]);
        }

        // If we've got this far, the user is allowed to delete this presentation
        mps_log('User has requested to delete their own presentation: ' . $this->auth->user['uuid']);
        mps_delete_presentation($presentation);
        wp_send_json_success(['message' => __('Presentation deleted')]);

        // Just to be sure
        wp_die();
    }


    public function mps_ajax_upload_presentation_callback()
    {
        /**
         * ajax request to upload presentation
         */

        // Check that it's a good request
        check_ajax_referer('ajax-nonce-mps', 'security');

        // If the user isnt logged in and hasn't got a captcha, deny.
        if (! $this->auth->user && ! isset($_POST['fetch_form'])) {
            if (isset($_POST['g-recaptcha-response'])) {
                $token = sanitize_text_field($_POST['g-recaptcha-response']);
                $is_captcha_valid = invisible_recaptcha_validation($token);

                if (! $is_captcha_valid) {
                    //True - What happens when user is verified
                    wp_send_json_error(['message' => __('Incorrect Verification Text', 'meeting-support')]);
                    wp_die();
                }
            }
        }

        // Are we editing an existing presentation or adding a new one?
        // we use 'fetch_form' flag to return form html without process data
        if (isset($_POST['presentation_id'])) {
            // We are editing an existing presentation

            $presentation_id = (int) $_POST['presentation_id'];
            // get existing presentation from database
            $pres = mps_get_presentation($presentation_id);

            if (empty($pres)) {
                wp_send_json_error(['message' => __('Presentation does not exist', 'meeting-support')]);
                wp_die();
            }

            // Let's make sure the user is allowed to edit this presentation
            if ($this->auth->user['uuid'] !== $pres->author_uuid) {
                wp_send_json_error(['message' => __('You do not have permission to edit this presentation', 'meeting-support')]);
                wp_die();
            }

            if (isset($_POST['fetch_form'])) {
                wp_send_json_success(['html' => ms_upload_form($this->auth, $pres, true, 'modal-')]);
                wp_die();
            }
        } else {
            // This is a new presentation
            if (isset($_POST['fetch_form'])) {
                wp_send_json_success(['html' => ms_upload_form($this->auth, null, true, 'modal-')]);
                wp_die();
            }
        }


        $files = [];
        if (! empty($_FILES['presentation_upload'])) {
            $files = $_FILES['presentation_upload'];

            $names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);
            foreach ($files as $key => $part) {
                // only deal with valid keys and multiple files
                $key = (string) $key;
                if (isset($names[$key]) && is_array($part)) {
                    foreach ($part as $position => $value) {
                        $files[$position][$key] = $value;
                    }
                }
                // remove old key reference or empty value
                unset($files[$key]);
            }
        }

        // for new persentation should be uploaded at list one file
        if (empty($files) && ! isset($_POST['presentation_id'])) {
            wp_send_json_error(['message' => __('At least one file should be uploaded', 'meeting-support')]);
            wp_die();
        }

        $presentation = [];

        $presentation['author_name'] = sanitize_text_field(stripslashes($_POST['author_name']));
        $this->check_required_field('Author Name', $presentation['author_name']);
        $presentation['title'] = sanitize_text_field(stripslashes($_POST['presentation_title']));
        $this->check_required_field('Title', $presentation['title']);
        $presentation['author_email'] = sanitize_email($_POST['author_email']);
        $this->check_required_field('Author Email', $presentation['author_email']);
        $presentation['session_id'] = (int) $_POST['presentation_session'];
        // $this->check_required_field('Session', $presentation['session_id']);
        $this->check_required_field('Slot', $_POST['presentation_slot']);
        $presentation['slot_id'] = (int) $_POST['presentation_slot'];
        $presentation['author_affiliation'] = sanitize_text_field(stripslashes($_POST['author_affiliation']));
        // $this->check_required_field('Author Affiliation', $presentation['author_affiliation']);

        // If the session_id is 0, it's probably because there was a slot set, it was probably because it was a non-js request
        if ($presentation['session_id'] == 0 && $presentation['slot_id'] != 0) {
            $slot = ms_get_slot($presentation['slot_id']);
            if ($slot) {
                $presentation['session_id'] = $slot->session_id;
            }
        }

        // check uploaded files and return array of errors if at list one file didn't pass test
        if (! empty($files)) {
            $file_check = $this->mps_check_presentation_files_upload($files);
            if ($file_check['success'] == false) {
                // File check failed - no need to go any further.
                wp_send_json_error(['message' => $file_check['message']]);
                wp_die();
            }
        }

        if (isset($_POST['presentation_id'])) {
            // We are editing an existing presentation

            // Let's make sure the user is allowed to edit this presentation
            if ($this->auth->user['uuid'] !== $pres->author_uuid) {
                wp_send_json_error(['message' =>  __('You do not have permission to edit this presentation', 'meeting-support')]);
                wp_die();
            }

            // User is good, let's update the presentation with the new fields
            $presentation['id'] = (int) $_POST['presentation_id'];

            // Update the presentation in the DB
            $this->mps_update_presentation($presentation);


            // We are deleting all files (which are in ['delete_files'] array) from HDD and database
            if (isset($_POST['delete_files'])) {
                $delete_files = $_POST['delete_files'];
                $presentation_files = json_decode($pres->filename);

                // we can delete only if current presentation contain file(s)
                if (! empty($delete_files) || ! empty($presentation_files)) {
                    $presentation_dir = mps_get_option('presentations_dir');

                    if (! $presentation_dir) {
                        mps_log('Cannot handle file upload, presentation directory not defined');
                        wp_send_json_error(['message' => __('Error occurred uploading files')]);
                        wp_die();
                    }

                    foreach ($delete_files as $key => $file_name) {
                        // check if filename exist in presentation
                        if (in_array($file_name, $presentation_files)) {
                            // build path to file
                            $file_path = get_home_path() . $presentation_dir . $file_name;
                            // check if file exist
                            if (file_exists($file_path)) {
                                // delete file
                                unlink(get_home_path() . $presentation_dir . $file_name);
                            }
                            // delete file from array, this array we save as presentation->filename later
                            $presentation_files = array_diff($presentation_files, [$file_name]);
                        }
                    }

                    // update database
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->prefix . 'ms_presentations',
                        array('filename' => json_encode(array_values(array_unique($presentation_files))) ),
                        array('id' => $presentation['id'] )
                    );
                }
            }

            // add new files to presenatation
            if ($files) {
                $result = $this->mps_add_files_to_presentation($presentation['id'], $files);

                if (! $result) {
                    mps_log('Error occurred uploading files');
                    wp_send_json_error(['message' => __('Error occurred uploading files')]);
                    wp_die();
                }
            }

            $this->mps_updated_presentation_mail($presentation);

            mps_flash('mps_presentation_upload', __('Presentation successfully updated', 'meeting-support'));
            mps_log('Presentation Edit');
            wp_send_json_success(['message' => __('Presentation successfully updated')]);
            wp_die();
        } else {
            // This is a new presentation

            // No files, put an empty array in the filename list
            $presentation['filename'] = json_encode([]);

            // author_uuid should first be resolved from being logged in, if not then try to match the email address with an existing user
            if ($this->auth->user) {
                $presentation['author_uuid'] = $this->auth->user['uuid'];
            } else {
                $uploader = $this->auth->getUserByEmail($presentation['author_email']);
                if ($uploader) {
                    $presentation['author_uuid'] = $uploader['uuid'];
                } else {
                    // No user found.
                    // If we're using local auth, let's create an account for the person who uploaded so they can manage their presentations later on.
                    // TODO [WSMPS-106]
                }
            }

            // We will put this information into the database, then with the $row->id we will name the file accordingly and UPDATE the row
            $id = $this->mps_insert_new_presentation($presentation);

            if (! $id) {
                // Row insert failed, no need to go any further.
                mps_log('Cannot handle presentation upload, no row ID returned');
                wp_send_json_error(['message' => __('Error uploading presentation', 'meeting-support')]);
                exit;
            }

            $presentation['id'] = $id;

            $result = $this->mps_add_files_to_presentation($id, $files);

            if (! $result) {
                mps_log('error during uploading: ' . $result);
                wp_send_json_error(['message' => __('Error during uploading files')]);
                wp_die();
            }

            // Send email to WS
            $this->ms_uploaded_presentation_mail($presentation);

            // If we got this far, the upload was good and we handled it all correctly
            $success_message = __('Presentation successfully uploaded', 'meeting-support');
            if (!$this->auth->user) {
                $success_message = $success_message . '<br>' . __('Log in to your ' . mps_get_ripencc_login_link('RIPE NCC Access account', true) . ' to see all your presentations', 'meeting-support');
            }
            mps_flash('mps_presentation_upload', $success_message);
            mps_log('Presentation Upload');
            wp_send_json_success(['message' => __('Presentation uploaded')]);
            wp_die();
        }

        wp_die();
    }

    private function check_required_field($field_name, $value)
    {
        if ($field_name == 'Slot') {
            if ($value == '') {
                mps_log('Required field: ' . $field_name . ' is empty');
                wp_send_json_error(['message' => $field_name . __(" field can't be empty", 'meeting-support')]);
            }
        } else {
            if (empty($value)) {
                mps_log('Required field: ' . $field_name . ' is empty');
                wp_send_json_error(['message' => $field_name . __(" field can't be empty", 'meeting-support')]);
                exit;
            }
        }
    }

    public function mps_presentation_delete_callback()
    {
        /**
         * response to public request to delete a presentation (form post)
         */
        $nonce = $_REQUEST['_wpnonce'];

        if (! wp_verify_nonce($nonce, 'mps_presentation_delete')) {
            die('Security check');
        }

        $presentation_id = (int) $_POST['presentation_id'];

        $presentation = mps_get_presentation($presentation_id);

        if (! $presentation) {
            mps_flash('mps_presentation_upload', __('This presentation does not exist', 'meeting-support'), 'danger');
            wp_safe_redirect(remove_query_arg('delete_presentation', wp_get_referer()));
            exit;
        }

        // Make sure the user is actually allowed to delete this presentation
        if ($this->auth->user['uuid'] != $presentation->author_uuid || ! $this->auth->user) {
            mps_flash('mps_presentation_upload', __('You do not have permission to delete this presentation', 'meeting-support'), 'danger');
            wp_safe_redirect(remove_query_arg('delete_presentation', wp_get_referer()));
            exit;
        }

        // We've got this far, let's delete and return
        mps_log('User has requested to delete their own presentation: ' . $this->auth->user['uuid']);
        mps_delete_presentation($presentation);

        mps_flash('mps_presentation_upload', __('Presentation deleted', 'meeting-support'));
        wp_safe_redirect(remove_query_arg('delete_presentation', wp_get_referer()));
        exit;
    }

    public function mps_presentation_upload_callback()
    {

        // Check that it's a good request
        $nonce = $_REQUEST['_wpnonce'];

        if (! wp_verify_nonce($nonce, 'mps_presentation_upload')) {
            die('Security check');
        }

        // If the user isnt logged in and hasn't got a captcha, deny.
        if (! $this->auth->user) {
            $captcha = $_POST['captcha'];
            if ($_SESSION['phrase'] != $captcha) {
                mps_flash('mps_presentation_upload', __('Incorrect Verification Text', 'meeting-support'), 'danger');
                $_SESSION['old_post'] = $_POST;
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        // Are we editing an existing presentation or adding a new one?
        if (isset($_POST['presentation_id'])) {
            // We are editing an existing presentation

            $presentation_id = (int) $_POST['presentation_id'];
            $presentation = mps_get_presentation($presentation_id);

            // Let's make sure the user is allowed to edit this presentation
            if ($this->auth->user['uuid'] !== $presentation->author_uuid) {
                mps_flash('mps_presentation_upload', __('You do not have permission to edit this presentation', 'meeting-support'), 'danger');
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            // User is good, let's update the presentation with the new fields

            // Convert to array so we can re-populate the database if needed
            $presentation = (array) $presentation;

            $presentation['author_name'] = sanitize_text_field(stripslashes($_POST['author_name']));
            $presentation['author_affiliation'] = sanitize_text_field(stripslashes($_POST['author_affiliation']));
            $presentation['title'] = sanitize_text_field(stripslashes($_POST['presentation_title']));
            $presentation['author_email'] = sanitize_email($_POST['author_email']);
            $presentation['session_id'] = (int) $_POST['presentation_session'];
            $presentation['slot_id'] =  (int) $_POST['presentation_slot'];

            // If the session_id is 0, it was probably because it was a non-js request
            if ($presentation['session_id'] == 0 && $presentation['slot_id'] != 0) {
                $slot = ms_get_slot($presentation['slot_id']);

                if ($slot) {
                    $presentation['session_id'] = $slot->session_id;
                }
            }

            // Update the presentation in the DB
            $this->mps_update_presentation($presentation);

            // Did we get a new file? It's not mandatory since we're editing an existing file.
            // There's a possibility that we're replacing an exisitng file, or adding a new file to the list of files for this presentation.
            if ($_FILES['presentation_upload']['size'] > 0) {
                // Is it a good file?
                $file_check = $this->mps_check_presentation_file_upload();

                if ($file_check['success'] == false) {
                    // File check failed - no need to go any further.
                    mps_flash('mps_presentation_upload', $file_check['message'], 'danger');
                    $_SESSION['old_post'] = $_POST;
                    wp_safe_redirect(wp_get_referer());
                    exit;
                }

                // Are we replacing a file or adding a new one to the list?
                $presentation_file_select = (int) $_POST['presentation_file_select'];

                if ($presentation_file_select !== 999) {
                    $this->mps_replace_presentation_file($presentation['id'], $presentation_file_select);
                } else {
                    // We are adding a new file to the presentation
                    // First we need to make sure that the filename doesn't collide with an existing
                    $presentation_files = json_decode($presentation['filename']);

                    $new_file_name = sanitize_file_name($presentation_id . '-' . $_FILES['presentation_upload']['name']);

                    if (in_array($new_file_name, $presentation_files)) {
                        mps_flash('mps_presentation_upload', __('A file with this name already exists', 'meeting-support'), 'danger');
                        $_SESSION['old_post'] = $_POST;
                        wp_safe_redirect(wp_get_referer());
                        exit;
                    }

                    // Now we can add it to the db
                    $this->mps_add_file_to_presentation($presentation['id']);
                }
            }

            // Send a mail to WS
            $this->mps_updated_presentation_mail($presentation);

            mps_flash('mps_presentation_upload', __('Presentation successfully updated', 'meeting-support'));
            mps_log('Presentation updated');
            wp_safe_redirect(wp_get_referer());
            exit;
        } else {
            // This is a new presentation

            // All new presentations require a file, check that.
            $file_check = $this->mps_check_presentation_file_upload();

            if ($file_check['success'] == false) {
                // File check failed - no need to go any further.
                mps_flash('mps_presentation_upload', $file_check['message'], 'danger');
                $_SESSION['old_post'] = $_POST;
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $presentation = [];
            $presentation['author_name'] = sanitize_text_field(stripslashes($_POST['author_name']));
            $presentation['author_affiliation'] = sanitize_text_field(stripslashes($_POST['author_affiliation']));
            $presentation['title'] = sanitize_text_field(stripslashes($_POST['presentation_title']));
            $presentation['author_email'] = sanitize_email($_POST['author_email']);
            $presentation['session_id'] = (int) $_POST['presentation_session'];
            $presentation['slot_id'] =  (int) $_POST['presentation_slot'];
            // No files, put an empty array in the filename list
            $presentation['filename'] = json_encode([]);


            // If the session_id is 0, it's probably because there was a slot set, it was probably because it was a non-js request
            if ($presentation['session_id'] == 0 && $presentation['slot_id'] != 0) {
                $slot = ms_get_slot($presentation['slot_id']);
                if ($slot) {
                    $presentation['session_id'] = $slot->session_id;
                }
            }


            // author_uuid should first be resolved from being logged in, if not then try to match the email address with an existing user
            if ($this->auth->user) {
                $presentation['author_uuid'] = $this->auth->user['uuid'];
            } else {
                $uploader = $this->auth->getUserByEmail($presentation['author_email']);
                if ($uploader) {
                    $presentation['author_uuid'] = $uploader['uuid'];
                } else {
                    // No user found.
                    // If we're using local auth, let's create an account for the person who uploaded so they can manage their presentations later on.
                    // TODO [WSMPS-106]
                }
            }

            // We will put this information into the database, then with the $row->id we will name the file accordingly and UPDATE the row
            $id = $this->mps_insert_new_presentation($presentation);

            if (! $id) {
                // Row insert failed, no need to go any further.
                mps_log('Cannot handle presentation upload, no row ID returned');
                mps_flash('mps_presentation_upload', __('Error uploading presentation', 'meeting-support'), 'danger');
                $_SESSION['old_post'] = $_POST;
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $result = $this->mps_add_file_to_presentation($id);

            if (! $result) {
                mps_flash('mps_presentation_upload', __('Error uploading presentation', 'meeting-support'), 'danger');
                $_SESSION['old_post'] = $_POST;
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $session_info = ms_get_session_data($presentation['session_id']);
            $slot_info = ms_get_slot($presentation['slot_id']);

            // Send a mail to WS
            $this->ms_uploaded_presentation_mail($presentation);

            // If we got this far, the upload was good and we handled it all correctly
            $success_message = __('Presentation successfully uploaded', 'meeting-support');
            if (!$this->auth->user) {
                $success_message = $success_message . '<br>' . __('Log in to your ' . mps_get_ripencc_login_link('RIPE NCC Access account') . ' to see all your presentations', 'meeting-support');
            }
            mps_flash('mps_presentation_upload', $success_message);
            mps_log('Presentation Upload');
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        die();
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
        $file_key = 'presentation_upload';

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

    private function mps_add_files_to_presentation($presentation_id, $files)
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

        $presentation_dir = mps_get_option('presentations_dir');

        if (! $presentation_dir) {
            mps_log('Cannot handle file upload, presentation directory not defined');
            return false;
        }

        foreach ($files as $key => $file) {
            $file_name = sanitize_file_name($presentation_id . '-' . $file['name']);

            if (! move_uploaded_file($file['tmp_name'], get_home_path() . $presentation_dir . $file_name)) {
                mps_log('Unable to move_uploaded_file()');
                return false;
            }

            // The file has been moved, let's associate it with the right presentation
            $success = $this->mps_update_presentation_file_list($presentation_id, $file_name);

            // warning: $success can be 0 when 0 rows updated
            if ($success === false) {
                mps_log('Cannot add file to database: ' . $success);
                return false;
            }
        }

        return true;
    }

    private function mps_replace_presentation_file($presentation_id, $file_id)
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
            mps_log('Trying to attach a file to a non-existant presentation');
            return false;
        }

        // First we'll move the file to where we want it to be
        $file_key = 'presentation_upload';

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
        $success = $this->mps_update_presentation_file_list($presentation_id, $file_name, $file_id);

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
            //if (! in_array($file_name, $current_files)) {
                $current_files[$file_id] = sanitize_file_name($file_name);
            //} else {

            //    unset($current_files[$file_id]);
            //}
        }
        $filename = json_encode(array_values(array_unique($current_files)));

        // Update existing presentation with the updated list of filename(s)
        $result = $wpdb->update(
            $wpdb->prefix . 'ms_presentations',
            array('filename' => $filename),
            array('id' => $presentation_id )
        );

        // $result is false if error, or # rows updated (0 is bad)
        return $result;
    }

    private function mps_update_presentation($presentation)
    {
        /**
         * Function to handle the row update for an existing presentation
         */

        if (! $presentation || ! is_array($presentation)) {
            mps_log('Received a bad request to edit a presentation. This should never happen.');
            return false;
        }

        global $wpdb;

        // Grab the presentation id and then remove it from the fields to be updated
        $presentation_id = (int) $presentation['id'];
        unset($presentation['id']);
        unset($presentation['filename']);

        $presentation['last_edited'] = date('Y-m-d H:i:s');
        $success = $wpdb->update($wpdb->prefix . 'ms_presentations', $presentation, array( 'id' => $presentation_id));

        return $success;
    }

    private function mps_insert_new_presentation($presentation)
    {
        /**
         * Function to handle the row insert for a brand new presentation, this will return the row id to use in future processing
         */

        if (! $presentation || ! is_array($presentation)) {
            mps_log('Received a bad request to add a new presentation. This should never happen.');
            return false;
        }

        global $wpdb;

        $presentation['last_edited'] = date('Y-m-d H:i:s');
        $success = $wpdb->insert($wpdb->prefix . 'ms_presentations', $presentation);

        if ($success) {
            return $wpdb->insert_id;
        }

        return false;
    }

    private function mps_check_presentation_files_upload($files)
    {
        /**
         * Function to check that a valid file has been uploaded to the presentation upload system
         */

        // Check filesize
        $total_size = array_sum($_FILES['presentation_upload']['size']);

        if ($total_size >= (int) getMaximumFileUploadSize()) {
            throw new RuntimeException(__('Exceeded filesize limit', 'meeting-support'));
        }

        $errors = [];
        foreach ($files as $key => $file) {
            // echo json_encode($part);
            // wp_die();
            try {
                // Undefined | Multiple Files | $_FILES Corruption Attack
                // If this request falls under any of them, treat it invalid.
                if (! isset($file['error']) || is_array($file['error'])) {
                    array_push($errors, __('Invalid file', 'meeting-support') . ': ' . $file['name']);
                }

                // Check $_FILES['presentation_upload']['error'] value.
                switch ($file['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        array_push($errors, __('No file sent', 'meeting-support'));
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        array_push($errors, __('Exceeded filesize limit', 'meeting-support'));
                        break;
                    default:
                        array_push($errors, __('Unknown errors'));
                }


                // Check extension against allowed filetypes
                $allowed_files = mps_get_option('allowed_file_types', []);
                $filename = $file['name'];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (! in_array($extension, $allowed_files)) {
                    array_push($errors, __('File extension is not allowed', 'meeting-support'));
                }
            } catch (RuntimeException $e) {
                $error_message = $e->getMessage();
                array_push($errors, $error_message);
                mps_log('File upload failed: ' . $error_message);
                mps_log(json_encode($_POST));
                mps_log(json_encode($_FILES));
            }
        }

        if (empty($errors)) {
            return array( 'success' => true );
        } else {
            return array( 'success' => false, 'message' => $errors );
        }
    }


    private function mps_check_presentation_file_upload()
    {
        /**
         * Function to check that a valid file has been uploaded to the presentation upload system
         */

        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (! isset($_FILES['presentation_upload']['error']) || is_array($_FILES['presentation_upload']['error'])) {
                throw new RuntimeException(__('Invalid parameters', 'meeting-support'));
            }

            // Check $_FILES['presentation_upload']['error'] value.
            switch ($_FILES['presentation_upload']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException(__('No file sent', 'meeting-support'));
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException(__('Exceeded filesize limit', 'meeting-support'));
                default:
                    throw new RuntimeException('Unknown errors');
            }

            // Check filesize
            if ($_FILES['presentation_upload']['size'] >= (int) getMaximumFileUploadSize()) {
                throw new RuntimeException(__('Exceeded filesize limit', 'meeting-support'));
            }

            // Check extension against allowed filetypes
            $allowed_files = mps_get_option('allowed_file_types', []);
            $filename = $_FILES['presentation_upload']['name'];
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (! in_array($extension, $allowed_files)) {
                throw new RuntimeException(__('File extension not allowed', 'meeting-support'));
            }

            return array( 'success' => true );
        } catch (RuntimeException $e) {
            $error_message = $e->getMessage();
            mps_log('File upload failed: ' . $error_message);
            mps_log(json_encode($_POST));
            mps_log(json_encode($_FILES));
            return array( 'success' => false, 'message' => $error_message );
        }
    }

    public function mps_send_mass_mail_callback()
    {

        $senderuuid = '';
        $senderemail = '';
        $senderip = $_SERVER['REMOTE_ADDR'];

        $auth = $this->auth;

        if (! empty($auth->user)) {
            $senderuuid = $auth->user['uuid'];
            $senderemail = $auth->user['email'];
        }

        // Check that the user is allowed to send emails
        if (! pcss_user_can('email_out_from_pc', $this->auth)) {
            mps_log("WARNING (PC Mass Mail) Attempt to send mass mail by ".$senderuuid." <".$senderemail."> from: ".$senderip);
            echo 'No access';
            die();
        }


        $ids = $_POST['ids'];
        // Make sure we have an actual array of ids to send mail to
        if (! is_array($ids)) {
            echo 'No ids';
            die();
        }

        // Get originals with {submission_title} and {author_name} tags
        $origsubject = stripslashes($_POST['subject']);
        $origbody = stripslashes($_POST['body']);

        // Who are we sending emails from?
        $pc_email = mps_get_option('pc_email_address');

        // Let's loop through each ID sent to send this email to
        foreach ($ids as $id) {
            // Get submission info
            $submission = mps_get_submission($id);

            // Search/Replace
            $search = array('{submission_title}', '{author_name}');
            $replace = array(stripslashes(html_entity_decode($submission->submission_title, ENT_QUOTES)), $submission->author_name);
            $subject = str_replace($search, $replace, $origsubject);
            $body = str_replace($search, $replace, $origbody);

            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->From = $pc_email;
            $mail->FromName = mps_get_option('meeting_name') . ' Programme Committee';

            $mail->addAddress($submission->author_email);
            $mail->AddCC($pc_email);
            $mail->isHTML(false);

            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail_sent = $mail->send();

            if ($mail_sent) {
                // Mail sent successfully
                mps_log("(PC Mass Mail) Successfully sent to <".$submission->author_email."> - Requested by ".$senderuuid." <".$senderemail."> from: ".$senderip);
            } else {
                // Problem sending mail
                mps_log("(PC Mass Mail) Unsuccessfully sent to <".$submission->author_email."> - Requested by ".$senderuuid." <".$senderemail."> from: ".$senderip);
            }
        }
        die();
    }

    public function ms_uploaded_presentation_mail($presentation)
    {
        $session_info = ms_get_session_data($presentation['session_id']);
        $slot_info = ms_get_slot($presentation['slot_id']);

        // Send a mail to WS
        $body = 'A presentation has been added by ' . sanitize_text_field($presentation['author_name']) . PHP_EOL;
        $body .= '============================================' . PHP_EOL;
        $body .= 'Added on: ' . date('d-m-Y') . PHP_EOL;
        $body .= 'By: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL . PHP_EOL;
        $body .= 'Presenter Name: ' . sanitize_text_field($presentation['author_name']) . PHP_EOL;
        $body .= 'Presenter Email: ' . sanitize_email($presentation['author_email']) . PHP_EOL;
        $body .= 'Affiliation: ' . sanitize_text_field($presentation['author_affiliation']) . PHP_EOL;
        $body .= 'Presentation Title: ' . sanitize_text_field($presentation['title']) . PHP_EOL;
        if ($session_info) {
            $body .= 'Session: ' . $session_info->name . ' (#' . $session_info->id . ')' . PHP_EOL;
        } else {
            $body .= 'Session: <None>' . PHP_EOL;
        }
        if ($slot_info) {
            $body .= 'Slot: ' . $slot_info->title . ' (#' . $slot_info->id . ')' . PHP_EOL;
        } else {
            $body .= 'Slot: <None>' . PHP_EOL;
        }
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->From = mps_get_option('sender_email_address', 'ws@ripe.net');
        $mail->FromName = mps_get_option('sender_email_name', 'RIPE NCC Web Services');

        $mail->addAddress(mps_get_option('sender_email_address', 'ws@ripe.net'));
        $mail->isHTML(false);

        $mail->Subject = '[' . mps_get_option('meeting_name') . ' Presentation Upload] ' . sanitize_text_field($presentation['author_name']);
        $mail->Body = $body;

        $presentation = mps_get_presentation($presentation['id']);
        $files = json_decode($presentation->filename);
        foreach ($files as $file) {
            $home_path = get_home_path();
            $file_path = $home_path . mps_get_option('presentations_dir') . $file;
            $mail->AddAttachment($file_path, $file);
        }
        $mail->send();
    }

    public function mps_updated_presentation_mail($presentation)
    {
        // If we got this far, the upload was good and we handled it all correctly
        $session_info = ms_get_session_data($presentation['session_id']);
        $slot_info = ms_get_slot($presentation['slot_id']);

        // Send a mail to WS
        $body = 'A presentation has been edited by ' . sanitize_text_field($presentation['author_name']) . PHP_EOL;
        $body .= '============================================' . PHP_EOL;
        $body .= 'Edited on: ' . date('d-m-Y') . PHP_EOL;
        $body .= 'By: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL . PHP_EOL;
        $body .= 'Presenter Name: ' . sanitize_text_field($presentation['author_name']) . PHP_EOL;
        $body .= 'Presenter Email: ' . sanitize_email($presentation['author_email']) . PHP_EOL;
        $body .= 'Affiliation: ' . sanitize_text_field($presentation['author_affiliation']) . PHP_EOL;
        $body .= 'Presentation Title: ' . sanitize_text_field($presentation['title']) . PHP_EOL;
        if ($session_info) {
            $body .= 'Session: ' . $session_info->name . ' (#' . $session_info->id . ')' . PHP_EOL;
        } else {
            $body .= 'Session: <None>' . PHP_EOL;
        }
        if ($slot_info) {
            $body .= 'Slot: ' . $slot_info->title . ' (#' . $slot_info->id . ')' . PHP_EOL;
        } else {
            $body .= 'Slot: <None>' . PHP_EOL;
        }
        $mail = new PHPMailer();

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->From = mps_get_option('sender_email_address', 'ws@ripe.net');
        $mail->FromName = mps_get_option('sender_email_name', 'RIPE NCC Web Services');


        $mail->addAddress(mps_get_option('sender_email_address', 'ws@ripe.net'));
        $mail->isHTML(false);

        $mail->Subject = '[' . mps_get_option('meeting_name') . ' Presentation Edit] ' . sanitize_text_field($presentation['author_name']);
        $mail->Body = $body;

        $presentation = mps_get_presentation($presentation['id']);
        foreach (json_decode($presentation->filename) as $file) {
            $home_path = get_home_path();
            $file_path = $home_path . mps_get_option('presentations_dir') . $file;
            $mail->AddAttachment($file_path, $file);
        }
        $mail->send();
    }


    public function mps_custom_routes()
    {

        add_rewrite_rule(
            '^logout$',
            'index.php?mps_action=logout',
            'top'
        );

        add_rewrite_rule(
            '^speakers/([^/]*)/([^/]*)/?',
            'index.php?pagename=speakers&speaker_slug=$matches[1]&mps_lang=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^speakers/([^/]*)/?',
            'index.php?pagename=speakers&speaker_slug=$matches[1]',
            'top'
        );
    }

    public function mps_custom_query_vars($vars)
    {
        $vars[] = 'mps_action';
        $vars[] = 'speaker_slug';
        $vars[] = 'mps_lang';
        return $vars;
    }

    public function mps_custom_page_titles($title)
    {
        if (! in_the_loop()) {
            return $title;
        }
        global $wp_query;
        $pagename = get_query_var('pagename');
        if ($pagename != 'speakers') {
            return $title;
        }
        $speaker_slug = get_query_var('speaker_slug');

        if ($speaker_slug) {
            $speaker = mps_get_speaker_by_slug($speaker_slug);
            if ($speaker) {
                return sanitize_text_field($speaker->name);
            } else {
                return '';
            }
        }
        return $title;
    }

    public function mps_custom_requests($wp)
    {
        $valid_actions = array( 'logout' );

        if (! empty($wp->query_vars['mps_action']) && in_array($wp->query_vars['mps_action'], $valid_actions)) {
            $action = $wp->query_vars['mps_action'];
            if ($action == 'logout') {
                $user = $this->auth->user;
                mps_log('User Logout (' . $user['email'] . ')');
                session_destroy();
                if (wp_get_referer()) {
                    wp_safe_redirect(wp_get_referer());
                } else {
                    wp_safe_redirect(get_home_url());
                }
                exit;
            }
        }
    }

    public function mps_public_ajax_url()
    {
        echo '<script tyle="text/javascript">var ajaxurl = "' . home_url('/wp-admin/admin-ajax.php') . '"</script>' . PHP_EOL;
    }

    public function mps_add_blog_id_body_class($classes)
    {
        $classes[] = 'blog-' . get_current_blog_id();
        return $classes;
    }

    public function mps_pcss_navigation($content)
    {
        /**
         * Add a navigation menu to the top of all pages which fall under {home}/pcss/
         */

        global $post;

        $pcss_parent_page = get_page_by_path('pcss');

        if (! $pcss_parent_page) {
            return $content;
        }

        if (($post->ID == $pcss_parent_page->ID || $post->post_parent == $pcss_parent_page->ID) && pcss_user_can('view_all_submissions')) {
            $menu = '<div id="pcssnav">';
            $menu .= '<ul>';
            $menu .= '<li><a href="' . get_permalink($pcss_parent_page->ID) . '">PCSS Home</a></li>';
            $menu .= wp_list_pages(array(
                'child_of' => $pcss_parent_page->ID,
                'echo' => false,
                'title_li' => '',
                'sort_column' => 'menu_order',
            ));
            $menu .= '</ul>';
            $menu .= '</div>';

            return $menu . $content;
        }

        return $content;
    }

    public function mps_add_translation_link($content)
    {
        /**
         * Add a bit of text at the bottom of pages (in Russian or English) to advertise the fact that the current page is also available in another language
         */

        global $post;

        if (! function_exists('icl_get_languages')) {
            return $content;
        }

        $current_locale = get_locale();

        $languages = icl_get_languages();

        // Only show these links if we have exactly 2 languages available, and they are EN and RU
        if (count($languages) == 2 && isset($languages['en']) && isset($languages['ru'])) {
            switch ($current_locale) {
                case 'en_US':
                    return $content . '<div class="clear"></div><br><div class="translation_link">Этот текст также доступен на <a href="' . esc_url($languages['ru']['url']) . '">русском</a></div>';
                    break;

                case 'ru_RU':
                    return $content . '<div class="clear"></div><br><div class="translation_link">This text is also available in <a href="' . esc_url($languages['en']['url']) . '">English</a></div>';
                    break;

                default:
                    return $content;
                    break;
            }
        }

        return $content;
    }

    public function mps_add_custom_query_var($vars)
    {
        $vars[] = "details";
        $vars[] = "editsub";
        $vars[] = "deletesub";
        $vars[] = "deletesubconfirm";
        $vars[] = "video";
        $vars[] = "steno";
        $vars[] = "chat";
        return $vars;
    }

    public function mps_add_rewrite_rules($rules)
    {
        // Add rewrite rules for the custom variables
        $new_rules = array(
            //'live/room/([^/]+)/?$' => 'index.php?pagename=live&room=$matches[1]',
            'archives/video/([^/]+)/?$' => 'index.php?pagename=archives&video=$matches[1]',
            'archives/chat/([^/]+)/?$' => 'index.php?pagename=archives&chat=$matches[1]',
            'archives/steno/([^/]+)/?$' => 'index.php?pagename=archives&steno=$matches[1]'
        );
        return ($new_rules + $rules);
    }

    public function mps_get_agenda_items_callback()
    {
        $result = array('schedule' => []);
        $sessions = mps_get_all_sessions();


        foreach ($sessions as $session) {
            $date = date('Y-m-d', strtotime($session->start_time));
            $session_start = date('H:i', strtotime($session->start_time));

            $sessions_now = mps_sessions_at_datetime(new DateTime($session->start_time));

            $sessions_now = array_map('make_pretty_session_array', $sessions_now);

            foreach ($result['schedule'] as $blob) {
                if ($blob['date'] == $date && $blob['groups']['time'] == $session_start) {
                    continue 2;
                }
            }

            $result['schedule'][] = ['date' => $date, 'groups' => ['time' => $session_start, 'sessions' => $sessions_now]];
        }

        print json_encode($result);
        exit;
    }

    public function mps_update_speaker_bio_callback()
    {

        if (! wp_verify_nonce($_POST['_wpnonce'], 'mps_update_speaker_bio')) {
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        if (! $this->auth) {
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        $comms_ticket_email = mps_get_option('comms_ticket_email');
        if ($comms_ticket_email) {
            $meeting_name = mps_get_option('meeting_name');
            $speaker_bio_page_url = get_admin_url() . 'admin.php?page=meeting-support-speakers';
            $loader = new Twig_Loader_Filesystem(realpath(plugin_dir_path(__FILE__) . '../templates/mail'));
            $twig = new Twig_Environment($loader, []);
            $mailcontent = $twig->render('speaker_bio_comms_review.twig', [
                'meeting_name' => $meeting_name,
                'speaker_bio_page' => $speaker_bio_page_url
            ]);

            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom('noreply@ripe.net', $meeting_name . ' System');
            $mail->addAddress($comms_ticket_email);
            $mail->isHTML(false);
            $mail->Subject = mps_get_option('meeting_name', 'Meeting') . ' Speaker Bio Review';
            $mail->Body = $mailcontent;
        }

        $speaker = mps_get_speaker_by_uuid($this->auth->user['uuid']);

        $lang = mps_get_short_current_locale();
        $bio = stripslashes($_POST['speaker_bio']);

        // Make sure that the bio meets the requirements
        $re = '/[^\s]+\s+/';
        preg_match_all($re, $bio, $matches, PREG_SET_ORDER, 0);

        // Bio word count validation
        if (count($matches) > 200) {
            mps_flash('speaker-bio', 'Speaker bios are limited to 200 words', 'danger');
            $_SESSION['bio'] = $bio;
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        if (count($matches) < 60) {
            mps_flash('speaker-bio', 'Speaker bios must be at least 60 words', 'danger');
            $_SESSION['bio'] = $bio;
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        if ($speaker) {
            // If a speaker already exists, then we're going to add a new bio to the _draft
            if (array_key_exists($lang, $speaker->bio_texts_draft)) {
                // Don't update if there is already a draft being processed
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            if (array_key_exists($lang, $speaker->bio_texts)) {
                // Don't update if the draft is the same as non-draft
                if ($bio == $speaker->bio_texts[$lang]) {
                    wp_safe_redirect(wp_get_referer());
                    exit;
                }
            }

            $speaker->bio_texts_draft[$lang] = $bio;
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'ms_speakers',
                ['bio_texts_draft' => json_encode($speaker->bio_texts_draft)],
                ['id' => $speaker->id ]
            );

            if ($comms_ticket_email) {
                $mail->send();
            }

            wp_safe_redirect(wp_get_referer());
            exit;
        }

        // If we got this far, we're adding a new Speaker entry
        $speaker = [
            'name' => sanitize_text_field($this->auth->user['name']),
            'uuid' => $this->auth->user['uuid'],
            'slug' => mps_get_unique_speaker_slug(sanitize_title($this->auth->user['name'])),
            'bio_texts' => json_encode([]),
            'bio_texts_draft' => json_encode([$lang => $bio])
        ];

        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'ms_speakers',
            $speaker
        );
        if ($comms_ticket_email) {
            $mail->send();
        }
        wp_safe_redirect(wp_get_referer());
        exit;
    }

    public function mps_get_pathable_agenda_items_callback()
    {
        $sessions = mps_get_all_sessions();

        // Iterate through the sessions to add the pretty name
        foreach ($sessions as $session) {
            $session->room_full = get_real_room_name($session->room);
        }


        if (isset($_GET['output']) && $_GET['output'] == 'json') {
            // Do we want to show slots too?
            if (isset($_GET['show_slots'])) {
                // Iterate through the sessions and add slot data for each one
                foreach ($sessions as $session) {
                    $slots = ms_get_session_slots($session->id);

                    $tidy_slots = [];
                    $children_slots = [];
                    foreach ($slots as $slot) {
                        // If the slot is a parent (parent_id == 0) then add it right away
                        if ($slot->parent_id == 0) {
                            $slot->children = [];
                            $tidy_slots[$slot->id] = $slot;
                        } else {
                            // Otherwise, add it to the children slots to process later
                            $children_slots[] = $slot;
                        }
                    }

                    // Now the parents are sorted, we can throw the children in there
                    foreach ($children_slots as $children_slot) {
                        $tidy_slots[$children_slot->parent_id]->children[] = $children_slot;
                    }

                    // Now we need to fix the sorting
                    usort($tidy_slots, function ($a, $b) {
                        if ($a->ordering == $b->ordering) {
                            return 0;
                        }
                        return ($a->ordering < $b->ordering) ? -1 : 1;
                    });

                    // Sort the children
                    foreach ($tidy_slots as $tidy_slot) {
                        if (count($tidy_slot->children) > 1) {
                            usort($tidy_slot->children, function ($a, $b) {
                                if ($a->ordering == $b->ordering) {
                                    return 0;
                                }
                                return ($a->ordering < $b->ordering) ? -1 : 1;
                            });
                        }
                    }

                    // Clean up the slots
                    foreach ($tidy_slots as $tidy_slot) {
                        unset($tidy_slot->parent_id);
                        unset($tidy_slot->ratable);
                        unset($tidy_slot->id);
                        unset($tidy_slot->session_id);
                        $tidy_slot->ordering = (int) $tidy_slot->ordering;
                        foreach ($tidy_slot->children as $tidy_child_slot) {
                            unset($tidy_child_slot->parent_id);
                            unset($tidy_child_slot->ratable);
                            unset($tidy_child_slot->id);
                            unset($tidy_child_slot->session_id);
                            $tidy_child_slot->ordering = (int) $tidy_child_slot->ordering;
                        }
                    }

                    $session->slots = $tidy_slots;
                }
            }

            // Print in JSON
            wp_send_json($sessions);
        } else {
            // Print in XML
            $xml = new SimpleXMLElement('<xml/>');
            $meetings = $xml->addChild('meetings');
            foreach ($sessions as $session) {
                $meeting = $meetings->addChild('meeting');
                $meeting->addChild('external_id', $session->id);
                $meeting->addChild('name', $session->name);
                $meeting->addChild(
                    'starts_at',
                    date(
                        'm/d/Y H:i:s',
                        strtotime($session->start_time)
                    )
                );
                $meeting->addChild(
                    'ends_at',
                    date(
                        'm/d/Y H:i:s',
                        strtotime($session->end_time)
                    )
                );
                $meeting->addChild('location_name', get_real_room_name($session->room));
                $meeting->addChild('mandatory', 'false');
                $meeting->addChild('private', 'false');
                if (trim($session->url) != '') {
                    $meeting->addChild('document_list', get_home_url() . $session->url);
                }
                $meeting->addChild('unschedulable', 'true');
            }

            Header('Content-type: text/xml');
            print($xml->asXML());
            exit;
        }
    }
}
