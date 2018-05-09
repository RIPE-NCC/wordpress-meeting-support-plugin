<?php

use Carbon\Carbon;

class Meeting_Support_Shortcodes
{

    public function __construct($auth)
    {

        $this->auth = $auth;
    }

    public function mps_user_links()
    {
        $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        $html = '';

        // Show the links to either login or out.
        if (empty($this->auth->user)) {
            if ($this->auth->auth_method == 'crowd') {
                $html .= '<a class="sign-in" href="';
                $html .= $this->auth->crowd_config['login_url'];
                $html .= '?originalUrl=' . urlencode($pageURL) . '">';
            } else {
                $html .= '<a class="sign-in" href="' . network_home_url('/login/') . '">';
            }
            if ($this->auth->auth_method == 'crowd') {
                $html .= __('Sign in with RIPE NCC Access', 'meeting-support');
            } else {
                $html .= __('Sign in', 'meeting-support');
            }
                    $html .= '</a>';
        } else {
            if ($this->auth->auth_method == 'crowd') {
                $html .= '<a class="user-profile" href="' . $this->auth->crowd_config['profile_url'] .'">' . $this->auth->user['name'] .'</a>';
            } else {
                $html .= '<a class="user-profile" href="' . mps_get_option('user_profile_url', '/user-profile') . '">' . $this->auth->user['name'] .'</a>';
            }

            $html .= ' | ';
            //$html .= '<br>';
            if ($this->auth->auth_method == 'crowd') {
                $html .= '<a class="sign-out" href="' . $this->auth->crowd_config['logout_url'] . '?originalUrl=' . urlencode($pageURL) . '">';
            } else {
                $html .= '<a class="sign-out" href="' . network_home_url('/logout/') . '">';
            }
            $html .= __('Sign out', 'meeting-support');
            $html .= '</a>';
        }
        $html .= '<div class="clear"></div>';
        return $html;
    }

    public function mps_ripe_user_links()
    {
        $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $login_url = $this->auth->crowd_config['login_url'] . '?originalUrl=' . urlencode($pageURL);
        if (empty($this->auth->user)) {
            return '<a class="anon" href="' . $login_url . '">Login</a>';
        }

        // If we got this far, we have a logged in user.
        $name = sanitize_text_field($this->auth->user['name']);
        $picture_url = 'https://access.ripe.net/picture/' . $this->auth->user['uuid'];
        $profile_url = $this->auth->crowd_config['profile_url'];
        $logout_url = $this->auth->crowd_config['logout_url'];
        return '<div id="loggedin" style="display: block">
                    <a class="active" href="#">
                        <span id="loggedin-username">' . $name . '</span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <a class="profile" style="background-image:url(' . $picture_url . ')"></a>
                    <div id="loggedin-box" class="portlet box-shadow" style="display: none;">
                        <h6>User Details</h6>
                        <ul>
                            <li><a href="' . $profile_url . '">Profile</a></li>
                            <li><a href="' . $logout_url .'">Logout</a></li>
                        </ul>
                    </div>
                    <a href="notification" id="notifications"></a>
                </div>';
    }


    public function mps_user_login()
    {

        // Let's get the referrer and keep it safe
        $referrer = wp_get_referer();

        // If the referrer is not /login/
        if (strpos($referrer, '/login/') === false) {
            // Set the return_to value to wherever they came from.
            $_SESSION['return_to'] = wp_get_referer();
        } else {
            // Probably a bad password attempt, we want to keep the current (good) referrer
            if (! $_SESSION['return_to']) {
                $_SESSION['return_to'] = home_url();
            }
        }

        // If the user is already logged in, we should send them back to from whence they came!
        if (empty($this->auth->user)) {
            $html = '<div class="boot">';
            $html .= '<form id="login_form" method="POST" action="' . admin_url('admin-post.php') . '">';
            $html .= '<input type="hidden" name="action" value="mps_user_login">';
            $html .= wp_nonce_field('mps_user_login', '_wpnonce', true, false);
            $html .= '<div class="login_container">';
            $html .= '<div class="row">';
            $html .= '<div class="col-md-6 col-md-offset-3">';
            $html .= mps_flash('mps_login');
            $html .= '<div class="panel panel-default">';
            $html .= '<div class="panel-body">';
            $html .= '<fieldset>';
            $html .= '<div class="form-group">';
            $html .= '<input required class="form-control" placeholder="' . __('Email Address', 'meeting-support') . '" name="email" type="email">';
            $html .= '</div>';
            $html .= '<div class="form-group">';
            $html .= '<input required class="form-control" placeholder="' . __('Password', 'meeting-support') . '" name="password" type="password" value="">';
            $html .= '</div>';
            $html .= '<input class="btn btn-md btn-success btn-block" type="submit" value="' . __('Sign in', 'meeting-support') . '">';
            $html .= '</fieldset>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<a href="' . mps_get_option('user_registration_url', '/user-registration') . '" class="pull-left btn btn-xs btn-default">' . __('Create account', 'meeting-support') . '</a>';
            $html .= '<a href="mailto:webmaster@ripe.net" class="pull-right">' . __('Forgot your password?', 'meeting-support') . '</a>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        }
        $html = __('You are already logged in', 'meeting-support') . '<br><br>';
        $html .= '<a href="' . home_url() . '">' . __('Go to Homepage', 'meeting-support') . '</a><br><br>';
        return $html;
    }

    public function mps_user_register()
    {
        if (! empty($this->auth->user)) {
            return __('You are already registered', 'meeting-support') . '. <a class="btn btn-default" href="' .  mps_get_option('user_profile_url', '/user-profile/') . '">' . __('View Profile', 'meeting-support') . '</a>';
        }

        // Captcha library
        $captcha = new Gregwar\Captcha\CaptchaBuilder;
        $captcha->setDistortion(true);
        $captcha->setIgnoreAllEffects(true);
        $captcha->build();

        $_SESSION['phrase'] = $captcha->getPhrase();

        $html = '';
        $html .= '<div class="boot">';
        $html .= '<form id="register_form" method="POST" action="' . admin_url('admin-post.php') . '">';
        $html .= '<input type="hidden" name="action" value="mps_user_register">';
        $html .= wp_nonce_field('mps_user_register', '_wpnonce', true, false);
        $html .= '<div class="register_container">';
        $html .= '<div class="row">';
        $html .= '<div class="col-md-6 col-md-offset-3">';
        $html .= mps_flash('mps_user_register');
        $html .= '<div class="panel panel-default">';
        $html .= '<div class="panel-body">';
        $html .= '<fieldset>';
        $html .= '<div class="form-group">';
        $html .= '<label>' . __('Name', 'meeting-support') . '</label>';
        if (isset($_SESSION['old_post'])) {
            $html .= '<input required class="form-control" value="' . sanitize_text_field($_SESSION['old_post']['name']) . '" placeholder="' . __('Full Name', 'meeting-support') . '" name="name" type="text">';
        } else {
            $html .= '<input required class="form-control" placeholder="' . __('Full Name', 'meeting-support') . '" name="name" type="text">';
        }
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '<label>' . __('Email Address', 'meeting-support') . '</label>';
        if (isset($_SESSION['old_post'])) {
            $html .= '<input required class="form-control" value="' . sanitize_email($_SESSION['old_post']['email']) . '" placeholder="' . __('Email Address', 'meeting-support') . '" name="email" type="email">';
        } else {
            $html .= '<input required class="form-control" placeholder="' . __('Email Address', 'meeting-support') . '" name="email" type="email">';
        }
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '<div class="captcha"><img src="' . $captcha->inline() . '" /></div>';
        $html .= '<input required class="form-control" placeholder="' . __('Verification Text', 'meeting-support') . '" name="captcha" type="text">';
        $html .= '</div>';
        $html .= '<input class="btn btn-md btn-success btn-block" type="submit" value="' . __('Register', 'meeting-support') . '">';
        $html .= '</fieldset>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Clear old form data from $_SESSION
        unset($_SESSION['old_post']);

        return $html;
    }

    public function mps_user_profile()
    {

        $html = '';

        // Don't show a profile if the user is not logged in
        if (empty($this->auth->user)) {
            $html .= '<div class="boot">';
            if ($this->auth->auth_method == 'crowd') {
                $html .= '<a class="btn btn-success" href="' . $this->auth->crowd_config['login_url'] . '?originalUrl=' . urlencode($pageURL) . '">';
            } else {
                $html .= '<a class="btn btn-success" href="/login/">';
            }
            if ($this->auth->auth_method == 'crowd') {
                $html .= __('Sign in with RIPE NCC Access', 'meeting-support');
            } else {
                $html .= __('Sign in', 'meeting-support');
            }
            $html .= '</a>';
            $html .= '</div>';
            $html .= '<br>';
            $html .= '<br>';
            return $html;
        }
        $html .= '<div class="boot">';
        $html .= '<form method="POST" action="' . admin_url('admin-post.php') . '">';
        $html .= '<input type="hidden" name="action" value="mps_user_update_profile">';
        $html .= wp_nonce_field('mps_user_update_profile', '_wpnonce', true, false);
        $html .= mps_flash('mps_user_update_profile');
        $html .= '<div class="form-group">';
        $html .= '<label for="user_name">' . __('Full Name', 'meeting-support') . '</label>';
        $html .= '<input type="text" class="form-control" id="user_name" name="user_name" value="' . sanitize_text_field($this->auth->user['name']) . '">';
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '<label for="user_email">' . __('Email Address', 'meeting-support') . '</label>';
        $html .= '<input type="email" class="form-control" id="user_email" name="user_email" value="' . sanitize_email($this->auth->user['email']) . '">';
        $html .= '</div>';
        $html .= '<input type="submit" name="update_info" value="' . __('Update Profile', 'meeting-support') . '" class="btn btn-success pull-right"/>';
        $html .= ' 	<br>';
        $html .= '  <hr>';
        $html .= '  <h4>' . __('Change Password', 'meeting-support') . '</h4><br>';
        $html .= '<div class="form-group">';
        $html .= '<label for="current_password">' . __('Current Password', 'meeting-support') . '</label>';
        $html .= '<input type="password" class="form-control" id="current_password" name="current_password">';
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '<label for="new_password">' . __('New Password', 'meeting-support') . '</label>';
        $html .= '<input type="password" class="form-control" id="new_password" name="new_password">';
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= '<label for="new_password_confirmation">' . __('New Password Confirmation', 'meeting-support') . '</label>';
        $html .= '<input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">';
        $html .= '</div>';
        $html .= '<input type="submit" id="update_password" name="update_password" value="' . __('Change Password', 'meeting-support') . '" class="btn btn-success pull-right"/>';
        $html .= '</form>';

        $html .= '</div>';

        return $html;
    }

    public function ms_show_star_image($atts)
    {

        extract(shortcode_atts(array(
            'n' => '5',
            ), $atts));

        $output = '';
        $n = intval($n);

        if ($n > 0) {
            $output .= '<span class="starrating" title="' . $n . ' Star Rating">';

            for ($i = $n; $i > 0; $i--) {
                $output .= '<i class="fa fa-star"></i>';
            }

            $output .= '</span>';
        }

        return $output;
    }

    public function ms_live_video($atts)
    {
        /**
        * Function to display a flowplayer element with the live feed
        * of the URL given
        */

        extract(shortcode_atts(array(
            'url' => '',
            'splash' => '',
            'autoplay' => false
        ), $atts));

        if ($autoplay) {
            $autoplay = 'true';
        } else {
            $autoplay = 'false';
        }

        // Make a hash so we can have multiple flowplayer instances on one view
        $unique = uniqid();

        $html = '';

        // If $splash is set, let's remove the default play button
        if ($splash != '') {
            $html .= '
        <style>
            .is-splash.flowplayer .fp-ui {
                background: none;
            }
        </style>
        ';
        }

        $html .= '<script>';
        $html .= '
            jQuery(document).ready(function () {
                flowplayer("#hlsjslive' . $unique . '", {
                    autoplay: ' . $autoplay . ',
                    embed: false,
                    clip: {
                        hlsQualities: [
                            // dimensions of all levels are the same
                            // set bitrate labels explicitly instead
                            { level: 1, label: "SD" },
                            { level: 5, label: "HD" }
                        ],
                        hlsjs: {
                            // this is a highly error prone stream
                            // always try to recover from errors
                            recover: -1,
                            // slightly better behaviour for this problem stream
                            smoothSwitching: false
                        },
                        sources: [
                            { type: "application/x-mpegurl",
                            src: "' . esc_url($url) . '" }
                        ]
                    }

                });

            });';
        $html .= '</script>';

        if ($autoplay == 'true') {
            $html .= '<div style="background-image:url('. esc_url($splash) .');" id="hlsjslive' . $unique . '" class="is-closeable"></div>';
        } else {
            $html .= '<div style="background-image:url('. esc_url($splash) .');" id="hlsjslive' . $unique . '" class="is-splash is-closeable"></div>';
        }

        return $html;
    }

    public function ms_live_chat($atts)
    {
        // Display live IRC (chat)
        extract(shortcode_atts(array(
        'room' => '',
        'height' => '322',
        ), $atts));

        $irc = '';

        $irc .= '<iframe style="height: ' . $height . 'px" id="stFrame" src="https://meetings.ripe.net/cgi-bin/irc/irc.cgi?room=' . $room . '" scrolling="no" frameborder="0" marginheight="0px" marginwidth="0px" width="99%">';
        $irc .= '<p>' . __('Your browser does not support iframes.', 'meeting-support') . '</p>';
        $irc .= '</iframe>';

        return $irc;
    }


    public function ms_sponsors($atts)
    {
        extract(shortcode_atts(array(
        'section' => '1',
        ), $atts));

        $section_id = (int) $section;

        $section = mps_get_sponsor_section($section_id);

        if (! $section) {
            return '';
        }

        $sponsors = mps_get_section_sponsors($section_id);

        if (! $sponsors) {
            return '';
        }

        $output = '';
        $output .= '<div class="mps_sponsor_section">';
        $output .= '<h4 class="mps_sponsor_title">' . __(sanitize_text_field($section->name), 'meeting-support') . '</h4>';
        $output .= '<div class="mps_sponsors_list">';
        foreach ($sponsors as $sponsor) {
            if ($section->is_grayscale) {
                $output .= '<a target="_blank" href="' . esc_url($sponsor->link_url) . '"><img class="grayscale mps_sponsor_logo" title="' . sanitize_text_field($sponsor->name) . '" src="' . esc_url($sponsor->image_url) . '"></a>';
            } else {
                $output .= '<a target="_blank" href="' . esc_url($sponsor->link_url) . '"><img class="mps_sponsor_logo" title="' . sanitize_text_field($sponsor->name) . '" src="' . esc_url($sponsor->image_url) . '"></a>';
            }
        }

        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }

    public function ms_presentations_vertical($atts)
    {
        /**
        * Shortcode to display a vertical list of presentations, and attached files
        */

        extract(shortcode_atts(array(
        'pdfonly' => false
        ), $atts));

        $html = '';

        $presentations = mps_get_all_presentations();

        $html .= '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>' . __('Presenter Name', 'meeting-support') . '</th>';
        $html .= '<th>' . __('Session', 'meeting-support') . '</th>';
        $html .= '<th>' . __('Presentation Title', 'meeting-support') . '</th>';
        $html .= '<th>' . __('File(s)', 'meeting-support') . '</th>';
        $html .= '<th style="width:1px;">' . __('Date Added', 'meeting-support') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        foreach ($presentations as $presentation) {
            $session_info = ms_get_session_data($presentation->session_id);
            $html .= '<tr>';
            $html .= '<td>' . sanitize_text_field($presentation->author_name) . '</td>';
            $html .= '<td>' . sanitize_text_field($session_info->name) . '</td>';
            $html .= '<td>' . sanitize_text_field($presentation->title) . '</td>';
            $html .= '<td>';
            foreach (json_decode($presentation->filename) as $file) {
        // If it's PDF only, then limit what we show.
                if ($pdfonly && ! ends_with($file, '.pdf')) {
                    continue;
                }
                $html .= '<a target="_blank" href="/' . mps_get_option('presentations_dir') . sanitize_file_name($file) .'"><i class="fa-2x fa ' . get_file_icon_class($file) . '"></i></a> ';
            }
            $html .= '</td>';
            $html .= '<td>' . date('Y&#8209;m&#8209;d', strtotime($presentation->submission_date)) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    public function ms_agenda_vertical()
    {
        // Shortcode to display the whole agenda (and presentation files) in a vertical layout, probably for ENOG
        $sessions = mps_get_all_sessions();
        $current_day = '';
        $current_session = '';
        $vertical_sessions = array();
        $time_increments = mps_get_option('meeting_increments', 15); //minutes
        $presentation_dir = mps_get_option('presentations_dir', '');

        $onwards_time = date('H:i', strtotime('midnight -' . $time_increments . ' minutes'));

        $html = '';

        foreach ($sessions as $session) {
            if ($session->is_intermission || $session->is_social) {
                continue;
            }
            $tidy_day = date('l, j F', strtotime($session->start_time));
            // Is it a new day?
            if ($tidy_day !== $current_day) {
                $html .= '<table class="sessions-vertical">';
                $html .= '<thead><tr><th colspan="3">' . $tidy_day . '</th></tr></thead>';
                $html .= '</table>';
                $current_day = $tidy_day;
            }
            if ($current_session !== $session->id) {
                $html .= '<h3>' . $session->name . '</h3>';
                $current_session = $session->id;
            }
            $slots = ms_get_session_slots($session->id);
            if (count($slots) > 0) {
                $html .= '<table>';
            }
            foreach ($slots as $slot) {
                // don't handle the children directly
                if ($slot->parent_id != 0) {
                    continue;
                }
                if (mps_get_option('global_ratings') == 'on' && $session->is_rateable && $slot->ratable) {
                    $ratings_enabled = true;
                } else {
                    $ratings_enabled = false;
                }
                if (substr($slot->content, 0, 3) !== '<p>') {
                    $content = '<p>' . $slot->content . '</p>';
                } else {
                    $content = $slot->content;
                }
                // $colspan is dependent on if ratings are enabled, and if this slot has children
                $colspan = 1;
                $children = ms_get_slot_children($slot->id);
                if (count($children) > 0) {
                    $is_parent = true;
                } else {
                    $is_parent = false;
                }

                // If it is a parent slot we'll never show the files
                if ($is_parent) {
                    $colspan++;
                }
                // If ratings are enabled, we need to make sure there is a column for it
                if (! $is_parent) {
                    if (! $ratings_enabled) {
                        $colspan++;
                    }
                } else {
                    $colspan++;
                }

                $html .= '<tr>';
                $html .= '<td colspan="' . $colspan . '">' . $content . '</td>';
                // td for files, don't show if it's a parent slot
                if (! $is_parent) {
                    $files = ms_get_slot_files($slot->id);
                    $videos = ms_get_slot_videos($slot->id, 'local');
                    $html .= '<td class="file_list_column" style="width:1px;">';
                    foreach ($files as $file) {
                        $html .= '<a target="_blank" href="/' . $presentation_dir . sanitize_file_name($file) .'"><i class="fa-2x fa ' . get_file_icon_class($file) . '"></i></a>&nbsp;';
                    }
                    foreach ($videos as $video) {
                        $html .= '<a target="_blank" href="' . esc_url($video->video_url) . '"><i class="fa fa-2x fa-file-video-o"></i></a>&nbsp;';
                    }
                    $html .= '</td>';
                }
                // td for ratings, don't show if ratings aren't enabled for this slot for whatever reason
                if ($ratings_enabled && ! $is_parent) {
                    $html .= '<td style="width: 1px;">' . ms_get_my_presentation_rating_button($slot->id, $this->auth) . '</td>';
                }
                $html .= '</tr>';
                // process children for this slot
                foreach ($children as $child_slot) {
                    if (mps_get_option('global_ratings') == 'on' && $session->is_rateable && $child_slot->ratable) {
                        $ratings_enabled = true;
                    } else {
                        $ratings_enabled = false;
                    }
                    if (substr($slot->content, 0, 3) !== '<p>') {
                        $content = '<p>' . $child_slot->content . '</p>';
                    } else {
                        $content = $child_slot->content;
                    }
                    if ($ratings_enabled) {
                        $colspan = 1;
                    } else {
                        $colspan = 2;
                    }
                    $html .= '<tr>';
                    $html .= '<td colspan="' . $colspan . '">' . $content . '</td>';

                    $files = ms_get_slot_files($child_slot->id);
                    $videos = ms_get_slot_videos($child_slot->id, 'local');
                    $html .= '<td class="file_list_column" style="width:1px;">';
                    foreach ($files as $file) {
                        $html .= '<a target="_blank" href="/' . $presentation_dir . sanitize_file_name($file) .'"><i class="fa-2x fa ' . get_file_icon_class($file) . '"></i></a>&nbsp;';
                    }
                    foreach ($videos as $video) {
                        $html .= '<a target="_blank" href="' . esc_url($video->video_url) . '"><i class="fa fa-2x fa-file-video-o"></i></a>&nbsp;';
                    }
                    $html .= '</td>';
                    // td for ratings, don't show if ratings aren't enabled for this slot for whatever reason
                    if ($ratings_enabled) {
                        $html .= '<td style="width: 1px;">' . ms_get_my_presentation_rating_button($child_slot->id, $this->auth) . '</td>';
                    }
                    $html .= '</tr>';
                }
            }
            if (count($slots) > 0) {
                $html .= '</table>';
            }
        }

        $html .= '</table>';

        $html .= ms_slot_rating_modal();

        return $html;
    }

    public function ms_session_table_vertical()
    {

        $sessions = mps_get_all_sessions();
        $current_day = '';
        $vertical_sessions = array();
        $time_increments = mps_get_option('meeting_increments', 15); //minutes

        $onwards_time = date('H:i', strtotime('midnight -' . $time_increments . ' minutes'));

        // Re-sort the sessions to be more for the vertical-style layout
        foreach ($sessions as $session) {
            $vertical_sessions[date('YmdHis', strtotime($session->start_time))][] = $session;
        }

        $html = '';

        foreach ($vertical_sessions as $timeslots) {
            $tidy_day = date('l, j F', strtotime($timeslots[0]->start_time));
            // Is it a new day?
            if ($tidy_day !== $current_day) {
                if ($html != '') {
                    $html .= '</table>';
                }
                $html .= '<table class="sessions-vertical">';
                $html .= '<colgroup><col width="14%"><col width="43%"><col width="43%"></colgroup>';
                $html .= '<thead><tr><th colspan="3">' . $tidy_day . '</th></tr></thead>';
                $current_day = $tidy_day;
            }
            $html .= '<tr>';
            $html .= '<td>';
            if (date('H:i', strtotime($timeslots[0]->end_time)) == $onwards_time) {
                $html .= date('H:i', strtotime($timeslots[0]->start_time)) . '-' . __('onwards', 'meeting-support');
            } else {
                $html .= date('H:i', strtotime($timeslots[0]->start_time)) . '&#8209;' . date('H:i', strtotime($timeslots[0]->end_time));
            }
                $html .= '</td>';
            foreach ($timeslots as $session) {
                $slots = ms_get_session_slots($session->id);
                if (count($timeslots) > 1) {
                    $html .= '<td colspan="1">';
                } else {
                    $html .= '<td colspan="2">';
                }
                $html .= esc_html($session->name) . '<br>';
                if ($session->room !== '_') {
                    $html .= '<i>' . get_real_room_name($session->room) . '</i>';
                }
                foreach ($slots as $slot) {
                    // If the content doesnt start wth a p tag, let's wrap it
                    if (substr($slot->content, 0, 3) !== '<p>') {
                            $html .= '<p>' . $slot->content . '</p>';
                    } else {
                        $html .= $slot->content;
                    }
                }
                    $html .= '</td>';
            }
                $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    public function ms_session_table($atts)
    {
        $time_colspan = 3;

        extract(shortcode_atts(array(
            'showsponsors' => false,
            'showday' => false
        ), $atts));

        if ($showsponsors == 'false') {
            $showsponsors = false;
        }
        if ($showday != false) {
            if ($showday == 'auto') {
                $showday = date('Y-m-d');
            }
            $time_colspan = 2;
        }

        $meeting_timezone = mps_get_option('meeting_timezone');

        if ($showday) {
            if ($showday < mps_get_option('meeting_start_date') || ( $showday > mps_get_option('meeting_end_date') )) {
                return '';
            }
            $meeting_start_day = $showday;
            $meeting_end_day = $showday;
        } else {
            $meeting_start_day = mps_get_option('meeting_start_date');
            $meeting_end_day = mps_get_option('meeting_end_date');
        }

        $meeting_length_seconds = abs(strtotime($meeting_end_day) - strtotime($meeting_start_day));
        $meeting_length_days = ( $meeting_length_seconds / 60 / 60 / 24) + 1;

        $agenda_start_time = mps_get_option('agenda_start_time');
        $agenda_end_time = mps_get_option('agenda_end_time');

        $time_increments = mps_get_option('meeting_increments', 15); //minutes

        $max_concurrent_sessions = 3;

        $rooms = mps_get_option('rooms');

        $intermission_config = mps_get_option('intermission_config');

        if (! $meeting_timezone || ! $meeting_start_day || ! $meeting_end_day || ! $agenda_start_time || ! $agenda_end_time || ! $rooms || ! $intermission_config) {
            mps_log('Missing configuration, not rendering ms_session_table()');
            return false;
        }

        // TODO Calculate the lowest common multiple properly from $max_concurrent_sessions
        $lcm = 12;

        $calendar_columns = $lcm * $meeting_length_days;

        // Make datetimes objects for us to iterate with
        $timeslot = new DateTime($meeting_start_day . ' ' . $agenda_start_time, new DateTimeZone($meeting_timezone));
        $dateslot = new DateTime($meeting_start_day . ' ' . $agenda_start_time, new DateTimeZone($meeting_timezone));

        $agenda_start = new DateTime($meeting_start_day . ' ' . $agenda_start_time, new DateTimeZone($meeting_timezone));
        $agenda_end = new DateTime($meeting_start_day . ' ' . $agenda_end_time, new DateTimeZone($meeting_timezone));

        $meeting_start = new DateTime($meeting_start_day . ' ' . $agenda_start_time, new DateTimeZone($meeting_timezone));
        $meeting_end = new DateTime($meeting_end_day . ' ' . $agenda_end_time, new DateTimeZone($meeting_timezone));

        $now = new DateTime('now', new DateTimeZone($meeting_timezone));

        $meeting_is_active = false;
        if ($meeting_start < $now && $now < $meeting_end) {
            $meeting_is_active = true;
        }

        $return = '';
        $return .= '<table class="agendaTable">';
        // Iterate over all time slots, just adding $time_increments minutes each time
        $return .= '<thead>';
        $return .= '<tr>';
        $return .= '<th colspan="'.$time_colspan.'" class="rowTime">&nbsp;</th>';
        for ($i = $meeting_length_days; $i > 0; $i--) {
                $return .= '    <th colspan="' . $lcm . '"><b>' . $dateslot->format('l') . '</b><br>' . $dateslot->format('j F') . '</th>';
                $dateslot->modify('+1 day');
        }
        unset($dateslot);
        $dateslot = $agenda_start;
        $return .= '    </tr>';
        $return .= '</thead>';
        $return .= '<tbody>';

        for ($timeslot; $timeslot <= $agenda_end; $timeslot->modify('+'.$time_increments.' minutes')) {
            $return .= '<tr>';
            // First Column
            $is_now_class = '';
            $this_time_incremenent_end = new DateTime($timeslot->format('Y-m-d H:i:s'), new DateTimeZone(mps_get_option('meeting_timezone')));
            $this_time_incremenent_end->modify('+'.$time_increments.' minutes');
            if ($meeting_is_active) {
                if ($timeslot->format('His') < $now->format('His') && $this_time_incremenent_end->format('His') > $now->format('His')) {
                    $is_now_class = ' class="is_now"';
                }
            }
            if ($timeslot->format('i') % 30 == 0) {
                $return .= '<td ' . $is_now_class . ' colspan="'.$time_colspan.'">'.$timeslot->format('H:i').'</td>';
            } else {
                $return .= '<td ' . $is_now_class . ' colspan="'.$time_colspan.'">&nbsp;<!-- ' . $timeslot->format('H:i') . ' --></td>';
            }
            // Loop through the number of days
            for ($i = $meeting_length_days; $i > 0; $i--) {
                // Are there any sessions running at this time?
                $this_slot = new DateTime($dateslot->format('Y-m-d') . ' ' . $timeslot->format('H:i'));
                $sessions_now = mps_sessions_at_datetime($this_slot);
                if ($sessions_now) {
                    // If the session has started at this slot, then we need to add it to the table
                    if ($sessions_now[0]->start_time == $this_slot->format('Y-m-d H:i:s')) {
                        $session_length_seconds = (strtotime($sessions_now[0]->end_time) - strtotime($sessions_now[0]->start_time) );
                        $session_length_minutes = $session_length_seconds / 60;
                        // Don't let sessions overflow from the table, in case we need to add stuff after (like Sponsors)
                        if (date('H:i', strtotime($sessions_now[0]->end_time)) > $agenda_end_time) {
                            $session_length_seconds = (strtotime(substr($sessions_now[0]->end_time, 0, -8) . $agenda_end_time . ':00') - strtotime($sessions_now[0]->start_time));
                            $session_length_minutes = $session_length_seconds / 60;
                        }
                        $rowspan = $session_length_minutes / $time_increments;
                        foreach ($sessions_now as $session_now) {
                            if ($session_now->is_intermission) {
                                $return .= '<td style="background-color: ' . esc_html($intermission_config['colour']) . ';" colspan="' . ( $lcm / count($sessions_now) ) . '" rowspan="' . $rowspan . '">';
                                $return .= '<span style="color: ' . esc_html($intermission_config['text_colour']) . ';">';
                                $return .= ( $session_now->hide_title == 0 ? ( esc_html(stripslashes($session_now->name)) ) : '' );
                                $return .= '</span>';
                                $return .= '</td>';
                            } else {
                                if ($session_now->url == '') {
                                    $return .= '<td style="background-color: ' . esc_html($rooms[$session_now->room]['colour']) . '" colspan="' . ( $lcm / count($sessions_now) ) . '" rowspan="' . $rowspan . '">';
                                    $return .= '<span style="color: ' . esc_html($rooms[$session_now->room]['text_colour']) . '">';
                                    $return .= ( $session_now->hide_title == 0 ? ( esc_html(stripslashes($session_now->name)) ) : '' );
                                    $return .= '</span>';
                                } else {
                                    $return .= '<td class="active-agenda-item" style="background-color: ' . esc_html($rooms[$session_now->room]['colour']) . '" colspan="' . ( $lcm / count($sessions_now) ) . '" rowspan="' . $rowspan . '">';
                                    $return .= '<a href="' . $session_now->url . '" style="color: ' . esc_html($rooms[$session_now->room]['text_colour']) . ' !important;">';
                                    $return .= ( $session_now->hide_title == 0 ? ( esc_html(stripslashes($session_now->name)) ) : '' );
                                    $return .= '</a>';
                                }
                                // If this session is running RIGHT NOW, and is streamed, then put a link in
                                if ($session_now->start_time < $now->format('Y-m-d H:i:s') && $session_now->end_time > $now->format('Y-m-d H:i:s')) {
                                    if ($session_now->is_streamed) {
                                        $return .= ' ';
                                        $return .= '<a target="_blank" title="Watch the live stream" style="color: green" href="' . home_url() . '/live/' . $session_now->room . '/"><i class="fa fa-video-camera"></i></a>';
                                    }
                                }
                            }
                        }
                    }
                    // If this slot is not the start of the session, then we need to print nothing, as the row has been filled by the session starting colspan
                } else {
                    // Is not currently part of a session, so let's put a <td> in
                    $return .= '<td colspan="' . $lcm . '">&nbsp;</td>';
                }
                $dateslot->modify('+1 day');
            }
            $dateslot->modify('-' . $meeting_length_days . ' days');
            // Reset the $dateslot back to the first day
            $return .= '</tr>';
        }

        $return .= '<tr class="sponsorhead">';
        $return .= '<td colspan="' . $time_colspan . '"></td>';
        for ($i = $meeting_length_days; $i > 0; $i--) {
            $sponsor_config = mps_get_option('sponsor_day_' . $dateslot->format('Y-m-d'), array());
            if (! isset($sponsor_config['title']) || $sponsor_config['title'] == '') {
                $return .= '<td colspan="' . $lcm . '"></td>';
            } else {
                $return .= '<td class="sponsorcells" colspan="' . $lcm . '">';
                if ($sponsor_config['title_url'] != '') {
                    $return .= '<a href="' . $sponsor_config['title_url'] . '">';
                }
                $return .= $sponsor_config['title'];
                if ($sponsor_config['title_url'] != '') {
                    $return .= '</a>';
                }
                $return .= '</td>';
            }
                $dateslot->modify('+1 day');
        }
        $dateslot->modify('-' . $meeting_length_days . ' days');

        $return .= '</tr>';

        $return .= '<tr>';
        $return .= '<td colspan="' . $time_colspan . '"></td>';
        for ($i = $meeting_length_days; $i > 0; $i--) {
            $sponsor_config = mps_get_option('sponsor_day_' . $dateslot->format('Y-m-d'), array());
            if (! isset($sponsor_config['title']) || $sponsor_config['title'] == '') {
                $return .= '<td colspan="' . $lcm . '"></td>';
            } else {
                $return .= '<td class="sponsorcells" colspan="' . $lcm . '">';
                $return .= $sponsor_config['body'];
                $return .= '</td>';
            }

                $dateslot->modify('+1 day');
        }
        $dateslot->modify('-' . $meeting_length_days . ' days');

        $return .= '</tr>';

        $return .= '</tbody>';

        $return .= '</table>';

        return $return;
    }

    public function ms_session_table_responsive($atts)
    {
        /**
         * Function to print both the full-width agenda, and a mobile-friendly version.
         * Along with CSS/JS used to automatically switch between the 2, and tab functionality for the mobile view
         */
        extract(shortcode_atts(array(
            'break' => '800',
        ), $atts));


        $meeting_start_date = new DateTime(mps_get_option('meeting_start_date'));
        $meeting_end_date = new DateTime(mps_get_option('meeting_end_date'));
        $meeting_end_date->modify('+1 day');
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($meeting_start_date, $interval, $meeting_end_date);

        $print = '';
        // Non-responsive
        $print .= do_shortcode('[session_table]');
        // Responsive
        $print .= '<div id="meeting-agenda-responsive" style="display: none;" class="boot">';

        $print .= '<ul class="nav nav-tabs" role="tablist">';
        $meeting_active = false;
        $i = 0;
        foreach ($period as $day) {
            // Use this loop to decide if the meeting is currently running and if so, what day it's on.
            if ($day->format('Ymd') == date('Ymd')) {
                $meeting_active = $day->format('Ymd');
            }
            $print .= '<li ' . ($i == 0 ? 'class="active"' : '') . ' role="presentation"><a href="#' . $day->format('Ymd') . '" aria-controls="' . $day->format('D') . '" role="tab" data-toggle="tab">' . $day->format('D') . '</a></li>';
            $i++;
        }
        $print .= '</ul>';
        $print .= '<div class="tab-content">';
        $i = 0;
        foreach ($period as $day) {
            $print .= '<div role="tabpanel" class="tab-pane ' . ($i == 0 ? 'active' : '') . '" id="' . $day->format('Ymd') . '">';
            $print .= do_shortcode('[session_table showday="' . $day->format('Y-m-d') . '"]');
            $print .= '</div>';
            $i++;
        }
        $print .= '</div>';

        $print .= '</div>';

        // Styling and css
        $print .= '
        <style>
        @media only screen and (max-width: ' . $break . 'px) {
            .agendaTable {
                display: none;
            }

            .agendaTable tr {
                line-height: 12px;
            }

            #meeting-agenda-responsive .agendaTable,
            #meeting-agenda-responsive {
                display: table !important;
                table-layout: fixed;
            }
        }
        </style>';

        if ($meeting_active) {
            $print .= "
            <script type='text/javascript'>
                jQuery(document).ready(function() {
                    if (window.location.hash == '') {
                        jQuery('.nav-tabs a[href=\"#" . $meeting_active . "\"]').tab('show');
                    }
                });
            </script>";
        }

        return $print;
    }

    public function ms_session_table_legend()
    {

        $rooms = mps_get_option('rooms');
        $return = '<table class="table">';
        $return .= '<tbody>';

        foreach ($rooms as $room) {
            if ($room['in_legend'] == 1) {
                $return .= '<tr><td style="color: ' . $room['text_colour'] . '; background-color: ' . $room['colour'] . ';" class="legenditem">'. $room['long'] .'</td></tr>';
            }
        }

        $return .= '</tbody>';
        $return .= '</table>';

        return $return;
    }

    public function ms_show_attendee_list($atts)
    {

        $countries = mps_countries_abbreviations();
        $attendee_list_url = "https://ba-apps.ripe.net/meeting-rest-api/meetings/";

        extract(shortcode_atts(array(
        'id' => '',
        ), $atts));

        $show_details = false;

        if ($_GET['details'] && ! empty($this->auth->user)) {
            $show_details = true;
        }

        $json_url = $attendee_list_url.$id."/attendees?o=json";
        $current_url = get_permalink();
        $attendee_status = array(
            'Open' => '',
            'Canceled' => "",
            'Checked in' => '<i title="Checked in" style="color:green;" class="fa fa-check"></i>',
            'No show' => '<i title="No show" style="color:red;" class="fa fa-times"></i>',
        );

        $json = file_get_contents($json_url);
        $attendees = json_decode($json, true);
        $output = "";

        $tableid = "attendeeTable";

        if (! empty($this->auth->user)) {
            if ($show_details) {
                $output .= '<div class="boot"><a class="btn btn-default nounderline pull-right" href="' . remove_query_arg('details') . '">Hide attendee contact details and profile picture</a></div><div class="clear"></div><br>';
                $tableid = 'attendeeTableDetails';
            } else {
                $output .= '<div class="boot"><a class="btn btn-default nounderline pull-right" href="' . add_query_arg('details', true) . '">View attendee contact details and profile picture</a></div><div class="clear"></div><br>';
            }
        }

        $output .= '<div class="boot">';
        $output .= '<table id="' . $tableid . '" class="table table-striped">';
        $output .= "  <thead>\n";
        $output .= "    <tr>\n";
        $output .= '      <th></th>';
        $output .= '      <th>' . __('First Name', 'meeting-support') . '</th>';
        $output .= '      <th>' . __('Last Name', 'meeting-support') . '</th>';
        $output .= '      <th>' . __('Organisation', 'meeting-support') . '</th>';
        $output .= '      <th><span style="padding-right:5px;">' . __('Country', 'meeting-support') . '</span></th>';
        $output .= '      <th><span style="padding-right:5px;">' . __('Status', 'meeting-support') . '</span></th>';
        $output .= '      <th><span style="padding-right:5px;">' . __('ASN', 'meeting-support') . '</span></th>';
        if ($show_details) {
            $output .= '      <th><span style="padding-right:5px;"><i class="fa fa-camera"></i></span></th>';
            $output .= '      <th><span style="padding-right:5px;"><i style="padding-right:5px;" class="fa fa-envelope"></i></span></th>';
        }
        $output .= "    </tr>\n";
        $output .= "  </thead>\n";
        $output .= "  <tbody>\n";

        if ($attendees) {
            $nlines = 0;
            foreach ($attendees as $atd) {
                if ($atd['status'] !== 'Canceled') {
                    // Odd/even
                    $nlines++;
                    // Name
                    $firstname = sanitize_text_field($atd['firstName']);
                    $lastname = sanitize_text_field($atd['lastName']);

                    // Turn country name into country code, or keep as it is if it does not exist
                    $country = sanitize_text_field($countries[$atd['country']]);
                    if (empty($country)) {
                        $country = '';
                    }

                    // Details
                    $photo = "";
                    $email = "";
                    $asn = "";

                    if ($show_details) {
                        if (is_array($atd['social'])) {
                            if ($atd['social']['publishEmail'] == '1') {
                                $email = '<a href="mailto:' . sanitize_email($atd['social']['email']) . '"><i class="fa fa-envelope"></i></a>';
                                if (!empty($atd['urlPicture'])) {
                                    $atd['urlPicture'] = preg_replace("/^http/", "https", $atd['urlPicture']);
                                    $photo = '<span style="display:none;">' . $atd['urlPicture'] . '</span><img class="attendeeimg" src="' . $atd['urlPicture'] . '"/>';
                                }
                            }
                        }
                    }

                    // Throw together all ASNs, if there
                    if (isset($atd['asns'])) {
                        // Let's make them into hrefs
                        array_walk($atd['asns'], function (&$value, &$key) {

                            $value = '<a title="'. __('View on RIPEstat', 'meeting-support') . '" target="_blank" href="https://stat.ripe.net/' . sanitize_text_field($value) . '">' . sanitize_text_field($value) . '</a>';
                        });

                        $asn = implode(' ', $atd['asns']);
                    }

                    $output .= "    <tr>\n";
                    $output .= "      <td></td>\n";
                    $output .= "      <td>" . $firstname . "</td>\n";
                    $output .= "      <td>" . $lastname . "</td>\n";
                    $output .= "      <td>" . $atd['organisation'] . "</td>\n";
                    $output .= "      <td>" . $country . "</td>\n";
                    $output .= '<td data-search="' . sanitize_text_field($atd['status']) . '">' . $attendee_status[$atd['status']] . '</td>';
                    $output .= "      <td>" . $asn . "</td>\n";
                    if ($show_details) {
                        $output .= "      <td>" . $photo . "</td>\n";
                        $output .= "      <td>" . $email . "</td>\n";
                    }
                    $output .= "    </tr>\n";
                }
            }
        }

        $output .= "  </tbody>\n";
        $output .= "</table>\n";
        $output .= '</div>';

        return $output;
    }

    public function ms_submission_form()
    {

        $submission_id = 0;
        if (isset($_GET['edit_submission'])) {
            $submission_id = (int) $_GET['edit_submission'];
        }

        $allowed_files = mps_get_option('allowed_file_types', array());

        // Get max file upload allowed in megabytes
        $max_file_upload = getMaximumFileUploadSize() / 1024 / 1024;

        $pc_config = pc_config();

        $submissiontypes = $pc_config['submission_types'];

        $submission = false;

        // If we are editing an existing submission, let's grab that one.
        if ($submission_id > 0) {
            if (empty($this->auth->user)) {
                return '<div class="boot"><div class="alert alert-danger">' . __('You must be logged in to edit an existing submission', 'meeting-support') . '</div></div>';
            }

            $submission = mps_get_submission($submission_id);

            if (! $submission) {
                return '<div class="boot"><div class="alert alert-danger">' . __('Submission not found', 'meeting-support') . '</div></div>';
            }

            if ($submission->author_uuid != $this->auth->user['uuid'] && ( ! pcss_user_can('edit_submission', $this->auth) )) {
                return '<div class="boot"><div class="alert alert-danger">' . __('You do not have permission to edit this submission', 'meeting-support') . '</div></div>';
            }

            // If we want to delete the submission, we should check for a $_GET['delete'] flag
            if (isset($_GET['delete'])) {
                $output = '';
                $output .= '<div class="boot">';
                $output .= mps_flash('mps_pc_submission');
                $output .= '<div class="alert alert-danger">';
                $output .= __('Are you sure you want to delete this submission?', 'meeting-support');
                $output .= '<form method="POST" class="pull-right form-horizontal" action="' . admin_url('admin-post.php') . '">';
                $output .= '<input type="hidden" name="action" value="mps_pc_submission">';
                $output .= '<input type="hidden" name="submission_id" value="' . $submission_id . '">';
                $output .=  wp_nonce_field('mps_pc_submission', '_wpnonce', true, false);
                $output .= '<input type="hidden" name="delete" value="true">';
                $output .= '<input class="btn btn-danger btn-xs" type="submit" value="' . __('Delete', 'meeting-support') . '"> ';
                $output .= '<a href="' . home_url('submit-topic/your-submissions') . '" class="btn btn-primary btn-xs">' . __('Cancel', 'meeting-support') . '</a>';
                $output .= '</form>';
                $output .= '</div>';
                $output .= '</div>';
                return $output;
            }
        }

        if (! $this->auth->user) {
            // User isn't logged in, so we're gonna present them with a captcha
            $captcha = new Gregwar\Captcha\CaptchaBuilder;
            $captcha->setDistortion(true);
            $captcha->setIgnoreAllEffects(true);
            $captcha->build();

            $_SESSION['phrase'] = $captcha->getPhrase();
        }

        $output = '';

        $output .= '<div class="boot">';
        $output .= mps_flash('mps_pc_submission');

        $output .= '<div class="clear"></div>';
        $output .= '<form enctype="multipart/form-data" method="POST" class="form-horizontal" role="form" action="' . admin_url('admin-post.php') . '">';
        $output .= '<input type="hidden" name="action" value="mps_pc_submission">';
        $output .= '<input type="hidden" name="submission_id" value="' . $submission_id . '">';
        $output .=  wp_nonce_field('mps_pc_submission', '_wpnonce', true, false);
        $output .= '<div class="form-group">';
        $output .= '<label for="subtype" class="col-xs-3 control-label">' . __('Submission Type', 'meeting-support') . '</label>';
        $output .= '<div class="col-xs-7">';
        $output .= '<select required name="subtype" class="form-control" id="subtype">';
        $output .= '<option></option>';
        foreach ($submissiontypes as $submission_type_id => $submission_name) {
            if ($submission && $submission->submission_type == $submission_type_id) {
                $output .= '<option selected value="'. $submission_type_id .'">'.$submission_name.'</option>';
            } else {
                $output .= '<option value="'. $submission_type_id .'">'.$submission_name.'</option>';
            }
        }
        $output .= '</select>';
        $output .= '</div>';
        $output .= '<small><i class="fa fa-asterisk"></i></small>';
        $output .= '</div>';
        $output .= '<div class="clear"></div>';
        $output .= '<div class="form-group">';
        $output .= '<label for="authorname" class="col-xs-3 control-label">' . __('Author Name', 'meeting-support') . '</label>';
        $output .= '<div class="col-xs-7">';
        if ($submission) {
            $output .= '<input value="' . sanitize_text_field($submission->author_name) . '" required type="text" id="authorname" name="authorname" class="form-control">';
        } else {
            $output .= '<input value="' . sanitize_text_field($this->auth->user['name']) . '" required type="text" id="authorname" name="authorname" class="form-control">';
        }
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';

            $output .= '<div class="form-group">';
            $output .= '<label for="authoraffiliation" class="col-xs-3 control-label">' . __('Affiliation', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
        if ($submission) {
            $output .= '<input value="' . sanitize_text_field($submission->author_affiliation) . '" type="text" id="authoraffiliation" name="authoraffiliation" class="form-control">';
        } else {
            $output .= '<input type="text" id="authoraffiliation" name="authoraffiliation" class="form-control">';
        }
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';

            $output .= '<div class="form-group">';
            $output .= '<label for="authoremail" class="col-xs-3 control-label">' . __('Email Address', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
        if ($submission) {
            $output .= '<input required value="' . sanitize_text_field($submission->author_email) . '" type="email" name="authoremail" class="form-control" id="authoremail">';
        } else {
            $output .= '<input required value="' . sanitize_text_field($this->auth->user['email']) . '" type="email" name="authoremail" class="form-control" id="authoremail">';
        }
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<div class="form-group">';
            $output .= '<label for="submissiontitle" class="col-xs-3 control-label">' . __('Submission Title', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
        if ($submission) {
            $output .= '<input value="' . sanitize_text_field($submission->submission_title) . '" required type="text" name="submissiontitle" class="form-control" id="submissiontitle">';
        } else {
            $output .= '<input required type="text" name="submissiontitle" class="form-control" id="submissiontitle">';
        }
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<div class="form-group">';
            $output .= '<label for="submissionabstract" class="col-xs-3 control-label">' . __('Submission Abstract', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
        if ($submission) {
            $output .= '<textarea style="height:auto;" rows="4" name="submissionabstract" class="col-xs-7 form-control" id="submissionabstract">' . escape_multiline_text($submission->submission_abstract) . '</textarea>';
        } else {
            $output .= '<textarea style="height:auto;" rows="4" name="submissionabstract" class="col-xs-7 form-control" id="submissionabstract"></textarea>';
        }
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<div class="form-group">';
            $output .= '<label for="authorcomments" class="col-xs-3 control-label">' . __('Author Comments', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
        if ($submission) {
            $output .= '<textarea placeholder="" style="height:auto;" rows="4" name="authorcomments" class="col-xs-7 form-control" id="authorcomments">' . escape_multiline_text($submission->author_comments) . '</textarea>';
        } else {
            $output .= '<textarea placeholder="" style="height:auto;" rows="4" name="authorcomments" class="col-xs-7 form-control" id="authorcomments"></textarea>';
        }
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<div class="form-group">';
            $output .= '<label for="submissionurl" class="col-xs-3 control-label">' . __('Submission URL', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
        if ($submission) {
            $output .= '<input type="text" value="' . sanitize_text_field($submission->submission_url) . '" name="submissionurl" class="form-control" id="submissionurl">';
        } else {
            $output .= '<input type="text" name="submissionurl" class="form-control" id="submissionurl">';
        }
            $output .= '</div>';
            $output .= '</div>';

        // CAPTCHA if not logged in
        if (! $this->auth->user) {
            $output .= '<div class="clear"></div>';
            $output .= '<div class="form-group">';
            $output .= '<label for="submissionurl" class="col-xs-3 control-label">' . __('Verification Text', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
            $output .= '<div class="captcha"><img src="' . $captcha->inline() . '" /></div>';
            $output .= '<input required class="form-control" placeholder="' . __('Verification Text', 'meeting-support') . '" name="captcha" type="text">';
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';
        }
        // End CAPTCHA

        $output .= '<div class="clear"></div>';
        $output .= '<div class="form-group">';
        $output .= '<label for="submissionupload" class="col-xs-3 control-label">' . __('Submission File', 'meeting-support') . '</label>';
        $output .= '<div class="col-xs-7">';
        $output .= '<input type="file" name="submissionupload" id="submissionupload">';
        if ($submission && $submission->filename) {
            $output .= '<span class="label label-warning">' . __('A file is currently uploaded. Leave empty to keep original file', 'meeting-support') . '</span>';
        } else {
            $output .= '<span class="label label-warning os-agnostic-warning"><i class="fa fa-info-circle"></i> ' . __('The Programme Committee favours submissions in PDF format', 'meeting-support') . '</span>';
        }
        $output .= '</div>';
        $output .= '<small><i id="file_upload_required" class="fa fa-asterisk"></i></small>';
        $output .= '</div>';
        $output .= '<div class="clear"></div>';
        $output .= '<br>';
        $output .= '<small class="uploadinfo">' . __('Maximum file size:', 'meeting-support') . ' ' . $max_file_upload . ' MB.<br>';
        $output .= __('Accepted file types:', 'meeting-support') . ' ' . implode(', ', $allowed_files) . '</small>';
        $output .= '<div class="clear"></div>';
        $output .= '<div style="display:none" id="bofworkshopwarning" class="alert alert-danger">' . __('Important note: BoFs and Workshops will not receive scribing, webcast or stenography support.', 'meeting-support') . '</div>';
        $output .= '<small><i class="fa fa-asterisk"></i>' . __('= required field', 'meeting-support') . '</small>';
        $output .= '<div class="form-group">';
        $output .= '<div class="col-xs-offset-8 col-xs-2">';
        $output .= '<button type="submit" class="customsubmitbtn pull-right btn btn-success">' . __('Submit', 'meeting-support') . '</button>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</form>';
        $output .= '</div>';

        return $output;
    }


    public function ms_show_session_list($atts)
    {

        $toshow = array();

        $output = '';

        $rooms = mps_get_option('rooms', array());
        $reset = date_default_timezone_get();
        date_default_timezone_set(mps_get_option('meeting_timezone'));

        // Extract parameters from attributes
        extract(shortcode_atts(array(
            'day' => '',
            'room' => '',
        ), $atts));

        $sessions = mps_get_all_sessions();
        if (empty($sessions)) {
            return '<p>The session list is empty.</p>';
        }

        if ($day == 'auto') {
            $day = date('Y-m-d');
        }

        foreach ($sessions as $session) {
            // Are we matching a session that we want to show?
            if (strpos($session->start_time, $day) !== false) {
                $toshow[] = $session;
            }
        }

        $meeting_now = new DateTime('now', new DateTimeZone(mps_get_option('meeting_timezone')));

        unset($session);
        $output .= '<h3>' . date('l j F', strtotime($day)) . '</h3>';
        if (empty($toshow)) {
            $output = "<p>The session list is empty.</p>";
        } else {
            $output .= '<ul>';
            foreach ($toshow as $session) {
                if ($room !== '') {
                    // If we want to show a specific room
                    if ($session->room != $room) {
                        continue;
                    }
                }
                // Dont show intermissions or sessions with no name (yet)
                if (trim($session->name) == '' || $session->is_intermission == 1 || $session->is_social) {
                    continue;
                }

                if ($session->start_time < $meeting_now->format('Y-m-d H:i:s') && $session->end_time > $meeting_now->format('Y-m-d H:i:s')) {
                    $session_is_now = true;
                } else {
                    $session_is_now = false;
                }

                if ($session_is_now) {
                    $output .= '<b>';
                }

                if ($session->url == '') {
                    $output .= '<li>' . date('H:i', strtotime($session->start_time)) . ' - ' . date('H:i', strtotime($session->end_time)) . ' - ' . stripslashes($session->name) . ' (' . $rooms[$session->room]['long'] . ')</li>';
                } else {
                    $output .= '<li>' . date('H:i', strtotime($session->start_time)) . ' - ' . date('H:i', strtotime($session->end_time)) . ' - <a href="'.$session->url.'">' . stripslashes($session->name) . '</a> (' . $rooms[$session->room]['long'] . ')</li>';
                }

                if ($session_is_now) {
                    $output .= '</b>';
                }

                if ($session_is_now) {
                    $slots = ms_get_session_slots($session->id);
                    if ($slots) {
                        $output .= '<ul>';
                    }
                    foreach ($slots as $slot) {
                        if ($slot->parent_id != 0) {
                            continue;
                        }
                        $slot_children = ms_get_slot_children($slot->id);
                        $output .= '<li>';
                        $output .= $slot->content;
                        if (count($slot_children) > 0) {
                            $output .= '<ul>';
                        }
                        foreach ($slot_children as $slot_child) {
                            $output .= '<li>' . $slot_child->content . '</li>';
                        }
                        if (count($slot_children) > 0) {
                            $output .= '</ul>';
                        }
                        $output .= '</li>';
                    }
                    if ($slots) {
                        $output .= '</ul>';
                    }
                }
            }
            $output .= '</ul>';
        }
        date_default_timezone_set($reset);

        return $output;
    }

    public function ms_show_submissions($atts)
    {

        // Display the list of uploaded submissions
        $output = '';
        // Extract parameters from attributes
        extract(
            shortcode_atts(
                array(
                'pdfonly' => false,
                'ratings' => false,
                'useronly' => false,
                'type' => '',
                'style' => 0,
                'intro' => '',
                ),
                $atts
            )
        );


        $user = $this->auth->user;
        $pc_config = pc_config();
        $statuses = $pc_config['submission_status'];

        if ($type == 'Tagged') {
            if (isset($_GET['tagged']) && ! empty($_GET['tagged'])) {
                mps_flash('mps_pc_submission', 'Showing submissions tagged with: <b>' . sanitize_text_field($_GET['tagged']) . '</b>. <a href="' . get_permalink() . '" class="pull-right btn btn-xs btn-default">Clear Tag Filter</a>', 'info');
            } else {
                mps_flash('mps_pc_submission', 'Showing all tagged submissions', 'info');
            }
        }

        if (! pcss_user_can('signed_in', $this->auth)) {
            return false;
        }

        // Display list of submissions, if any
        if (! pcss_user_can('view_all_submissions', $this->auth) || ( $useronly )) {
            $submissions = mps_get_own_submissions();
        } else {
            $submissions = mps_get_all_submissions();
        }

        if (pcss_user_can('sort_submission', $this->auth)) {
            if (isset($_POST['sort_form']) && $_POST['sort_form'] == 1) {
                foreach ($submissions as $key => $submission) {
                    // Handle if they want to see a specific submission status
                    if ($_POST['sort_status'] != '') {
                        if ($submission->submission_status != $_POST['sort_status']) {
                            unset($submissions[$key]);
                        }
                    }
                    // Handle if they want to see rated/unrated
                    if ($_POST['sort_my'] != '') {
                        if ($_POST['sort_my'] == 'unratedsubs') {
                            if (ms_user_has_rated($submission->id)) {
                                unset($submissions[$key]);
                            }
                        }
                        if ($_POST['sort_my'] == 'ratedsubs') {
                            if (!ms_user_has_rated($submission->id)) {
                                unset($submissions[$key]);
                            }
                        }
                    }
                }
                if ($_POST['sort_by'] && $_POST['sort_order']) {
                    switch ($_POST['sort_by']) {
                        case 'avgcontent':
                            foreach ($submissions as $submission) {
                                    $submission->sort = ms_get_average_content_rating($submission->id);
                            }
                            break;
                        case 'avgpresenter':
                            foreach ($submissions as $submission) {
                                    $submission->sort = ms_get_average_presenter_rating($submission->id);
                            }
                            break;
                        case 'submissiondate':
                            foreach ($submissions as $submission) {
                                    $submission->sort = strtotime($submission->submission_date);
                            }
                            break;
                        case 'submissionauthor':
                            foreach ($submissions as $submission) {
                                    $submission->sort = strtolower($submission->author_name);
                            }
                            break;
                        case 'submissiontitle':
                            foreach ($submissions as $submission) {
                                    $submission->sort = strtolower($submission->submission_title);
                            }
                            break;
                    }
                    switch ($_POST['sort_order']) {
                        case 'ascending':
                            uasort($submissions, 'ms_cmp_submissions');
                            break;
                        case 'descending':
                            uasort($submissions, 'ms_cmp_submissions_r');
                            break;
                    }
                }
            }
        }

        // If we want to view a certain type of submission
        if ($type) {
            if ($type != 'Tagged') {
                foreach ($submissions as $id => $sub) {
                    if (strtolower($type) != strtolower(ms_get_submission_type_name($sub->submission_type))) {
                        unset($submissions[$id]);
                    }
                }
            } else {
                $tagged_submissions = mps_get_option('tagged_submissions', array());
                foreach ($submissions as $id => $sub) {
                    if (! isset($tagged_submissions['submission_' . $sub->id]) || empty($tagged_submissions['submission_' . $sub->id])) {
                        unset($submissions[$id]);
                    } else {
                        if (isset($_GET['tagged'])) {
                            if (! in_array(sanitize_text_field($_GET['tagged']), $tagged_submissions['submission_' . $sub->id])) {
                                unset($submissions[$id]);
                            }
                        }
                    }
                }
            }
        }

        // Display submission count
        $n_submissions = count($submissions);
        if ($n_submissions > 0) {
            $output .= "<p>Showing: " . $n_submissions . " submission" . ($n_submissions == 1 ? '' : 's') . "</p>";
        } else {
            $output .= '<p>No submissions found</p>';
        }

        // Display submissions
        $output .= '<div class="boot">';

        $output .= mps_flash('mps_pc_submission');

        $output .= '<div class="panel-group" id="accordion">';

        foreach ($submissions as $sub) {
            $submission_date = new Carbon($sub->submission_date);
            if ($sub->updated_date == '0000-00-00 00:00:00') {
                $updated_date = new Carbon($sub->submission_date);
            } else {
                $updated_date = new Carbon($sub->updated_date);
            }
            $submission_labels = mps_get_submission_labels($sub->id);
            $output .= '<div class="panel panel-default panel-sub-' . $sub->id . '">';
            $output .= '<div class="panel-heading status-' . $statuses[$sub->submission_status] . '">';
            if (pcss_user_can('email_out_from_pc', $this->auth) && ($useronly == false)) {
                $output .= '<input class="sub-checkbox js_show" data-id="'.$sub->id.'" type="checkbox"/>&nbsp;';
            }
            $output .= '<h4 style="display:inline;" class="panel-title">';
            $output .= '<span class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" data-target="#sub' . $sub->id . '">';
            $output .= sanitize_text_field(stripslashes($sub->submission_title)).' <span class="subidheader">(#'.$sub->id.') ';

            if (pcss_user_can('view_all_submissions')) {
                $output .= '<span class="pull-right">';
                foreach ($submission_labels as $label) {
                    $output .= '<label class="submission-label label label-' . $label['level'] . '">' . $label['text'] . '</label>';
                }
                // If a submission has tags, show something here to display them
                $output .= ' ';
                $submission_tags = mps_list_submission_tags($sub->id);
                if (! empty($submission_tags)) {
                    $output .= '<label class="label label-default label-xs btn-view-submission-tags" data-toggle="popover" title="Submission Tags" data-placement="left" data-content=\'' . $submission_tags . '\'><i class="fa fa-tag"></i></label>';
                }
                $output .= '</span>';
            }
            $output .= '</span>';
            $output .= '</span>';
            $output .= '</h4>';
            $output .= '</div>';

            if (isset($_GET['submission_id']) && $_GET['submission_id'] == $sub->id) {
                $output .= '<div id="sub'.$sub->id.'" class="panel-collapse collapse in">';
            } else {
                $output .= '<div id="sub'.$sub->id.'" class="panel-collapse collapse">';
            }
            $output .= '<div class="panel-body">';
            //start building panel content
            $output .= '<table class="table pc_submission_table">';
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Author Name', 'meeting-support') . '</b></td>';
            $output .= '<td><a href="mailto:' . sanitize_email(stripslashes($sub->author_email)) . '">' . sanitize_text_field(stripslashes($sub->author_name)) . '</a></td>';
            $output .= '</tr>';
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Affiliation', 'meeting-support') . '</b></td>';
            $output .= '<td>' . sanitize_text_field(stripslashes($sub->author_affiliation)) . '</td>';
            $output .= '</tr>';
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Status', 'meeting-support') . '</b></td>';
            $output .= '<td class="substatusname">' . ucfirst($statuses[$sub->submission_status]) . '</td>';
            $output .= '</tr>';
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Submission Type', 'meeting-support') . '</b></td>';
            $output .= '<td>' . ms_get_submission_type_name($sub->submission_type) . '</td>';
            $output .= '</tr>';
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Submission Date', 'meeting-support') . '</b></td>';
            $output .= '<td>' . $submission_date->format('Y-m-d H:i') . ' (' . $submission_date->diffForHumans() . ')</td>';

            $output .= '</tr>';
            $submission_archives = mps_get_submission_archives($sub->id);
            $archives_count = count($submission_archives);
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Last Updated', 'meeting-support') . '</b></td>';
            $output .= '<td>';
            $output .= '<button style="display:none" class="js_show open-submission-history-modal btn btn-default btn-xs" data-id="' . $sub->id . '" data-diff-latest="true" data-toggle="modal" data-target="#submissionHistoryModal">Version  ' . ($archives_count + 1) . ' <i class="fa fa-eye"></i></button>';
            $output .=  ' ' . $updated_date->format('Y-m-d H:i') . ' (' . $updated_date->diffForHumans() . ') ';
            $output .= '</td>';
            $output .= '</tr>';
            // Show an accordion with the history of each submission revision
            $output .= '<tr class="js_show">';
            $output .= '<td><b>Submission History</b></td>';
            $output .= '<td>';
            $output .= '<button class="btn btn-primary btn-toggle-submission-history collapsed" type="button" data-toggle="collapse" data-target="#submission-history-' . $sub->id . '" aria-expanded="false" aria-controls="collapseExample">Show Submission History</button>';
            $output .= '<br>';
            $output .= '<div class="collapse out" id="submission-history-' . $sub->id . '">';
            $output .= '<br>';
            $output .= '<table class="table table-condensed">';

            // Print out the latest version in the Submission History
            $output .= '<tr>';
            $output .= '<td>';
            $output .= '<button style="display:none" class="js_show open-submission-history-modal btn btn-default btn-xs" data-id="' . $sub->id . '" data-diff-latest="true" data-toggle="modal" data-target="#submissionHistoryModal">Version ' . ($archives_count + 1) . ' <i class="fa fa-eye"></i></button>';
            $output .= '</td>';
            $output .= '<td>';
            $output .= $updated_date->format('Y-m-d H:i') . ' (' . $updated_date->diffForHumans() . ')';
            $output .= '</td>';
            $output .= '</tr>';

            foreach ($submission_archives as $archive) {
                $archive_date = new Carbon($archive->timestamp);
                $output .= '<tr>';
                $output .= '<td>';
                $output .= '<button style="display:none" class="js_show open-submission-history-modal btn btn-default btn-xs" data-id="' . $archive->id . '" data-toggle="modal" data-target="#submissionHistoryModal">Version ' . $archives_count-- . ' <i class="fa fa-eye"></i></button>';
                $output .= '</td>';
                $output .= '<td>';
                $output .= $archive_date->format('Y-m-d H:i') . ' (' . $archive_date->diffForHumans() . ')';
                $output .= '</td>';

                $output .= '</tr>';
            }
            $output .= '</table>';
            $output .= '</div>';
            $output .= '</td>';
            $output .= '</tr>';
            if (pcss_user_can('rate_submission', $this->auth) || pcss_user_can('view_ratings', $this->auth)) {
                $ratinginfo = ms_get_rating_info($sub->id);
                $output .= '<tr>';
                if (pcss_user_can('rate_submission', $this->auth)) {
                    $output .= '<td class="subitem">';
                    $output .= '<button style="display:none" class="js_show openRatingModal btn btn-default" data-id="' . $sub->id . '" data-toggle="modal" data-target="#ratingModal">Rating <i class="fa fa-align-justify"></i></button>';
                } else {
                    $output .= '<td class="subitem"><b>' . __('Rating', 'meeting-support') . '</b>';
                }
                $output .= '</td>';
                $output .= '<td>';
                $output .= '<div style="padding-right: 2em;" class="spark">';
                $output .= '<label>' . __('Content', 'meeting-support') . '</label><br>';
                $output .= __('Total', 'meeting-support') . ': <span class="contentcount">'.$ratinginfo['content']['count'].'</span>, Average: <span class="contentavg">'.$ratinginfo['content']['avg'].'</span>';
                $output .= '<div class="rating-results" title="' . __('Average', 'meeting-support') . ': '.$ratinginfo['content']['avg'].'" value="'.$ratinginfo['content']['values'].'"></div>';
                $output .= '</div>';
                $output .= '<div class="spark">';
                $output .= '<label>' . __('Delivery', 'meeting-support') . '</label><br>' . __('Total', 'meeting-support') . ': <span class="presentercount">' . $ratinginfo['presenter']['count'] . '</span>, ' . __('Average', 'meeting-support') . ': <span class="presenteravg">'.$ratinginfo['presenter']['avg'].'</span>';
                $output .= '<div class="presenter-rating-results" title="' . __('Average', 'meeting-support') . ': ' . $ratinginfo['presenter']['avg'] . '" value="' . $ratinginfo['presenter']['values'] . '"></div>';
                $output .= '</div>';

                $output .= '</tr>';
            }
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Submission Abstract', 'meeting-support') . '</b></td>';
            $output .= '<td>' . nl2br(escape_multiline_text($sub->submission_abstract)) . '</td>';
            $output .= '</tr>';
            $output .= '<tr>';
            if (pcss_user_can('chair_actions', $this->auth)) {
                $output .= '<td class="subitem">';
                $output .= '<button class="js_show openFinalDecisionModal btn btn-default" data-id="'.$sub->id.'" data-toggle="modal" data-target="#finalDecisionModal">Final Decision <i class="fa fa-edit"></i></button>';
                $output .= '</td>';
            } else {
                $output .= '<td class="subitem"><b>' . __('Final Decision', 'meeting-support') . '</b></td>';
            }

            $output .= '<td class="finald">' . nl2br(escape_multiline_text($sub->final_decision)) . '</td>';
            $output .= '</tr>';
            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Author Comments', 'meeting-support') . '</b></td>';
            $output .= '<td>'.nl2br(escape_multiline_text($sub->author_comments)).'</td>';
            $output .= '</tr>';

            if ($sub->submission_url) {
                $output .= '<tr>';
                $output .= '<td class="subitem"><b>' . __('Submission URL', 'meeting-support') . '</b></td>';
                $output .= '<td><a target="_blank" href="' . sanitize_text_field(stripslashes($sub->submission_url)) . '">' . sanitize_text_field(stripslashes($sub->submission_url)) . '</a></td>';
                $output .= '</tr>';
            }

            $upload_dir = wp_upload_dir();
            $submissions_dir = $upload_dir['baseurl'] . '/submissions/';

            $output .= '<tr>';
            $output .= '<td class="subitem"><b>' . __('Submission File', 'meeting-support') . '</b></td>';
            $output .= '<td><a href="'.$submissions_dir . stripslashes($sub->filename).'">'.stripslashes($sub->filename).'</td>';
            $output .= '</tr>';

            // Submission tags
            if (pcss_user_can('view_all_submissions')) {
                $output .= '<tr>';
                $output .= '<td class="subitem">';
                $output .= '<button class="btn btn-default btn-open-submission-tag-modal" data-id="' . $sub->id . '" data-toggle="modal" data-target="#submissionTaggingModal">' . __('Tags', 'meeting-support') . ' <i class="fa fa-tag"></i></button>';
                $output .= '</td>';
                $output .= '<td>' . mps_list_submission_tags($sub->id) . '</td>';
                $output .= '</tr>';
            }

            if (pcss_user_can('edit_submission', $this->auth) || pcss_user_can('delete_submission', $this->auth) || $sub->author_uuid == $user['uuid']) {
                $output .= '<tr>';
                $output .= '<td class="subitem"></td>';
                $output .= '<td>';
                $output .= '<div class="pull-right">';
                if (pcss_user_can('edit_submission', $this->auth) || $sub->author_uuid == $user['uuid']) {
                    $output .= '<a href="' . esc_url(add_query_arg('edit_submission', $sub->id, home_url('/submit-topic/submission-form/'))) . '" class="btn btn-default"><i class="fa fa-edit"></i> Edit</a> ';
                }
                if (pcss_user_can('delete_submission', $this->auth) || $sub->author_uuid == $user['uuid']) {
                    $output .= '<a style="margin-top:2px;" href="' . esc_url(add_query_arg('edit_submission', $sub->id, add_query_arg('delete', true, home_url('/submit-topic/submission-form/')))) . '" class="btn btn-danger"><i class="fa fa-trash-o"></i> ' . __('Delete', 'meeting-support') . '</a>';
                }
                $output .= '</div>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</table>';
            //end panel content
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>'; //end accordion


        //begin submission tagging
        add_action('wp_footer', function () use ($statuses) {

            $output = '<div class="boot">';
            //begin rating modal

            $output .= '<div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" aria-labelledby="ratingModalLabel" aria-hidden="true">';
            $output .= '<div class="modal-dialog">';
            $output .= '<div class="modal-content">';
            $output .= '<div class="modal-header">';
            $output .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
            $output .= '<h4 class="modal-title" id="ratingModalLabel">' . __('Ratings for Submission #', 'meeting-support') . '<span class="modalsubid"></span></h4>';
            $output .= '</div>';
            $output .= '<div class="modal-body">';
            $output .= '<div id="modalownratingid"></div>';
            //Modal content
            $output .= '<form id="modalratingform" action="" method="POST" class="form-horizontal" role="form">';
            $output .= '<input type="hidden" id="modalsubmissionid" name="subid" value="">';
            // Begin 'rating content'
            $output .= '<div class="row">';
            $output .= '<div class="col-xs-6 form-group">';
            $output .= '<label for="ratingcontent" class="col-xs-5 control-label">' . __('Rating Content', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
            $output .= '<select id="modalratingcontent" name="ratingcontent" class="form-control">';
            $output .= '<optgroup label="' . __('High', 'meeting-support') . '"></optgroup>';
            $output .= '<option value="5">5</option>';
            $output .= '<option value="4">4</option>';
            $output .= '<option value="3">3</option>';
            $output .= '<option value="2">2</option>';
            $output .= '<option value="1">1</option>';
            $output .= '<optgroup label="' . __('Low', 'meeting-support') . '"></optgroup>';
            $output .= '<option selected="selected" value="0">' . __('None', 'meeting-support') . '</option>';
            $output .= '</select>';
            $output .= '</div>';
            $output .= '</div>';
            // End 'rating content'
            // Begin 'rating presenter'
            $output .= '<div class="col-xs-6 form-group">';
            $output .= '<label for="ratingpresenter" class="col-xs-5 control-label">' . __('Rating Delivery', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
            $output .= '<select id="modalratingpresenter" name="ratingpresenter" class="form-control">';
            $output .= '<optgroup label="' . __('High', 'meeting-support') . '"></optgroup>';
            $output .= '<option value="5">5</option>';
            $output .= '<option value="4">4</option>';
            $output .= '<option value="3">3</option>';
            $output .= '<option value="2">2</option>';
            $output .= '<option value="1">1</option>';
            $output .= '<optgroup label="' . __('Low', 'meeting-support') . '"></optgroup>';
            $output .= '<option selected="selected" value="0">' . __('None', 'meeting-support') . '</option>';
            $output .= '</select>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            // End 'rating presenter'
            $output .= '<div class="clear"></div>';
            $output .= '<br />';
            $output .= '<div class="form-group">';
            $output .= '<label for="ratingcomments" class="col-xs-2 control-label">' . __('Comments', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-9">';
            $output .= '<textarea style="height:auto;" rows="4" name="ratingcomments" class="col-xs-7 form-control" id="modalratingcomments"></textarea>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<div style="margin-top:20px;" class="col-sm-offset-9">';
            $output .= '<button id="modaldeleterating" class="btn btn-danger"><i class="fa fa-trash-o"></i> ' . __('Delete', 'meeting-support') . '</button>';
            $output .= '<button type="submit" style="margin-left:5px;" class="btn btn-success"><i class="fa fa-thumbs-o-up"></i> ' . __('Rate', 'meeting-support') . '</button>';
            $output .= '</div>';
            $output .= '</form>';
            $output .= '<div class="clear"></div>';
            $output .= '<br />';
            //Modal content
            $output .= '<h4>' . __('Ratings and Submission History', 'meeting-support') . '</h4>';
            $output .= '<div id="ratingsTable"><div class="alert alert-info">Loading...</div></div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';

            //end rating modal

            //begin final decision modal

            $output .= '<div class="modal fade" id="finalDecisionModal" tabindex="-1" role="dialog" aria-labelledby="finalDecisionModalLabel" aria-hidden="true">';
            $output .= '<div class="modal-dialog">';
            $output .= '<div class="modal-content">';
            $output .= '<div class="modal-header">';
            $output .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
            $output .= '<h4 class="modal-title" id="finalDecisionModalLabel">' . __('Final Decision for Submission #', 'meeting-support') . '<span class="modalsubid"></span></h4>';
            $output .= '</div>';
            $output .= '<div class="modal-body">';
            //Modal content
            $output .= '<form id="modalfinaldecisionform" action="" method="POST" class="form-horizontal" role="form">';
            $output .= '<input type="hidden" id="modalsubmissionid" name="subid" value="">';
            $output .= '<div class="form-group">';
            $output .= '<label for="submissionstatus" class="col-xs-3 control-label">' . __('Submission Status', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-4">';
            $output .= '<select id="modalsubmissionstatus" name="submissionstatus" class="form-control">';
            foreach ($statuses as $id => $status) {
                $output .= '<option value="' . $id . '">' . ucfirst($status) . '</option>';
            }
            $output .= '</select>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<br />';
            $output .= '<div class="form-group">';
            $output .= '<label for="finaldecision" class="col-xs-3 control-label">' . __('Final Decision Comments', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-9">';
            $output .= '<textarea style="height:auto;" rows="4" name="finaldecision" class="col-xs-7 form-control" id="modalfinaldecision"></textarea>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<div style="margin-top:20px;" class="pull-right">';
            $output .= '<button type="submit" style="margin-left:5px;" class="btn btn-success">' . __('Update', 'meeting-support') . '</button>';
            $output .= '</div>';
            $output .= '</form>';
            $output .= '<div class="clear"></div>';
            //Modal content
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';

            //end final decision modal

            //begin submission history modal
            $output .= '<div class="modal fade" id="submissionHistoryModal" tabindex="-1" role="dialog" aria-labelledby="submissionHistoryModalLabel" aria-hidden="true">';
            $output .= '<div class="modal-dialog">';
            $output .= '<div class="modal-content">';
            $output .= '<div class="modal-header">';
            $output .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
            $output .= '<h4 class="modal-title" id="submissionHistoryModalLabel">Submission History</h4>';
            $output .= '</div>';
            $output .= '<div class="modal-body">';
            //Modal content
            $output .= 'Loading...';
            //Modal content
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';

            //end submission history modal

            $available_submission_tags = mps_get_available_submission_tags();
            $output .= '<div class="modal fade" id="submissionTaggingModal" tabindex="-1" role="dialog" aria-labelledby="submissionTaggingModalLabel" aria-hidden="true">';
            $output .= '<div class="modal-dialog">';
            $output .= '<div class="modal-content">';
            $output .= '<div class="modal-header">';
            $output .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
            $output .= '<h4 class="modal-title" id="submissionTaggingModalLabel">Tag Submission</h4>';
            $output .= '</div>';
            $output .= '<div class="modal-body">';

            $output .= '<select id="select-submission-tags" class="form-control submission-tagging-input" multiple="multiple">';
            foreach ($available_submission_tags as $tag) {
                $output .= '<option value="' . sanitize_text_field($tag) . '">' . sanitize_text_field($tag) . '</option>';
            }
            $output .= '</select>';
            $output .= '<div class="clear"></div>';
            $output .= '<br>';
            $output .= '<button id="btn-save-submission-tags" class="btn btn-success btn-default pull-right">Save Tags</button>';

            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            //end submission tagging

            echo $output;
        });

        $output .= "</div>"; //end .boot
        return $output;
    }

    public function ms_sort_submissions_form()
    {
        if (! pcss_user_can('sort_submission', $this->auth)) {
            return '';
        }

        $pc_config = pc_config();
        $statuses = $pc_config['submission_status'];

        $output = '';
        $output .= '<div class="clear"></div>';
        $output .= '<hr>';
        $output .= '<div class="boot">';
        $output .= '<div class="clear"></div>';
        $output .= '<h4 class="pull-left">' . __('Filter Results', 'meeting-support') . '</h4>';
        $output .= '<div class="clear"></div>';
        $output .= '<br>';
        $output .= '<form class="pull-left" method="POST" action="" id="form-sort-submissions">';
        $output .= '<input type="hidden" name="sort_form" value="1">';
        $output .= '<p class="pull-left">' . __('Sort By:', 'meeting-support') . '</p>';
        $output .= '<select style="width:100%" name="sort_by">';
        $output .= '<option></option>';

        if (isset($_POST['sort_by'])) {
            $output .= '<option '.($_POST['sort_by'] == 'avgcontent' ? 'selected="selected" ' : '').'value="avgcontent">' . __('Average Ratings (Content)', 'meeting-support') . '</option>';
            $output .= '<option '.($_POST['sort_by'] == 'avgpresenter' ? 'selected="selected" ' : '').'value="avgpresenter">' . __('Average Ratings (Delivery)', 'meeting-support') . '</option>';
            $output .= '<option '.($_POST['sort_by'] == 'submissiondate' ? 'selected="selected" ' : '').'value="submissiondate">' . __('Submission Date', 'meeting-support') . '</option>';
            $output .= '<option '.($_POST['sort_by'] == 'submissionauthor' ? 'selected="selected" ' : '').'value="submissionauthor">' . __('Author', 'meeting-support') . '</option>';
            $output .= '<option '.($_POST['sort_by'] == 'submissiontitle' ? 'selected="selected" ' : '').'value="submissiontitle">' . __('Submission Title', 'meeting-support') . '</option>';
        } else {
            $output .= '<option value="avgcontent">' . __('Average Ratings (Content)', 'meeting-support') . '</option>';
            $output .= '<option value="avgpresenter">' . __('Average Ratings (Delivery)', 'meeting-support') . '</option>';
            $output .= '<option value="submissiondate">' . __('Submission Date', 'meeting-support') . '</option>';
            $output .= '<option value="submissionauthor">' . __('Author', 'meeting-support') . '</option>';
            $output .= '<option value="submissiontitle">' . __('Submission Title', 'meeting-support') . '</option>';
        }

            $output .= '</select>';
            $output .= '<div class="clear"></div>';
            $output .= '<p class="pull-left">' . __('Status', 'meeting-support') . ':</p>';
            $output .= '<select style="width:100%;" name="sort_status">';
            $output .= '<option></option>';

        foreach ($statuses as $statusid => $status) {
            if (isset($_POST['sort_status']) && $_POST['sort_status'] != '') {
                $output .= '<option ' . ( (int) $_POST['sort_status'] === (int) $statusid ? 'selected="selected" ' : '' ) . 'value="' . $statusid . '">' . ucfirst($status) . '</option>';
            } else {
                $output .= '<option value="' . $statusid.'">' . ucfirst($status) . '</option>';
            }
        }
        $output .= '</select>';
        $output .= '<div class="clear"></div>';
        $output .= '<p class="pull-left">' . __('My', 'meeting-support') . ':</p>';
        $output .= '<select style="width:100%;" name="sort_my">';
        $output .= '<option></option>';

        if ($_POST) {
            $output .= '<option '.($_POST['sort_my'] == 'ratedsubs' ? 'selected="selected" ' : '').'value="ratedsubs">' . __('Rated Submissions', 'meeting-support') . '</option>';
            $output .= '<option '.($_POST['sort_my'] == 'unratedsubs' ? 'selected="selected" ' : '').'value="unratedsubs">' . __('Unrated Submissions', 'meeting-support') . '</option>';
        } else {
            $output .= '<option value="ratedsubs">' . __('Rated Submissions', 'meeting-support') . '</option>';
            $output .= '<option value="unratedsubs">' . __('Unrated Submissions', 'meeting-support') . '</option>';
        }

        $output .= '</select>';
        $output .= '<div class="clear"></div>';
        $output .= '<p class="pull-left">' . __('Tagged', 'meeting-support') . ':</p>';
        $output .= '<select style="width:100%;" name="tagged_with">';
        $output .= '<option></option>';
        $used_tags = mps_list_used_submission_tags();
        foreach ($used_tags as $tag) {
            if ($_POST && isset($_POST['tagged_with']) && $_POST['tagged_with'] == $tag) {
                $output .= '<option selected="selected" value="' . sanitize_text_field($tag) . '">' . sanitize_text_field($tag) . '</option>';
            } else {
                $output .= '<option value="' . sanitize_text_field($tag) . '">' . sanitize_text_field($tag) . '</option>';
            }
        }
        $output .= '</select>';
        $output .= '<div class="clear"></div>';
        $output .= '<p class="pull-left">' . __('Order', 'meeting-support') . ':</p>';
        $output .= '<span class="pull-right">'. __('Ascending', 'meeting-support') .': <input checked="checked" type="radio" name="sort_order" value="ascending"/></span><br />';
        $output .= '<span class="pull-right">' . __('Descending', 'meeting-support') . ': <input '.(isset($_POST['sort_order']) && $_POST['sort_order'] == 'descending' ? 'checked="checked" ' : '').'type="radio" name="sort_order" value="descending"/></span>';
        $output .= '<div class="clear"></div>';
        $output .= '<br>';
        $output .= '<button id="pcss-submissions-sort" type="submit" class="pull-right btn btn-default"><i class="fa fa-filter"></i> ' . __('Filter', 'meeting-support') . '</button>';
        $output .= '<button data-toggle="tooltip" data-placement="left" title="Quick sort results on this page by Average Ratings (Content), Descending" type="submit" id="pcss-submissions-quick-sort" class="pull-left btn btn-default"><i class="fa fa-filter"></i> ' . __('PC Call', 'meeting-support') . '</button>';

        $output .= '</form>';
        $output .= '</div>';
        $output .= '<div class="clear"></div>';

        return $output;
    }

    public function ms_submission_email()
    {

        if (! pcss_user_can('email_out_from_pc', $this->auth)) {
            return '';
        }

        $output = '<hr>';
        $output .= '<div id="massEmailWidget" class="boot">';
        $output .= '<div class="clear"></div>';
        $output .= '<div class="modal fade" id="massEmailModal" tabindex="-1" role="dialog" aria-labelledby="massEmailModalLabel" aria-hidden="true">';
        $output .= '<div class="modal-dialog">';
        $output .= '<div class="modal-content">';
        $output .= '<div class="modal-header">';
        $output .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        $output .= '<h4 class="modal-title" id="massEmailModalLabel">' . __('PC Mass Email Tool', 'meeting-support') . '</h4>';
        $output .= '</div>';
        $output .= '<div class="modal-body">';
        $output .= '<div id="massMailTableHolder">';
        $output .= '<table id="massMailTable" class="boot table table-condensed table-striped table-bordered">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<td>' . __('ID', 'meeting-support') . '</td>';
        $output .= '<td>' . __('Name', 'meeting-support') . '</td>';
        $output .= '<td>' . __('Email Address', 'meeting-support') . '</td>';
        $output .= '<td>' . __('Submission Title', 'meeting-support') . '</td>';
        $output .= '<td>' . __('Status', 'meeting-support') . '</td>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '</table>';
        $output .= '</div>';
        $output .= '<div id="massMailWaitingMessage" style="text-align:center; display:none" class="alert alert-info"><i class="fa fa-spin fa-2x fa-cog"></i></div>';
        $output .= '<div id="massMailSuccessMessage" style="text-align:center; display:none" class="alert alert-success">' . __('Email(s) successfully sent', 'meeting-support') . '</div>';
        // Surrounding div to hide on form submit
        $output .= '<div id="massEmailSurround">';
        // Template Selector
        $output .= '<div class="btn-group btn-group-justified">';
        $output .= '<div class="btn-group">';
        $output .= '<button id="btnLoadBlankTemplate" type="button" class="btn btn-default">' . __('Blank Template', 'meeting-support') . '</button>';
        $output .= '</div>';
        $output .= '<div class="btn-group">';
        $output .= '<button id="btnLoadAcceptanceTemplate" type="button" class="btn btn-default">' . __('Acceptance Template', 'meeting-support') . '</button>';
        $output .= '</div>';
        $output .= '<div class="btn-group">';
        $output .= '<button id="btnLoadRejectionTemplate" type="button" class="btn btn-default">' . __('Rejection Template', 'meeting-support') . '</button>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<br>';
        $output .= '<div class="clear"></div>';
        // End Template Selector
        // Begin Form
        $output .= '<form id="frmMassEmail">';
        // Subject Input Field
        $output .= '<div class="form-group">';
        $output .= '<label for="massEmailSubject">' . __('Subject', 'meeting-support') . '</label>';
        $output .= '<input type="text" class="form-control" id="massEmailSubject">';
        $output .= '</div>';
        // End Subject Input Field
        // Body Input Field
        $output .= '<div class="form-group">';
        $output .= '<label for="massEmailBody">' . __('Body', 'meeting-support') . '</label>';
        $output .= '<textarea rows="12" class="form-control" id="massEmailBody"></textarea>';
        $output .= '</div>';
        // End Body Input Field
        // Submit Button
        $output .= '<button class="btn btn-default pull-right" type="submit">' . __('Submit', 'meeting-support') . '</button>';
        $output .= '<br /><br />';
        $output .= '</form>';
        // End Form
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<h4 class="pull-left">' . __('Email Author(s)', 'meeting-support') . '</h4>';
        $output .= '<div class="clear"></div>';
        $output .= '<a class="btn btn-xs btn-success" id="btnSelectAllSubs" href="#"><i class="fa fa-check-square-o"></i> ' . __('Select all submissions', 'meeting-support') . '</a>';
        $output .= '<a class="btn btn-xs btn-danger" style="display:none;" id="btnDeselectAllSubs" href="#"><i class="fa fa-square-o"></i> ' . __('Deselect all submissions', 'meeting-support') . '</a>';
        $output .= '<br>';
        $output .= '<br>';
        $output .= '<a id="btnSendMassEmail" class="btn btn-default" href="#"><i class="fa fa-envelope-o"></i> ' . __('Send Email', 'meeting-support') . '</a>';

        $output .= '</div>';
        return $output;
    }


    public function ms_contact_form()
    {

        $output = '';
        $output .= '<div class="boot">';
        $output .= '<form id="contactform" method="POST" class="form-horizontal" role="form" action="' . admin_url('admin-post.php') . '">';
        $output .= '<input type="hidden" name="action" value="mps_contact_form">';
        $output .=  wp_nonce_field('mps_contact_form', '_wpnonce', true, false);
        $output .=  mps_flash('mps_contact_form');
        $output .= '<div class="form-group">';
        $output .= '<label for="sender_name" class="col-xs-3 control-label">' . __('Name', 'meeting-support') . '</label>';
        $output .= '<div class="col-xs-7">';
        if ($_SESSION['old_post']['email']) {
            $output .= '<input type="text" required value="' . sanitize_text_field($_SESSION['old_post']['sender_name']) . '" name="sender_name" class="form-control">';
        } else {
            if (! empty($this->auth->user)) {
                $output .= '<input type="text" required value="' . sanitize_text_field($this->auth->user['name']) . '" name="sender_name" class="form-control">';
            } else {
                $output .= '<input type="text" required value="" name="sender_name" class="form-control">';
            }
        }
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';
            $output .= '<div class="clear"></div>';
            $output .= '<br>';
            $output .= '<div class="form-group">';
            $output .= '<label for="email" class="col-xs-3 control-label">' . __('Email Address', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
        if ($_SESSION['old_post']['email']) {
            $output .= '<input required type="email" value="' . sanitize_email($_SESSION['old_post']['email']) . '" id="email" name="email" class="form-control">';
        } else {
            if (! empty($this->auth->user)) {
                $output .= '<input required type="email" value="' . sanitize_email($this->auth->user['email']) . '" id="email" name="email" class="form-control">';
            } else {
                $output .= '<input required type="email" value="" id="email" name="email" class="form-control">';
            }
        }
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';

            $output .= '<div class="clear"></div>';
            $output .= '<br>';
            $output .= '<div class="form-group">';
            $output .= '<label for="subject" class="col-xs-3 control-label">' . __('Subject', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
            $output .= '<input type="text" value="' . sanitize_text_field($_SESSION['old_post']['subject']) . '" name="subject" class="form-control" id="subject">';
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';

            $output .= '<div class="clear"></div>';
            $output .= '<br>';
            $output .= '<div class="form-group">';
            $output .= '<label for="message" class="col-xs-3 control-label">' . __('Message', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7">';
            $output .= '<textarea required="" style="height:auto;" rows="4" name="message" class="col-xs-7 form-control" id="message">' . escape_multiline_text($_SESSION['old_post']['message']) . '</textarea>';
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';
            $output .= '</div>';


        if (! $this->auth->user) {
        // Captcha library
            $captcha = new Gregwar\Captcha\CaptchaBuilder;
            $captcha->setDistortion(true);
            $captcha->setIgnoreAllEffects(true);
            $captcha->build();

            $_SESSION['phrase'] = $captcha->getPhrase();

        // If the user isn't logged in, let's show them a CAPTCHA form
            $output .= '<div class="clear"></div>';
            $output .= '<br>';
            $output .= '<div class="form-group">';
            $output .= '<label for="submissionurl" class="col-xs-3 control-label">' . __('Verification Text', 'meeting-support') . '</label>';
            $output .= '<div class="col-xs-7 captcha"><img src="' . $captcha->inline() . '" /></div>';
            $output .= '<div class="col-xs-offset-3 col-xs-7">';
            $output .= '<input required class="form-control" placeholder="' . __('Verification Text', 'meeting-support') . '" name="captcha" type="text">';
            $output .= '</div>';
            $output .= '<small><i class="fa fa-asterisk"></i></small>';

            $output .= '</div>';
        // End CAPTCHA form
        }

            $output .= '<div class="clear"></div>';
            $output .= '<small><i class="fa fa-asterisk"></i> ' . __('= required field', 'meeting-support') . '</small>';
            $output .= '<div class="form-group">';
            $output .= '<div class="col-xs-offset-8 col-xs-2">';
            $output .= '<button type="submit" class="customsubmitbtn pull-right btn btn-warning">' . __('Submit', 'meeting-support') . '</button>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
            $output .= '</div>';

        // Clear old_post values
            $_SESSION['old_post'] = array();

            return $output;
    }


    public function ms_presentation_upload_form()
    {
        /**
        * Return the form to start a presentation upload
        * Populate a form if we are editing an existing one
        */

        if ($this->auth->user) {
            /**
            * If user uploaded presentation(s) before creating RIPE NCC account, the presentations table will have empty uuid field
            * So we need to fill uuid field after user create and sign in
            */
            assign_presentations($this->auth);
        }

        $allowed_files = mps_get_option('allowed_file_types', array());

        $presentation = false;

        // Are we editing a presentation?
        if ($this->auth->user && isset($_GET['edit_presentation'])) {
            $presentation = mps_get_presentation((int) $_GET['edit_presentation']);

            // If the user can't edit this presentation, then don't expose anything to them
            if ($this->auth->user['uuid'] !== $presentation->author_uuid) {
                mps_flash('mps_presentation_upload', __('You do not have permission to edit this presentation', 'meeting-support'), 'danger');
                unset($presentation);
            } else {
                mps_flash('mps_presentation_upload', __('Editing presentation:', 'meeting-support') . ' "<b>' . sanitize_text_field($presentation->title) . '</b>"', 'warning');
            }
        }

        // Are we trying to edit a presentation but not logged in?
        if (! $this->auth->user) {
            if (isset($_GET['edit_presentation'])) {
                mps_flash('mps_presentation_upload', __('You must be logged in to edit presentations', 'meeting-support'), 'danger');
                unset($presentation);
            } else {
                mps_flash('mps_presentation_upload', __("For greater efficiency, security, and to include your speaker bio, use your " . mps_get_ripencc_login_link('RIPE NCC Access account') . ". <br>You may upload without a " . mps_get_ripencc_login_link('RIPE NCC Access account') . ". This option is less flexible, you can't view your presentation history.", 'meeting-support'), 'mps-warning');
            }
        }

        if ($this->auth->user && isset($_GET['delete_presentation'])) {
            $presentation = mps_get_presentation((int) $_GET['delete_presentation']);

            // If the user can't delete this presentation, then don't expose anything to them
            if ($this->auth->user['uuid'] !== $presentation->author_uuid) {
                mps_flash('mps_presentation_upload', __('You do not have permission to delete this presentation', 'meeting-support'), 'danger');
                unset($presentation);
            } else {
                // Show delete presentation form
                $return = '<div class="boot">';
                $return .= '<div class="alert alert-danger">' . __('Are you sure you want to delete this presentation?', 'meeting-support') . ' <b>' . sanitize_text_field($presentation->title) . '</b>';
                $return .= '<br>';
                $return .= '<br>';
                $return .= '<form action="' . admin_url('admin-post.php') . '" method="POST" role="form">';
                $return .= '<input type="hidden" name="action" value="mps_presentation_delete"/>';
                $return .= '<input type="hidden" name="presentation_id" value="' . $presentation->id . '"/>';
                $return .=  wp_nonce_field('mps_presentation_delete', '_wpnonce', true, false);

                $return .= '<input class="btn btn-danger btn-xs" type="submit" value="' . __('Delete', 'meeting-support') . '">';
                $return .= '</form>';

                $return .= '</div>';
                $return .= '</div>';

                return $return;
            }
        }

        // Get presentations to show
        $presentations = [];
        if ($this->auth->user) {
            $presentations = mps_get_presentations_for_uuid($this->auth->user['uuid']);
        }

        // Inject some javascript variables for client-side validation of file uploads
        add_action('wp_footer', function () {
            echo "
            <script>
                var max_file_upload_size = '" . getMaximumFileUploadSize() . "'; // bytes
                var allowed_file_types = " . json_encode(mps_get_option('allowed_file_types', array())) . ";
                var ajax_nonce = '" . wp_create_nonce('ajax-nonce-mps') . "';
            </script>
            ";
        });


        $return = '<div class="boot">';
        if (! empty($presentations)) {
            $return .= '<div class="container-fluid" id="presentations-box">';
            $return .= '<div class="row">';
            $return .= '<div class="col-xs-12">';
            $return .= '<div style="display: none;" class="js_show btn btn-default pull-right uploadNewPresentation">Upload New Presentation</div>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div> <!-- presentations-box-->';
        }

        $return .= mps_flash('mps_presentation_upload');

        // if javascript
        $return .= '<div class="js_show">';

        if (! empty($presentations)) {
            // The user has not uploaded any presentations yet
            $return .= '<div class="hidden" id="no-presentations-box">';
        } else {
            $return .= '<div id="no-presentations-box">';
        }

        if ($this->auth->user) {
            $return .= '<small>' . __('You do not have any uploaded presentations', 'meeting-support') . '</small>';
        } else {
            $return .= '<small>' . __('Log in to your ' . mps_get_ripencc_login_link('RIPE NCC Access account') . ' account to see all your presentations', 'meeting-support') . '</  small>';
        }
        $return .= '<div class="clear"></div>';
        $return .= '<div class="btn btn-default uploadNewPresentation">Upload Presentation</div>';

        $return .= '</div> <!-- no-presentations-box-->';

        if (! empty($presentations)) {
            // The user has already uploaded at least one presentation

            // Show a table of all existing presentations
            $return .= '<div id="existing-presentations">';
            foreach ($presentations as $pres) {
                $slot_info = ms_get_slot($pres->slot_id);
                $session_info = ms_get_session_data($pres->session_id);
                $return .= '<div class="container-fluid row-striped existing-presentation" data-presentation-id="' . $pres->id . '">';

                $return .= '<div class="row"><!-- main-row -->';

                $return .= '<div class="col-sm-9 col-xs-12 presentation-table">';
                $return .= '<div class="pull-left" style="min-width: 70px;"><i class="fa fa-5x fa-file-text-o"></i></div>';
                $return .= '<div><b><small>' . sanitize_text_field($pres->title) .'</small></b></div>';
                if ($slot_info) {
                    $return .= '<div><small>' .sanitize_text_field($slot_info->title) . '</small></div>';
                } else {
                    $return .= '<div><small>No Slot Selected</small></div>';
                }
                $return .= '<div><small>' .stripslashes($session_info->name).' ('.date('H:i', strtotime($session_info->start_time)).'-'.date('H:i', strtotime($session_info->end_time)). ')</small></div>';

                $return .= '<span><small>Last updated: <b>' . $pres->last_edited . '</b></small></span>';

                $return .= '<div class="row"><!-- nested row -->';
                $return .= '<div class="col-sm-12 col-xs-12">';
                $return .= '<strong>Download </strong>';
                foreach ($pres->files as $file) {
                    $return .= '<span class="file-link">';
                    $return .= '<a target="_blank" data-toggle="tooltip" data-placement="top" title="' . $file['name'] . '" href="' . $file['url'] . '">';
                    $return .= '<i class="hidden-xs fa ' . $file['icon'] . '"></i>';
                    $return .= '<span class="filename visible-xs-inline-block"><i class="fa ' . $file['icon'] . '"></i> ' . $file['name'] . '</span>';
                    $return .= '</a>';
                    $return .= '</span>';
                }
                $return .= '</div>';
                $return .= '</div> <!-- nested row -->';

                $return .= '</div>';

                $return .= '<div class="col-xs-12 col-sm-3 text-right" style="padding: 10px 0;">';
                $return .= '<div class="row">';
                $return .= '<div class="col-xs-6 col-sm-12">';
                $return .= '<button class="btn btn-sm btn-success update-presentation" btn-success data-toggle="modal">Update</button>';
                $return .= '</div>';
                $return .= '<div class="col-xs-6 col-sm-12">';
                $return .= '<button class="btn btn-sm btn-danger delete-presentation">Delete</button>';
                $return .= '</div>';
                $return .= '</div>';
                $return .= '</div>';

                $return .= '</div><!-- main-row -->';
                $return .= '</div><!-- existing-presentation -->';
            }
            $return .= '</div><!-- existing-presentations -->';

            $return .= '<div class="clear"></div>';
        }

        $return .= '</div>';
        // endif javascript

        $return .= '<noscript>';
        $return .= ms_upload_form($this->auth, $presentation);
        $return .= $this->ms_my_presentations();
        $return .= '</noscript>';

        $return .= '</div>';
        add_action('wp_footer', function ($presentations) {
            // upload form - modal window
            // if (! isset($presentation)) {
            //     $presentation = false;
            // }
            $footer_html = '';
            $footer_html .= ms_upload_form_modal($this->auth, null);
            echo $footer_html;
        });


        // Speaker bio area
        if ($this->auth->user) {
            $return .= mps_speaker_bio_upload_form($this->auth->user);
        }
        // Clear old form data from $_SESSION
        unset($_SESSION['old_post']);

        return $return;
    }

    public function ms_my_presentations()
    {
        /**
        * Function to display a table of presentations owned by the current logged in user.
        */

        // Don't return anything if the user isn't logged in
        if (! $this->auth->user) {
            return '';
        }

        $presentations = mps_get_presentations_for_uuid($this->auth->user['uuid']);

        $presentation_dir = mps_get_option('presentations_dir');

        // Don't show anything if the user didn't actually upload any presentations
        if (! $presentations) {
            return '';
        }

        $html = '';
        $html .= '<div class="boot hide_js">';
        $html .= '<table class="table table-striped table-condensed">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>' . __('Title', 'meeting-support') . '</th>';
        $html .= '<th>' . __('Session', 'meeting-support') . '</th>';
        $html .= '<th style="width:1px;"></th>';
        $html .= '<th style="width:1px;"></th>';
        $html .= '<th style="width:1px;"></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        foreach ($presentations as $presentation) {
            $html .= '<tr>';
            $html .= '<td>' . sanitize_text_field($presentation->title) . '</td>';
            $html .= '<td>' . sanitize_text_field(@ms_get_session_data($presentation->session_id)->name) . '</td>';
            $html .= '<td><a title="' . __('Edit presentation', 'meeting-support') . '" href="' . esc_url(remove_query_arg('delete_presentation', add_query_arg('edit_presentation', $presentation->id))) . '"><i class="fa fa-pencil"></i></a></td>';
            $html .= '<td class="file_list_column">';
            foreach (json_decode($presentation->filename) as $file) {
                $html .= '<a target="_blank" title="' . sanitize_file_name($file) . '" href="/' . $presentation_dir . $file . '"><i class="fa ' . get_file_icon_class($file) .'"></i></a>&nbsp;';
            }
            $html .='</td>';
            $html .= '<td><a title="' . __('Delete presentation', 'meeting-support') . '" href="' . esc_url(remove_query_arg('edit_presentation', add_query_arg('delete_presentation', $presentation->id))) . '"><i class="fa fa-trash-o"></i></a></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    public function ms_pcss_homepage($attrs)
    {
        if (! $this->auth->user) {
            return false;
        }

        if (! pcss_user_can('view_all_submissions')) {
            return false;
        }

        $output = '';
        $to_rate = array();
        $to_update = array();
        $submissions = mps_get_all_submissions();

        foreach ($submissions as $submission) {
            $my_rating = ms_get_rating($this->auth->user['uuid'], $submission->id);
            if (! $my_rating) {
                // Submission hasn't been rated at all by this user, add to the "to-rate" list.
                $to_rate[] = $submission;
            } else {
                // Submission has been rating, let's check to see if the rating needs to be updated
                if ($submission->updated_date > $my_rating->timestamp) {
                    $to_update[] = $submission;
                }
            }
        }

        $output .= '<div class="pcss-actions">';
        $output .= '<h4>Submissions requiring your attention:</h4><br>';
        if (empty($to_rate) && empty($to_update)) {
            $output .= 'None';
        } else {
            if (! empty($to_rate)) {
                $output .= '<label class="label label-success submission-label">Not yet rated:</label><br>';
                $output .= '<ul>';
                foreach ($to_rate as $submission) {
                    if ($submission->updated_date == '0000-00-00 00:00:00') {
                        $submission_date = new Carbon($submission->submission_date);
                    } else {
                        $submission_date = new Carbon($submission->updated_date);
                    }
                    $output .= '<li><a href="' . add_query_arg('submission_id', $submission->id, ms_get_submission_type_slug($submission->submission_type)) . '">' . $submission->submission_title . '</a> (' . $submission_date->diffForHumans() . ')</li>';
                }
                $output .= '</ul>';
            }
            if (! empty($to_rate) && ! empty($to_update)) {
                $output .= '<br>';
            }
            if (! empty($to_update)) {
                $output .= '<label class="label label-danger submission-label">Updated since last rated</label><br>';
                $output .= '<ul>';
                foreach ($to_update as $submission) {
                    $submission_date = new Carbon($submission->updated_date);
                    $output .= '<li><a href="' . add_query_arg('submission_id', $submission->id, ms_get_submission_type_slug($submission->submission_type)) . '">' . $submission->submission_title . '</a> (' . $submission_date->diffForHumans() . ')</li>';
                }
                $output .= '</ul>';
            }
        }
        $output .= '</div>';

        return $output;
    }

    public function ms_user_is_logged_in($atts, $content = null)
    {
        if ($this->auth->user) {
            return do_shortcode($content);
        }
        return;
    }

    public function ms_user_is_not_logged_in($atts, $content = null)
    {
        if (! $this->auth->user) {
            return do_shortcode($content);
        }
        return;
    }

    public function ms_pcss_user_can($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'permission' => ''
        ), $atts));

        if (pcss_user_can($permission)) {
            return do_shortcode($content);
        }
        return;
    }

    public function ms_agenda_pdf_url()
    {
        $admin_url = get_admin_url();
        $admin_post_url = esc_url($admin_url . 'admin-post.php');
        $full_url = add_query_arg('action', 'mps_get_agenda_pdf', $admin_post_url);
        return $full_url;
    }


    public function ms_agenda_ics_url()
    {
        $admin_url = get_admin_url();
        $admin_post_url = esc_url($admin_url . 'admin-post.php');
        $full_url = add_query_arg('action', 'mps_get_agenda_ics', $admin_post_url);
        return $full_url;
    }

    public function ms_session_slots($atts)
    {
        /**
        * Shortcode to display slots for this session, taking into account overrides on a per-slot basis.
        * Also display any presentations which refer to each slot
        */
        extract(shortcode_atts(array(
            'session' => 0,
            'showfiles' => 'true',
            'hidedate' => false,
            'custom_title' => false
        ), $atts));

        if ($session == 0) {
            return '';
        }

        $session = ms_get_session_data($session);

        if (! $session) {
            return '';
        }

        $slots = ms_get_session_slots($session->id);

        $output = '';

        if ($hidedate === false) {
            // Don't show the date if we throw a 'hidedate' argument in the shortcode
            $output .= '<h2>'.date('l, j F H:i', strtotime($session->start_time)).' - '.date('H:i', strtotime($session->end_time)).'</h2>';
        }
        if ($custom_title !== false) {
            // We need to ltrim() the shortcode argument because Wordpress is ridiculous and automatically adds <br /> and <p> when it thinks it should... (wpautop)
            $output .= ltrim($custom_title, '<br />');
        } else {
            $output .= '<h3>'.stripslashes($session->name).'</h3>';
        }
        if (count($slots) > 0) {
            $output .= '<div class="boot">';
            $output .= '<table class="slots-table table table-striped">';
            foreach ($slots as $slot) {
                if ($slot->parent_id != 0) {
                    continue;
                }

                // Build an array of children to iterate through later.
                $children = array();
                foreach ($slots as $slot_tmp) {
                    if ($slot_tmp->parent_id == $slot->id) {
                        $children[] = $slot_tmp;
                    }
                }

                if (count($children) > 0) {
                    // This is a parent slot, we don't want to show the second column.
                    $output .= '<tr>';
                    if (mps_get_option('global_ratings') == 'on' && $session->is_rateable == 1) {
                        $output .= '<td colspan="3" style="vertical-align:middle;">'.stripslashes($slot->content).'</td>';
                    } else {
                        $output .= '<td colspan="2" style="vertical-align:middle;">'.stripslashes($slot->content).'</td>';
                    }
                    $output .= '</tr>';
                    foreach ($children as $child) {
                        $output .= '<tr>';
                        $output .= '<td style="vertical-align:middle;"><ul><li>'.stripslashes($child->content).'</li></ul></td>';

                        // Do we want to show the second column for files?
                        if ($showfiles == 'true') {
                            $output .= '<td style="width:1px;"><span class="downloadlinks">';

                            $output .= get_filelist_view($child->id);
                            $output .= get_videofeed_view($child->id);
                            if (mps_get_option('global_ratings') == 'on' && $session->is_rateable == 1 && $child->ratable) {
                                $output .= ms_get_my_presentation_rating_button($child->id, $this->auth);
                            }
                            $output .= get_speaker_bio_view($child->id);
                            $output .= '</span></td>';
                        }
                        // Rate column
                        $output .= '</tr>';
                    }
                } else {
                    // This slot has no children
                    $output .= '<tr>';
                    $output .= '<td style="vertical-align:middle;">' . stripslashes($slot->content) . '</td>';

                    // Do we want to show the second column for files?
                    if ($showfiles == 'true') {
                        $output .= '<td style="width:1px;"><span class="downloadlinks">';

                        $output .= get_filelist_view($slot->id);
                        $output .= get_videofeed_view($slot->id);
                        if (mps_get_option('global_ratings') == 'on' && $session->is_rateable == 1 && $slot->ratable) {
                            $output .= ms_get_my_presentation_rating_button($slot->id, $this->auth);
                        }
                        $output .= get_speaker_bio_view($slot->id);
                        $output .= '</span></td>';
                    }
                    // Rate column
                    $output .= '</tr>';
                }
            }
            $output .= '</table>';


            add_action('wp_footer', function () {

                echo ms_slot_rating_modal();
            });

            $output .= '</div>';
        }

        return $output;
    }

    public function ms_presentation_list($atts)
    {

        $uploaddir = wp_upload_dir();
        $speakers_page = get_page_by_path('speakers');
        $output = '<div class="boot">';
        // Fetch those presentations
        $presentations = mps_get_all_presentations();

        $output .= '<table id="existing_presentations" class="pres_datatable table table-striped">';
        $output .= '<thead>';
        $output .= '<tr>';

        $output .= '<th></th>';
        $output .= '<th>' . __('Presenter Name', 'meeting-support') . '</th>';
        $output .= '<th>' . __('Session', 'meeting-support') . '</th>';
        $output .= '<th>' . __('Presentation Title', 'meeting-support') . '</th>';
        $output .= '<th title="' . __('Download Presentation', 'meeting-support') . '"><i class="fa fa-download"></i></th>';
        $output .= '<th title="' . __('Presentation Upload Date', 'meeting-support') . '"><i class="fa fa-calendar"></i></th>';
        $output .= '</tr>';
        $output .= '</thead>';

        $showcount = 0;
        foreach ($presentations as $presentation) {
            $pres_session = ms_get_session_data($presentation->session_id);
            if ($speakers_page) {
                $speaker = mps_get_speaker_by_uuid($presentation->author_uuid);
            } else {
                $speaker = false;
            }
            if (is_null($pres_session)) {
                $sessionname = __('N/A', 'meeting-support');
                $sessionurl = '';
            } else {
                $sessionname = $pres_session->name;
                $sessionurl = $pres_session->url;
            }
            $showcount++;

            $output .= '<tr>';
            $output .= '<td></td>';

            if ($speaker && $speaker->allowed) {
                $output .= '<td><a target="_blank" href="' . home_url('speakers/' . $speaker->slug) . '">' . stripslashes(sanitize_text_field($presentation->author_name)) . '</a></td>';
            } else {
                $output .= '<td>' . stripslashes(sanitize_text_field($presentation->author_name)) . '</td>';
            }
            if ($sessionurl != '') {
                $output .= '<td><a href="' . $sessionurl . '">' . stripslashes($sessionname) . '</a></td>';
            } else {
                $output .= '<td>' . stripslashes($sessionname) . '</td>';
            }
            $output .= '<td>'.stripslashes(sanitize_text_field($presentation->title)).'</td>';
            $output .= '<td class="download-icon-group downloadlinks">';
            foreach (json_decode($presentation->filename) as $file) {
                $output .= '<a href="'.$uploaddir['baseurl'].'/presentations/'.$file.'"><i class="download-icon fa '.get_file_icon_class($file).'"></i></a>&nbsp;';
            }
            // Check for a video
            $videos = ms_get_slot_videos($presentation->slot_id);
            if (is_array($videos)) {
                foreach ($videos as $video) {
                    $output .= '<a class="nounderline" style="color:green;" href="' . home_url() . '/archives/video/'.$video->id.'"><i class="download-icon fa fa-video-camera"></i></a>';
                }
            }
            $output .= '</td>';
            $output .= '<td>' . date('Y-m-d', strtotime($presentation->submission_date)) . '</td>';

            $output .= '</tr>';
        }
        if ($showcount == 0) {
            $output .= '<tr>';
            $output .= '<td>' . __('There are not yet any presentations available', 'meeting-support') . '</td>';
            $output .= '<td></td>';
            $output .= '<td></td>';
            $output .= '<td></td>';
            $output .= '<td></td>';
            $output .= '</tr>';
        }
        $output .= '</table>';

        $output .= '</div>';
        return $output;
    }

    public function ms_room_query($atts)
    {
        $rooms = mps_get_option('rooms');

        // $mps_rooms = $mps_config['mps_rooms'];

        $otherroom = array('main' => 'side', 'side' => 'main');

        // Extract parameters from attributes
        extract(shortcode_atts(array(
            'cap' => false,
            'other' => false,
            'name' => false,
            'room' => '',
        ), $atts));

        // Get the room from query variable or parameter
        if (empty($room) && (isset($wp_query->query_vars['room']))) {
            $room = urldecode($wp_query->query_vars['room']);
        }

        // Sanitise in case it contains bad text
        if (empty($room) || (($room != 'main') && ($room != 'side'))) {
            $room = 'main';
        }

        // If 'other' is specified, return the 'other' room
        if ($other) {
            $room = $otherroom[$room];
        }

        // If 'name' is requested, return it
        if ($name) {
            $room = $rooms[$room]['short'];
        }

        // Capitalise first letter if required
        if ($cap) {
            $room = ucfirst($room);
        }

        return $room;
    }

    public function ms_live_recorder($atts)
    {
        // Display Webstream recorder
        extract(shortcode_atts(array(
            'room' => '',
        ), $atts));

        $recorder = '<iframe id="recorder" src="https://recorder.ripe.net/record/?room=' . $room . '" frameborder="0" marginheight="0px" marginwidth="0px" width="100%" height="100%"> <p>Your browser does not support iframes.</p></iframe>';

        return $recorder;
    }

    public function ms_live_transcript($atts)
    {
        extract(shortcode_atts(array(
            'room' => '',
            'stdayroomtime' => '',
            'etiquette_title' => 'Etiquette',
            'otherclients_title' => 'Live Help Clients - Main',
        ), $atts));

        $trans = '';

        $etiquette_page = get_page_by_title($etiquette_title);

        $otherclients_page = get_page_by_title($otherclients_title);

        $trans .= '<div class="boot">';

        $trans .= '<ul id="live_tabs" class="nav nav-tabs" role="tablist">';
        $trans .= '<li role="presentation" class="active"><a href="#transcript" aria-controls="transcript" role="tab" data-toggle="tab">Transcript</a></li>';
        $trans .= '<li role="presentation"><a href="#etiquette" aria-controls="etiquette" role="tab" data-toggle="tab">Etiquette</a></li>';
        $trans .= '<li role="presentation"><a href="#otherclients" aria-controls="messages" role="tab" data-toggle="tab">Other Clients</a></li>';
        $trans .= '</ul>';

        $trans .= '<div class="tab-content">';
        $trans .= '<div role="tabpanel" class="tab-pane active" id="transcript">';

        $trans .= '<iframe style="height: 400px" id="stFrame" frameborder="0" height="100%" marginheight="0px" marginwidth="0px" scrolling="no" src="https://www.streamtext.net/text.aspx?event=' . $stdayroomtime . '&amp;ff=Helvetica,Arial,sans-serif&amp;fs=18&amp;fgc=000000&amp;bgc=FFFFFF&amp;header=false&amp;controls=false&amp;footer=false&amp;chat=false" width="100%">' . __('Your browser does not support iframes.', 'meeting-support') . '</iframe>';

        $trans .= '</div>';
        $trans .= '<div role="tabpanel" class="tab-pane" id="etiquette">';
        $trans .= $etiquette_page->post_content;
        $trans .= '</div>';
        $trans .= '<div role="tabpanel" class="tab-pane" id="otherclients">';
        $trans .= $otherclients_page->post_content;
        $trans .= '</div>';
        $trans .= '</div>';

        $trans .= '</div>';


        return $trans;
    }

    public function ms_pc_elections_form()
    {
        $max_votes = mps_get_option('max_pc_votes', 2);
        $my_votes = mps_get_pc_votes_from_uuid($this->auth->user['uuid']);
        $my_votes_count = count($my_votes);
        $votes_left = $max_votes - $my_votes_count;
        $candidates = mps_get_all_pc_candidates();
        shuffle($candidates);
        $output = '';

        $my_votes_ids = array();
        foreach ($my_votes as $my_vote) {
            $my_votes_ids[] = $my_vote->candidate;
        }

        $login_url = add_query_arg('originalUrl', get_permalink(), $this->auth->crowd_config['login_url']);

        $numberlookup = array(
            0 => 'none',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
        );

        $output .= mps_flash('mps_pc_election_vote');

        if (!$this->auth->user) {
            $output .= '<div class="boot">';
            $output .= '<div class="alert alert-danger"><a href="' . $login_url . '">Sign in to RIPE NCC Access</a> to vote in the RIPE PC elections.</div>';
            $output .= '</div>';
            return $output;
        }
        if (!empty($candidates)) {
            $output .= '<div class="boot">';
            $output .= '<div class="alert alert-warning">';
            $output .= 'You have used <b>' . $numberlookup[$my_votes_count] . '</b> of your <b>' . $numberlookup[$max_votes] . '</b> votes. You can change your votes until the deadline by selecting or deselecting the checkboxes and clicking <b>Vote</b>.';
            $output .= '</div>';

            $output .= '<form method="POST" action="' . admin_url('admin-post.php') . '">';
            $output .= '<input type="hidden" name="action" value="mps_pc_election_vote">';
            $output .= '<table class="table table-striped">';
            $output .= '<thead>';
            $output .= '<tr>';
            $output .= '<th>Candidate Name</th>';
            $output .= '<th></th>';
            $output .= '</tr>';
            $output .= '</thead>';
            $output .= '<tbody>';
            foreach ($candidates as $candidate) {
                $output .= '<tr>';
                $output .= '<td>' . $candidate->name . '</td>';
                if (in_array($candidate->id, $my_votes_ids)) {
                    $output .= '<td><input type="checkbox" class="candidate_select" checked="checked" name="candidates[]" value="' . $candidate->id . '"/></td>';
                } else {
                    $output .= '<td><input type="checkbox" class="candidate_select" name="candidates[]" value="' . $candidate->id . '"/></td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody>';
            $output .= '</table>';
            $output .= '<input class="btn btn-default pull-right" type="submit" value="Vote"/>';
            $output .= '</form>';
            $output .= '<div data-max-votes="' . $max_votes . '" id="user-max-votes"></div>';
        } else {
            $output .= '<div class="boot">';
            $output .= '<div class="alert alert-warning">';
            $output .= 'The candidate list will be updated soon';
            $output .= '</div>';
        }


        $output .= '</div>';

        return $output;
    }

    public function ms_pc_elections_results()
    {
        $candidates = mps_get_all_pc_candidates();
        foreach ($candidates as $candidate) {
            $candidate->votes = mps_get_pc_candidate_votes($candidate->id)['votes'];
        }

        usort(
            $candidates,
            function ($a, $b) {
                return strcmp($b->votes, $a->votes);
            }
        );

        $output = '';
        $output .= '<div class="boot">';
        $output .= '<table id="pc-elections-results" class="table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>Name</th>';
        $output .= '<th># Votes</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        foreach ($candidates as $candidate) {
            $output .= '<tr>';
            $output .= '<td>' . sanitize_text_field($candidate->name) . '</td>';
            $output .= '<td>' . sanitize_text_field($candidate->votes) . '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';

        $output .= '</div>';

        return $output;
    }

    public function ms_speakers($atts)
    {

        // If we set 'tagged' argument, then we should only show speakers which have at least 1 tag match
        // Comma separated list
        extract(shortcode_atts(array(
            'tagged' => ''
        ), $atts));



        if ($tagged) {
            $tags = explode(',', $tagged);
            $tags = array_map('trim', $tags);
        } else {
            $tags = false;
        }

        $speaker_slug = sanitize_title(get_query_var('speaker_slug'));
        // Are we showing a specific speaker?
        if ($speaker_slug) {
            $speaker = mps_get_speaker_by_slug($speaker_slug);
            if ($speaker) {
                // We have a speaker
                // Has their profile been reviewed?
                if ($speaker->allowed) {
                    // We should display the speaker bio
                    // Are we being forced to show a specific language?
                    $locale = get_query_var('mps_lang', mps_get_short_current_locale());
                    $content = '';

                    // If there are tags specified for this speaker, we should display them
                    if ($speaker->tags) {
                        $tags = explode(',', $speaker->tags);
                        $tags = array_map('trim', $tags);
                        $content .= '<div class="boot">';
                        $tag_html = [];
                        foreach ($tags as $tag) {
                            $tag_html[] = '<span class="label label-primary">' . $tag . '</span>';
                        }
                        $content .= implode(' ', $tag_html);
                        $content .= '</div>';
                    }

                    // If we have more than 1 bio language available, we should display the 'language switcher'
                    if (count($speaker->bio_texts) > 1) {
                        $content .= '<div class="boot">';
                        foreach ($speaker->bio_texts as $language => $bio) {
                            $button = '<a class="nounderline btn btn-xs btn-default" href="';
                            $button .= get_permalink() . $speaker_slug . '/' . $language . '/';
                            $button .= '">' . sanitize_text_field(strtoupper($language)) . '</a>';
                            $content .= $button;
                        }
                        $content .= '</div>';
                        $content .= '<br>';
                    }
                    if (array_key_exists($locale, $speaker->bio_texts)) {
                        $content .= nl2br(escape_multiline_text($speaker->bio_texts[$locale]));
                        return $content;
                    } else {
                        $content .= "<i>" . __("A bio for this speaker is not currently available in this language", "meeting-support"). "</i>";
                        return $content;
                    }
                } else {
                    return "<i>" . __("This speaker profile is pending review", "meeting-support"). "</i>";
                }
            } else {
                return '<i>' . __("Speaker not found", "meeting-support") . '</i>';
            }
        } else {
            // Get all speakers
            $speakers = mps_get_all_speakers();

            // Only show speakers which have been approved
            $speakers = array_filter($speakers, function ($speaker) {
                $locale = get_query_var('mps_lang', mps_get_short_current_locale());
                return ($speaker->allowed && array_key_exists($locale, $speaker->bio_texts));
            });

            $content = '';
            $content .= '<table id="speakers-table" class="table">';

            foreach ($speakers as $speaker) {
                // Do we need to filter the results to a certain tag?
                if ($tags) {
                    $speaker_tags = explode(',', $speaker->tags);
                    $speaker_tags = array_map('trim', $speaker_tags);
                    // Do any of the arrays intersect?
                    $matches = array_intersect($tags, $speaker_tags);
                    if (count($matches) == 0) {
                        // There are no matches, we shouldn't show this speaker
                        continue;
                    }
                }
                $content .= '<tr><td data-filter="' . sanitize_title($speaker->slug) . ' ' . sanitize_text_field($speaker->name) . '">';
                $content .= '<a href="' . $speaker->slug . '">';
                $content .= sanitize_text_field($speaker->name);
                $content .= '</a>';
                $content .= '</td></tr>';
            }
            $content .= '</table>';
            return $content;
        }
    }

    public function ms_speaker_bio()
    {
        if ($this->auth->user) {
            $content = mps_speaker_bio_upload_form($this->auth->user);
        } else {
            $content = do_shortcode('[user_links]');
        }
        return $content;
    }


    public function ms_session_archives()
    {

        $output = '';
        global $wp_query;
        if (!is_null($video = $wp_query->query_vars['video'])) {
            // Are we looking at /archives/video/$video?
            $video = mps_get_video($this->auth->auth_method, $video);
            $uploaddir = wp_upload_dir();
            $path = $uploaddir['basedir'];
            $videodir = realpath($path.'/../../archive/video/');
            if (!$videodir) {
                return 'Video directory does not exist';
            }

            // Show the video player
            if (file_exists($videodir.'/'.$video->filename)) {
                $incomplete_videos = mps_get_option('incomplete_videos', array());
                $flv_vid = $video->filename;
                $mp4_vid = str_replace('.flv', '.mp4', $video->filename);
                $output .= "<h2>".htmlspecialchars(stripslashes($video->presenter_name." - ".$video->presentation_title))."</h2>\n";
                if (in_array($video->id, $incomplete_videos)) {
                    $output .= '<div class="boot"><div class="alert alert-danger">' . __("This video is incomplete and is currently being repaired.", 'meeting-support') . '</div></div>';
                }
                if (!file_exists($videodir.'/'.$mp4_vid)) {
                    $output .= '<div class="boot"><div class="alert alert-danger">You need Flash to play this video. We\'ll upload an MP4 version soon. </div></div>';
                }
                $output .= '<div class="flowplayer is-splash" style="background-color:#ffffff; background-image:url(' . get_stylesheet_directory_uri() . '/images/webcast.png)">'.PHP_EOL;
                $output .= '    <video>';
                if (file_exists($videodir.'/'.$mp4_vid)) {
                    // Only include the mp4 file as a source if it exists
                    $output .= '        <source type="video/mp4" src="' . home_url() . '/archive/video/'.urlencode($mp4_vid).'"/>'.PHP_EOL;
                }
                $output .= '        <source type="video/flash" src="' . home_url() . '/archive/video/'.urlencode($flv_vid).'"/>'.PHP_EOL;
                $output .= "    </video>".PHP_EOL;
                $output .= "</div>".PHP_EOL;



                // Show associated presentations
                if ($video->presentation_id == 0 || $video->session_id == 0) {
                    $presentations = array();
                } else {
                    $presentations = ms_get_slot_presentations($video->presentation_id);
                }

                $output .= '<div class="boot">';
                $output .= '<br />';
                if (file_exists($videodir.'/'.$mp4_vid)) {
                    $output .= '<a href="' . home_url() . '/archive/video/'.urlencode($mp4_vid).'" class="nounderline btn btn-success btn-xs">Download Video <i class="fa fa-video-camera"></i></a>';
                    $output .= '<br />';
                }
                if (!empty($presentations)) {
                    $output .= '<table class="dataTable table table-striped">';
                    $output .= '<thead>';
                    $output .= '<tr>';
                    $output .= '<th>Presenter Name</th>';
                    $output .= '<th>Presentation Title</th>';
                    $output .= '<th><i class="fa fa-download"></i></th>';
                    $output .= '<th>Date Added</th>';
                    $output .= '</tr>';
                    $output .= '</thead>';
                    $output .= '<tbody>';
                    foreach ($presentations as $presentation) {
                        $output .= '<tr>';
                        $output .= '<td>'.htmlspecialchars(stripslashes($presentation->author_name)).'</td>';
                        $output .= '<td>'.htmlspecialchars(stripslashes($presentation->title)).'</td>';
                        $files = json_decode($presentation->filename);
                        $output .= '<td>';
                        foreach ($files as $file) {
                            $output .= '<a href="' . home_url() . '/presentations/'.urlencode($file).'">';
                            $output .= '<i class="download-icon fa '.get_file_icon_class($file).'"></i>';
                            $output .= '</a>';
                            $output .= '&nbsp;';
                        }
                        $output .= '</td>';
                        $output .= '<td>'.date('Y-m-d', strtotime($presentation->submission_date)).'</td>';
                        $output .= '</tr>';
                    }
                    $output .= '<tbody>';
                    $output .= '</table>';
                }
                $output .= '</div>';

                $output .= "";
            } else {
                $output .= "<p>The specified file was not found (".$video->filename.")</p>";
            }
            return $output;
        }

        if (!is_null($chat = $wp_query->query_vars['chat'])) {
            // Are we looking at /archives/chat/$chat?
            $uploaddir = wp_upload_dir();
            $path = $uploaddir['basedir'];
            $chatdir = realpath($path.'/../../archive/chat/');
            if (!$chatdir) {
                return 'Chat directory does not exist';
            }
            $textfile = $chatdir.'/'.$chat.'.log';
            if (file_exists($textfile)) {
                // File exists, let's show it!
                $output .= nl2br(htmlspecialchars(file_get_contents($textfile)));
            } else {
                $output .= '<p>The specified file was not found</p>';
            }
            return $output;
        }
        if (!is_null($steno = $wp_query->query_vars['steno'])) {
            // Are we looking at /archives/steno/$steno?
            $uploaddir = wp_upload_dir();
            $path = $uploaddir['basedir'];
            $stenodir = realpath($path.'/../../archive/steno/');
            if (!$stenodir) {
                return 'Steno directory does not exist';
            }
            $stenofile = $stenodir.'/'.$steno.'.txt';
            if (file_exists($stenofile)) {
                // File exists, let's show it!
                $output .= nl2br(htmlspecialchars(file_get_contents($stenofile)));
            } else {
                $output .= '<p>The specified file was not found</p>';
            }
            return $output;
        }

        // Not showing a specific thing, let's show the tab list
        $sessions = mps_get_all_sessions();
        $active_day = false;
        $sortedsessions = [];
        foreach ($sessions as $session) {
            // Is there a session today? We want a flag for some JS later on
            if ($active_day == false) {
                if (strpos($session->start_time, date('Y-m-d')) !== false) {
                    $active_day = true;
                }
            }
            if ($session->is_intermission || $session->is_social) {
                continue;
            }
            if (trim($session->name) != '') {
                $sortedsessions[date('l', strtotime($session->start_time))][] = $session;
            }
        }
        $output = '<div class="boot">';
        $output .= '<ul id="archive-tabs" class="nav nav-tabs" role="tablist">';
        foreach ($sortedsessions as $day => $v) {
            if (date('Y-m-d', strtotime($v[0]->start_time)) > date('Y-m-d')) {
                continue;
            }
            $shortname = strtolower(substr($day, 0, 3));
            if ($shortname == 'mon') {
                $output .= '<li role="presentation" class="active"><a href="#' . $shortname . '" role="tab" data-toggle="tab">' . $day . '</a></li>';
            } else {
                $output .= '<li role="presentation" class="active"><a href="#' . $shortname . '" role="tab" data-toggle="tab">' . $day . '</a></li>';
            }
        }
        $output .= '</ul>';
        $output .= '<div class="clear"></div>';
        $output .= '<div class="tab-content">';
        foreach ($sortedsessions as $day => $v) {
            if (date('Y-m-d', strtotime($v[0]->start_time)) > date('Y-m-d')) {
                continue;
            }
            $shortname = strtolower(substr($day, 0, 3));

            $output .= '<div role="tabpanel" class="tab-pane active" id="'.$shortname.'">';
            $output .= '<h3>'.$day.'</h3>';
            $output .= '<hr />';
            $output .= show_archive_day_list($sortedsessions[$day]);
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '</div>';

        if ($active_day == true) {
            $output .= "
        <script>
            jQuery(document).ready(function() {
                if (window.location.hash == '') {
                    jQuery('.nav-tabs a[href=\"#".strtolower(substr(date('l'), 0, 3))."\"]').tab('show');
                }
            });
        </script>
        ";
        } else {
            $output .= "
        <script>
            jQuery(document).ready(function() {
                if (window.location.hash == '') {
                    jQuery('.nav-tabs a[href=\"#mon\"]').tab('show');
                }
            });
        </script>
        ";
        }


        return $output;
    }
}


// Load the instance
$mps_shortcodes = new Meeting_Support_Shortcodes($this->auth);

// Load shortcodes
add_shortcode('crowd_links', array( $mps_shortcodes, 'mps_user_links' ));
add_shortcode('user_links', array( $mps_shortcodes, 'mps_user_links' ));
add_shortcode('user_login', array( $mps_shortcodes, 'mps_user_login' ));
add_shortcode('ripe_user_links', array( $mps_shortcodes, 'mps_ripe_user_links' ));
add_shortcode('user_register', array( $mps_shortcodes, 'mps_user_register' ));
add_shortcode('user_profile', array( $mps_shortcodes, 'mps_user_profile' ));
add_shortcode('showstar', array( $mps_shortcodes, 'ms_show_star_image' ));
add_shortcode('session_table', array( $mps_shortcodes, 'ms_session_table' ));
add_shortcode('session_table_responsive', array( $mps_shortcodes, 'ms_session_table_responsive' ));
add_shortcode('session_table_vertical', array( $mps_shortcodes, 'ms_session_table_vertical' ));
add_shortcode('agenda_vertical', array( $mps_shortcodes, 'ms_agenda_vertical' ));
add_shortcode('presentations_vertical', array( $mps_shortcodes, 'ms_presentations_vertical' ));
add_shortcode('session_table_legend', array( $mps_shortcodes, 'ms_session_table_legend' ));
add_shortcode('attendee_list', array( $mps_shortcodes, 'ms_show_attendee_list' ));
add_shortcode('submission_form', array( $mps_shortcodes, 'ms_submission_form' ));
add_shortcode('session_list', array( $mps_shortcodes, 'ms_show_session_list' ));
add_shortcode('submission_list', array( $mps_shortcodes, 'ms_show_submissions' ));
add_shortcode('submission_sort', array( $mps_shortcodes, 'ms_sort_submissions_form' ));
add_shortcode('submission_email', array( $mps_shortcodes, 'ms_submission_email' ));
add_shortcode('contact_form', array( $mps_shortcodes, 'ms_contact_form' ));
add_shortcode('contactform', array( $mps_shortcodes, 'ms_contact_form' ));
add_shortcode('sponsors', array( $mps_shortcodes, 'ms_sponsors' ));
add_shortcode('presentation_upload', array( $mps_shortcodes, 'ms_presentation_upload_form' ));
add_shortcode('my_presentations', array( $mps_shortcodes, 'ms_my_presentations' ));
add_shortcode('pcss_homepage', array( $mps_shortcodes, 'ms_pcss_homepage' ));
add_shortcode('session_slots', array( $mps_shortcodes, 'ms_session_slots' ));
add_shortcode('user_is_logged_in', array( $mps_shortcodes, 'ms_user_is_logged_in' ));
add_shortcode('user_is_not_logged_in', array( $mps_shortcodes, 'ms_user_is_not_logged_in' ));
add_shortcode('pcss_user_can', array( $mps_shortcodes, 'ms_pcss_user_can' ));
add_shortcode('agenda_pdf_url', array( $mps_shortcodes, 'ms_agenda_pdf_url' ));
add_shortcode('agenda_ics_url', array( $mps_shortcodes, 'ms_agenda_ics_url' ));
add_shortcode('live_video', array( $mps_shortcodes, 'ms_live_video' ));
add_shortcode('live_chat', array( $mps_shortcodes, 'ms_live_chat' ));
add_shortcode('slots_list', array( $mps_shortcodes, 'ms_presentation_list' ));
add_shortcode('roomquery', array( $mps_shortcodes, 'ms_room_query' ));
add_shortcode('live_recorder', array( $mps_shortcodes, 'ms_live_recorder' ));
add_shortcode('live_transcript', array( $mps_shortcodes, 'ms_live_transcript' ));
add_shortcode('session_archives', array( $mps_shortcodes, 'ms_session_archives' ));
add_shortcode('pc_elections_form', array( $mps_shortcodes, 'ms_pc_elections_form' ));
add_shortcode('pc_elections_results', array( $mps_shortcodes, 'ms_pc_elections_results' ));
add_shortcode('speakers', array( $mps_shortcodes, 'ms_speakers' ));
add_shortcode('speaker_bio', array( $mps_shortcodes, 'ms_speaker_bio' ));
