<?php

function dd()
{
    array_map(function ($x) {
        var_dump($x);
    }, func_get_args());
    die;
}

function ms_crowd_sanitise(&$value)
{
    if (is_string($value)) {
        $value = htmlspecialchars($value);
    }
}

function mps_get_option($option, $default = false)
{
    return get_option('mps_' . $option, $default);
}


function mps_update_option($option, $value)
{
    return update_option('mps_' . $option, $value);
}

function removeslashes($string)
{
    $string = implode("", explode("\\", $string));
    return stripslashes(trim($string));
}

function escape_multiline_text($contents)
{
    /**
     * The same as sanitize_text_field, but doesn't strip lines
     */
    return stripslashes(implode(PHP_EOL, array_map('sanitize_text_field', explode(PHP_EOL, $contents))));
}

function invisible_recaptcha_validation($token)
{
    $secret_key = mps_get_option('meeting_irecaptcha_secret_key');
    $remote_ip = $_SERVER['REMOTE_ADDR'];

    $recaptcha_validation_url = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$token&remoteip=$remote_ip");
    $result = json_decode($recaptcha_validation_url, true);

    if ($result['success'] == 1) {
        return true;
    } else {
        return false;
    }
}

function ms_get_slot_presentations($slotid)
{
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ms_presentations WHERE slot_id = %d", $slotid));
}

function mps_get_all_pc_candidates()
{
    global $wpdb;
    $candidates = $wpdb->get_results("SELECT * FROM " .$wpdb->prefix . "ms_pc_candidates");
    return $candidates;
}

function mps_get_all_local_users()
{
    global $wpdb;
    $users = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "ms_users");
    return $users;
}

function mps_get_pc_candidate_votes($auth, $candidate_id, $days = 7)
{

    global $wpdb;
    $votes = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ms_pc_votes WHERE candidate = %d", $candidate_id));
    $aged_votes = 0;
    $count_votes = 0;
    $acceptable_timestamp = (time() - ($days * 24 * 60 * 60)) * 1000;

    // If we want to do a deep check, we want to see how many RIPE NCC Access accounts which voted were created in the last $days days
    foreach ($votes as $vote) {
        $voter_info = $auth->getCrowdUserByUUID($vote->uuid);
        if ($voter_info) {
            $count_votes++;
            if ((int) $voter_info['timeofregistration'] < $acceptable_timestamp) {
                //dd($voter_info);
                $aged_votes++;
            }
        }
    }

    $return = ['votes' => $count_votes, 'aged_votes' => $aged_votes];
    return $return;
}

function mps_get_pc_votes_from_uuid($uuid)
{
    global $wpdb;
    $votes = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ms_pc_votes WHERE uuid = %s", $uuid));
    return $votes;
}

function mps_clear_pc_votes_for_uuid($uuid)
{
    global $wpdb;
    $deleted = $wpdb->delete($wpdb->prefix . "ms_pc_votes", ['uuid' => $uuid]);
    return $deleted;
}

function mps_add_pc_vote_for_uuid($uuid, $candidate_id)
{
    global $wpdb;
    $wpdb->insert($wpdb->prefix . "ms_pc_votes", ['uuid' => $uuid, 'candidate' => $candidate_id]);
    return true;
}

function get_file_icon_class($filename)
{
    /**
     * return the html class (FontAwesome) for the filename
     */

    $info = new SplFileInfo(strtolower($filename));
    $extension = $info->getExtension();

    switch ($extension) {
        case 'png':
            return 'fa-file-image-o';
            break;

        case 'jpg':
            return 'fa-file-image-o';
            break;

        case 'ppt':
            return 'fa-file-powerpoint-o';
            break;

        case 'key':
            return 'fa-file-image-o';
            break;

        case 'pdf':
            return 'fa-file-pdf-o';
            break;

        case 'doc':
            return 'fa-file-word-o';
            break;

        case 'xls':
            return 'fa-file-excel-o';
            break;

        case 'docx':
            return 'fa-file-word-o';
            break;

        case 'xlsx':
            return 'fa-file-excel-o';
            break;

        case 'pptx':
            return 'fa-file-powerpoint-o';
            break;

        case 'odt':
            return 'fa-file-text-o';
            break;

        case 'odp':
            return 'fa-file-image-o';
            break;

        case 'txt':
            return 'fa-file-text-o';
            break;

        case 'zip':
            return 'fa-file-archive-o';
            break;

        default:
            return 'fa-file-o';
            break;
    }
}

/**
 * Function to create and display error and success messages
 * @access public
 * @param string session name
 * @param string message
 * @param string display class
 * @param bool autoclose
 * @return string message
 */
function mps_flash($name = '', $message = '', $class = 'success', $autoclose = false)
{
    //We can only do something if the name isn't empty
    if (! empty($name)) {
        //No message, create it
        if (! empty($message) && empty($_SESSION[$name])) {
            if (! empty($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
            if (! empty($_SESSION[$name.'_class'])) {
                unset($_SESSION[$name.'_class']);
            }
            if (! empty($_SESSION[$name.'_autoclose'])) {
                unset($_SESSION[$name.'_autoclose']);
            }
            $_SESSION[$name] = $message;
            $_SESSION[$name.'_class'] = $class;
            $_SESSION[$name.'_autoclose'] = $autoclose;
        } elseif (! empty($_SESSION[$name]) && empty($message)) {
            $class = ! empty($_SESSION[$name.'_class']) ? $_SESSION[$name.'_class'] : 'success';
            $message = $_SESSION[$name];
            $autoclose = $_SESSION[$name.'_autoclose'];
            unset($_SESSION[$name.'_autoclose']);
            unset($_SESSION[$name.'_class']);
            unset($_SESSION[$name]);
            if ($autoclose !== false) {
                return '<div class="boot"><div class="alert alert-autoclose alert-' . $class . '"><p>' . $message . '</p></div></div>';
            } else {
                $alert = '<div class="boot"><div class="alert alert-' . $class . '"><p>' . $message . '</p></div></div>';
                return $alert;
            }
        }
    }
}

function make_pretty_session_array($session)
{
    $tidy_session = [];
    $tidy_session['name'] = $session->name;
    $tidy_session['timeStart'] = date('H:i', strtotime($session->start_time));
    $tidy_session['timeEnd'] = date('H:i', strtotime($session->end_time));
    $tidy_session['location'] = get_real_room_name($session->room);
    $tidy_session['tracks'] = [];

    return $tidy_session;
}

/**
 * Determines if there are any sessions for the given time.
 *
 * Returns false if none, or returns an array of objects with current sessions. Sort the rooms so when we return multiple sessions,
 * they are in the right order.
 *
 * @param object $datetime  DateTime object with the time to check
 * @return mixed
 */
function mps_sessions_at_datetime($datetime, $hide_socials = true)
{
    global $wpdb;

    $current_sessions = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_sessions`
        WHERE `start_time` <= '" . $datetime->format('Y-m-d H:i:s') . "'
        AND `end_time` > '" . $datetime->format('Y-m-d H:i:s') . "'
        ORDER BY FIELD(room, 'main', 'side', 'terminal', 'tricolour')
        "
    );

    if ($hide_socials) {
        $current_sessions = array_filter($current_sessions, function ($session) {
            return ! $session->is_social;
        });
    }

    return $current_sessions;
}

function mps_get_all_sessions()
{

    global $wpdb;

    $sessions = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_sessions` ORDER BY start_time ASC, FIELD(room, 'main', 'side', 'terminal', 'tricolour')
        "
    );

    return $sessions;
}

function ms_get_session_data($session_id)
{

    global $wpdb;

    $session_id = (int) $session_id;

    $session = $wpdb->get_row(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_sessions`
        WHERE `id` = '" . $session_id . "'
        "
    );

    return $session;
}

function mps_get_submission($submission_id)
{

    global $wpdb;

    $submission_id = (int) $submission_id;

    $submission = $wpdb->get_row(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions`
        WHERE `id` = '" . $submission_id . "'
        "
    );

    return $submission;
}

function mps_get_submission_archives($submission_id)
{
    global $wpdb;

    $submission_id = (int) $submission_id;

    $submission = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions_archive`
        WHERE `submission_id` = '" . $submission_id . "'
        ORDER BY `timestamp` DESC
        "
    );

    return $submission;
}

function mps_get_submission_archive($archive_id)
{
    global $wpdb;

    $archive_id = (int) $archive_id;

    $archive = $wpdb->get_row(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions_archive`
        WHERE `id` = '" . $archive_id . "'
        "
    );

    return $archive;
}

function mps_get_previous_submission_archive($archive)
{
    global $wpdb;

    $previous_submission_archive = $wpdb->get_row(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions_archive`
        WHERE `id` < '" . $archive->id . "'
        AND `submission_id` = '" . $archive->submission_id . "'
        ORDER BY `timestamp` DESC
        "
    );

    return $previous_submission_archive;
}

function mps_get_all_speakers()
{

    global $wpdb;

    $speakers = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_speakers`
        "
    );

    // Iterate through the speakers to process the bio_texts into a nice managable
    array_walk($speakers, 'mps_process_speaker');

    return $speakers;
}

function mps_get_speaker_by_slug($speaker_slug)
{
    global $wpdb;

    $speaker = $wpdb->get_row(
        $wpdb->prepare(
            "
                SELECT * FROM `" . $wpdb->base_prefix . "ms_speakers`
                WHERE `slug` = '%s'
            ",
            $speaker_slug
        )
    );

    if ($speaker) {
        $speaker = mps_process_speaker($speaker);
    }

    return $speaker;
}


function mps_get_speaker_by_uuid($uuid)
{
    global $wpdb;

    $speaker = $wpdb->get_row(
        $wpdb->prepare(
            "
                SELECT * FROM `" . $wpdb->base_prefix . "ms_speakers`
                WHERE `uuid` = '%s'
            ",
            $uuid
        )
    );

    if ($speaker) {
        $speaker = mps_process_speaker($speaker);
    }

    return $speaker;
}

function mps_get_speaker($speaker_id)
{
    global $wpdb;

    $speaker = $wpdb->get_row(
        $wpdb->prepare(
            "
                SELECT * FROM `" . $wpdb->base_prefix . "ms_speakers`
                WHERE `id` = '%d'
            ",
            $speaker_id
        )
    );

    if ($speaker) {
        $speaker = mps_process_speaker($speaker);
    }

    return $speaker;
}

function mps_process_speaker($speaker)
{
    $speaker->bio_texts = json_decode($speaker->bio_texts, true);
    $speaker->bio_texts_draft = json_decode($speaker->bio_texts_draft, true);
    if (property_exists($speaker, 'tags')) {
        $decoded = json_decode($speaker->tags);
        if ($decoded) {
            $speaker->tags = implode(', ', json_decode($decoded));
        } else {
            $speaker->tags = '';
        }
    }
    return $speaker;
}

/**
 * @param  string $slug The proposed slug which should be checked
 * @return string The slug which will be unique
 */
function mps_get_unique_speaker_slug($slug)
{
    $speaker = mps_get_speaker_by_slug($slug);
    if ($speaker) {
        // There is already a speaker with this slug, we need to make a new one
        $slug_attempt = mps_create_new_speaker_slug($speaker->slug);
        return mps_get_unique_speaker_slug($slug_attempt);
    } else {
        // This slug is unique, it's good to go
        return $slug;
    }
}

function mps_create_new_speaker_slug($slug)
{
    mps_log('Trying to create a new slug');
    mps_log($slug);
    $pattern_first = '/[a-z]+$/';
    $pattern_dashed = '/(.*)-(\d)+$/';

    if (preg_match($pattern_first, $slug)) {
        //dd($slug);
        return $slug . '-1';
    }

    preg_match($pattern_dashed, $slug, $matches);
    mps_log(json_encode($matches));
    //dd($matches);
    return $matches[1] . '-' . ($matches[2] + 1);
}


function mps_get_latest_submission_archive($submission_id)
{
    global $wpdb;

    $latest_submission_archive = $wpdb->get_row(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions_archive`
        WHERE `submission_id` = '" . $submission_id . "'
        ORDER BY `timestamp` DESC
        "
    );

    return $latest_submission_archive;
}


function mps_get_presentation_rating_stats($ratings)
{
    /**
     * function to create a stats array for the given ratings
     * average, count, etc
     */
    $stats = [];
    $stats['rating_content_count'] = 0;
    $stats['rating_delivery_count'] = 0;
    $stats['rating_content_total'] = 0;
    $stats['rating_delivery_total'] = 0;
    $stats['rating_content_average'] = 0;
    $stats['rating_delivery_average'] = 0;

    foreach ($ratings as $rating) {
        $stats['rating_content_total'] += (int) $rating->rating_content;
        $stats['rating_delivery_total'] += (int) $rating->rating_presenter;
        if ((int) $rating->rating_content > 0) {
            $stats['rating_content_count']++;
        }
        if ((int) $rating->rating_presenter > 0) {
            $stats['rating_delivery_count']++;
        }
    }
    if ($stats['rating_content_count'] > 0) {
        $stats['rating_content_average'] = round($stats['rating_content_total'] / $stats['rating_content_count'], 1);
    }
    if ($stats['rating_delivery_count'] > 0) {
        $stats['rating_delivery_average'] = round($stats['rating_delivery_total'] / $stats['rating_delivery_count'], 1);
    }
    return $stats;
}

function mps_get_all_submissions()
{

    global $wpdb;

    $submissions = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions`
        "
    );

    return $submissions;
}


function mps_get_new_submissions_since($seconds)
{
    global $wpdb;

    $seconds = (int) $seconds;

    $time_ago = date('Y-m-d H:i:s', strtotime('-' . $seconds . ' seconds'));

    $submissions = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions`
        WHERE `submission_date` > '$time_ago'
        AND `updated_date` = `submission_date`
        "
    );

    foreach ($submissions as $submission) {
        $submission->url = add_query_arg('submission_id', $submission->id, network_site_url('/pcss/' . ms_get_submission_type_slug($submission->submission_type)));
    }

    return $submissions;
}

function mps_get_updated_submissions_since($seconds)
{
    global $wpdb;

    $seconds = (int) $seconds;

    $time_ago = date('Y-m-d H:i:s', strtotime('-' . $seconds . ' seconds'));

    $submissions = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions`
        WHERE `updated_date` > '$time_ago'
        AND `updated_date` != `submission_date`
        "
    );

    foreach ($submissions as $submission) {
        $submission->url = add_query_arg('submission_id', $submission->id, network_site_url('/pcss/' . ms_get_submission_type_slug($submission->submission_type)));
    }

    return $submissions;
}

function mps_get_pc_submission_ratings_since($seconds)
{
    global $wpdb;

    $seconds = (int) $seconds;

    $time_ago = date('Y-m-d H:i:s', strtotime('-' . $seconds . ' seconds'));


    $auth = Meeting_Support_Auth::getInstance();
    $uuid = $auth->user['uuid'];

    $ratings = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submission_ratings`
        WHERE `timestamp` > '$time_ago'
        AND uuid != '$uuid'
        "
    );

    foreach ($ratings as $rating) {
        if ($auth->auth_method == 'local') {
            $details = (array) $auth->getUserByUUID($rating->uuid);
        } else {
            $details = $auth->getCrowdUserByUUID($rating->uuid);
        }

        $submission = mps_get_submission($rating->submission_id);

        $rating->submission_name = $submission->submission_title;
        $rating->url = add_query_arg('submission_id', $submission->id, network_site_url('/pcss/' . ms_get_submission_type_slug($submission->submission_type)));
        $rating->rater_name = $details['name'];
    }

    return $ratings;
}

function mps_get_all_presentation_ratings()
{
    global $wpdb;

    $ratings = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_presentation_ratings`
        "
    );

    return $ratings;
}


function mps_get_sponsor_section($section_id)
{
    $section_id = (int) $section_id;

    global $wpdb;

    $section = $wpdb->get_row(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_sponsor_sections`
        WHERE `id` = '" . $section_id . "'
        "
    );

    return $section;
}

function mps_set_submission_tags($sub_id, $tags)
{

    $sub_id = (int) $sub_id;

    if ($sub_id == 0) {
        return false;
    }

    $tagged_submissions = mps_get_option('tagged_submissions', array());

    if ($tags[0] == '' || empty($tags)) {
        unset($tagged_submissions['submission_' . $sub_id]);
        mps_log('Submission #' . $sub_id . ' removed all tags');
    } else {
        $tagged_submissions['submission_' . $sub_id] = $tags;
        mps_log('Submission #' . $sub_id . ' tagged with the following tags: ' . implode(', ', $tags));
    }

    mps_update_option('tagged_submissions', $tagged_submissions);


    return $tagged_submissions['submission_' . $sub_id];
}

function mps_get_submission_tags($submission_id)
{
    $submission_id = (int) $submission_id;
    $tagged_submissions = mps_get_option('tagged_submissions', array());
    if (isset($tagged_submissions['submission_' . $submission_id])) {
        return $tagged_submissions['submission_' . $submission_id];
    } else {
        return [];
    }
}

function mps_list_submission_tags($submission_id)
{
    $output = '';
    $tags = mps_get_submission_tags($submission_id);
    foreach ($tags as $tag) {
        $output .= '<a href="' . home_url()  . '/pcss/tagged/?tagged=' . urlencode($tag) . '" class="submission-label btn btn-primary btn-xs">' . sanitize_text_field($tag) . '</a> ';
    }

    return $output;
}

function mps_list_used_submission_tags()
{
    $tagged_submissions = mps_get_option('tagged_submissions', array());
    $tags = [];
    foreach ($tagged_submissions as $submission_tags) {
        foreach ($submission_tags as $tag) {
            if (! in_array($tag, $tags)) {
                $tags[] = $tag;
            }
        }
    }

    // TODO: Could sort by popularity at one point?
    asort($tags);

    return $tags;
}

function mps_get_available_submission_tags()
{
    $default_tags = mps_default_submission_tags();

    $set_tags = mps_get_option('pc_submission_tags');

    $set_tags = explode(',', $set_tags);

    $set_tags = array_map('trim', $set_tags);

    $tags = array_merge($default_tags, $set_tags);

    $tags = array_unique($tags);

    asort($tags);

    return $tags;
}

function mps_default_submission_tags()
{
    $tags = array(
        'DNS',
        'BGP',
        'IoT',
        'Certification',
        'Governance',
        'IPv4',
        'IXP',
        'RPKI',
        'Measurements',
        'NAT',
        'RIS',
        'Security',
        'Statistics',
        'Transfers',
        'IP Brokers',
        'Commercial'
    );

    return $tags;
}


function mps_get_section_sponsors($section_id)
{

    $section_id = (int) $section_id;

    global $wpdb;

    $section = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_sponsors`
        WHERE `section_id` = '" . $section_id . "'
        ORDER BY sort_order ASC
        "
    );

    return $section;
}

function mps_countries_abbreviations()
{
    $countries = array(
        'Afghanistan' => 'AF',
        'Aland Islands' => 'AX',
        'Albania' => 'AL',
        'Algeria' => 'DZ',
        'American Samoa' => 'AS',
        'Andorra' => 'AD',
        'Angola' => 'AO',
        'Anguilla' => 'AI',
        'Antarctica' => 'AQ',
        'Antigua and Barbuda' => 'AG',
        'Argentina' => 'AR',
        'Armenia' => 'AM',
        'Aruba' => 'AW',
        'Australia' => 'AU',
        'Austria' => 'AT',
        'Azerbaijan' => 'AZ',
        'Bahamas the' => 'BS',
        'Bahrain' => 'BH',
        'Bangladesh' => 'BD',
        'Barbados' => 'BB',
        'Belarus' => 'BY',
        'Belgium' => 'BE',
        'Belize' => 'BZ',
        'Benin' => 'BJ',
        'Bermuda' => 'BM',
        'Bhutan' => 'BT',
        'Bolivia' => 'BO',
        'Bosnia And Herzegovina' => 'BA',
        'Botswana' => 'BW',
        'Bouvet Island (Bouvetoya)' => 'BV',
        'Brazil' => 'BR',
        'British Indian Ocean Territory (Chagos Archipelago)' => 'IO',
        'British Virgin Islands' => 'VG',
        'Brunei Darussalam' => 'BN',
        'Bulgaria' => 'BG',
        'Burkina Faso' => 'BF',
        'Burundi' => 'BI',
        'Cambodia' => 'KH',
        'Cameroon' => 'CM',
        'Canada' => 'CA',
        'Cape Verde' => 'CV',
        'Cayman Islands' => 'KY',
        'Central African Republic' => 'CF',
        'Chad' => 'TD',
        'Chile' => 'CL',
        'China' => 'CN',
        'Christmas Island' => 'CX',
        'Cocos (Keeling) Islands' => 'CC',
        'Colombia' => 'CO',
        'Comoros the' => 'KM',
        'Congo' => 'CD',
        'Congo the' => 'CG',
        'Cook Islands' => 'CK',
        'Costa Rica' => 'CR',
        'Cote d\'Ivoire' => 'CI',
        'Croatia' => 'HR',
        'Cuba' => 'CU',
        'Cyprus' => 'CY',
        'Czech Republic' => 'CZ',
        'Denmark' => 'DK',
        'Djibouti' => 'DJ',
        'Dominica' => 'DM',
        'Dominican Republic' => 'DO',
        'Ecuador' => 'EC',
        'Egypt' => 'EG',
        'El Salvador' => 'SV',
        'Equatorial Guinea' => 'GQ',
        'Eritrea' => 'ER',
        'Estonia' => 'EE',
        'Ethiopia' => 'ET',
        'Faroe Islands' => 'FO',
        'Falkland Islands (Malvinas)' => 'FK',
        'Fiji the Fiji Islands' => 'FJ',
        'Finland' => 'FI',
        'France' => 'FR',
        'French Guiana' => 'GF',
        'French Polynesia' => 'PF',
        'French Southern Territories' => 'TF',
        'Gabon' => 'GA',
        'Gambia the' => 'GM',
        'Georgia' => 'GE',
        'Germany' => 'DE',
        'Ghana' => 'GH',
        'Gibraltar' => 'GI',
        'Greece' => 'GR',
        'Greenland' => 'GL',
        'Grenada' => 'GD',
        'Guadeloupe' => 'GP',
        'Guam' => 'GU',
        'Guatemala' => 'GT',
        'Guernsey' => 'GG',
        'Guinea' => 'GN',
        'Guinea-Bissau' => 'GW',
        'Guyana' => 'GY',
        'Haiti' => 'HT',
        'Heard Island and McDonald Islands' => 'HM',
        'Holy See (Vatican City State)' => 'VA',
        'Honduras' => 'HN',
        'Hong Kong' => 'HK',
        'Hungary' => 'HU',
        'Iceland' => 'IS',
        'India' => 'IN',
        'Indonesia' => 'ID',
        'Iran' => 'IR',
        'Iraq' => 'IQ',
        'Ireland' => 'IE',
        'Isle of Man' => 'IM',
        'Israel' => 'IL',
        'Italy' => 'IT',
        'Jamaica' => 'JM',
        'Japan' => 'JP',
        'Jersey' => 'JE',
        'Jordan' => 'JO',
        'Kazakhstan' => 'KZ',
        'Kenya' => 'KE',
        'Kiribati' => 'KI',
        'Korea' => 'KP',
        'Korea' => 'KR',
        'Kuwait' => 'KW',
        'Kyrgyz Republic' => 'KG',
        'Lao' => 'LA',
        'Latvia' => 'LV',
        'Lebanon' => 'LB',
        'Lesotho' => 'LS',
        'Liberia' => 'LR',
        'Libyan Arab Jamahiriya' => 'LY',
        'Liechtenstein' => 'LI',
        'Lithuania' => 'LT',
        'Luxembourg' => 'LU',
        'Macao' => 'MO',
        'Macedonia' => 'MK',
        'Madagascar' => 'MG',
        'Malawi' => 'MW',
        'Malaysia' => 'MY',
        'Maldives' => 'MV',
        'Mali' => 'ML',
        'Malta' => 'MT',
        'Marshall Islands' => 'MH',
        'Martinique' => 'MQ',
        'Mauritania' => 'MR',
        'Mauritius' => 'MU',
        'Mayotte' => 'YT',
        'Mexico' => 'MX',
        'Micronesia' => 'FM',
        'Moldova' => 'MD',
        'Monaco' => 'MC',
        'Mongolia' => 'MN',
        'Montenegro' => 'ME',
        'Montserrat' => 'MS',
        'Morocco' => 'MA',
        'Mozambique' => 'MZ',
        'Myanmar' => 'MM',
        'Namibia' => 'NA',
        'Nauru' => 'NR',
        'Nepal' => 'NP',
        'Netherlands Antilles' => 'AN',
        'Netherlands' => 'NL',
        'New Caledonia' => 'NC',
        'New Zealand' => 'NZ',
        'Nicaragua' => 'NI',
        'Niger' => 'NE',
        'Nigeria' => 'NG',
        'Niue' => 'NU',
        'Norfolk Island' => 'NF',
        'Northern Mariana Islands' => 'MP',
        'Norway' => 'NO',
        'Oman' => 'OM',
        'Pakistan' => 'PK',
        'Palau' => 'PW',
        'Palestinian Territory' => 'PS',
        'Panama' => 'PA',
        'Papua New Guinea' => 'PG',
        'Paraguay' => 'PY',
        'Peru' => 'PE',
        'Philippines' => 'PH',
        'Pitcairn Islands' => 'PN',
        'Poland' => 'PL',
        'Portugal' => 'PT',
        'Puerto Rico' => 'PR',
        'Qatar' => 'QA',
        'Reunion' => 'RE',
        'Romania' => 'RO',
        'Russian Federation' => 'RU',
        'Rwanda' => 'RW',
        'Saint Barthelemy' => 'BL',
        'Saint Helena' => 'SH',
        'Saint Kitts and Nevis' => 'KN',
        'Saint Lucia' => 'LC',
        'Saint Martin' => 'MF',
        'Saint Pierre and Miquelon' => 'PM',
        'Saint Vincent and the Grenadines' => 'VC',
        'Samoa' => 'WS',
        'San Marino' => 'SM',
        'Sao Tome and Principe' => 'ST',
        'Saudi Arabia' => 'SA',
        'Senegal' => 'SN',
        'Serbia' => 'RS',
        'Seychelles' => 'SC',
        'Sierra Leone' => 'SL',
        'Singapore' => 'SG',
        'Slovakia' => 'SK',
        'Slovenia' => 'SI',
        'Solomon Islands' => 'SB',
        'Somalia, Somali Republic' => 'SO',
        'South Africa' => 'ZA',
        'South Georgia and the South Sandwich Islands' => 'GS',
        'Spain' => 'ES',
        'Scotland' => 'Scotland',
        'Sri Lanka' => 'LK',
        'Sudan' => 'SD',
        'Suriname' => 'SR',
        'Svalbard & Jan Mayen Islands' => 'SJ',
        'Swaziland' => 'SZ',
        'Sweden' => 'SE',
        'Switzerland' => 'CH',
        'Syrian Arab Republic' => 'SY',
        'Taiwan' => 'TW',
        'Tajikistan' => 'TJ',
        'Tanzania' => 'TZ',
        'Thailand' => 'TH',
        'Timor-Leste' => 'TL',
        'Togo' => 'TG',
        'Tokelau' => 'TK',
        'Tonga' => 'TO',
        'Trinidad and Tobago' => 'TT',
        'Tunisia' => 'TN',
        'Turkey' => 'TR',
        'Turkmenistan' => 'TM',
        'Turks and Caicos Islands' => 'TC',
        'Tuvalu' => 'TV',
        'Uganda' => 'UG',
        'Ukraine' => 'UA',
        'United Arab Emirates' => 'AE',
        'United Kingdom' => 'GB',
        'United States' => 'US',
        'United States Minor Outlying Islands' => 'UM',
        'United States Virgin Islands' => 'VI',
        'Uruguay' => 'UY',
        'Uzbekistan' => 'UZ',
        'Vanuatu' => 'VU',
        'Venezuela' => 'VE',
        'Vietnam' => 'VN',
        'Wales' => 'GB',
        'Wallis and Futuna' => 'WF',
        'Western Sahara' => 'EH',
        'Yemen' => 'YE',
        'Zambia' => 'ZM',
        'Zimbabwe' => 'ZW',
        'Other' => ' '
    );
    return $countries;
}

function convertPHPSizeToBytes($sSize)
{

    if (is_numeric($sSize)) {
        return $sSize;
    }
    $sSuffix = substr($sSize, -1);
    $iValue = substr($sSize, 0, -1);
    switch (strtoupper($sSuffix)) {
        case 'P':
            $iValue *= 1024;
            // Fallthrough intentional
        case 'T':
            $iValue *= 1024;
            // Fallthrough intentional
        case 'G':
            $iValue *= 1024;
            // Fallthrough intentional
        case 'M':
            $iValue *= 1024;
            // Fallthrough intentional
        case 'K':
            $iValue *= 1024;
            break;
    }
    return $iValue;
}

function getMaximumFileUploadSize()
{
    $upload_max_filesize = convertPHPSizeToBytes(ini_get('upload_max_filesize'));
    $memory_limit = convertPHPSizeToBytes(ini_get('memory_limit'));
    $post_max_size = convertPHPSizeToBytes(ini_get('post_max_size'));
    $smallest = min($upload_max_filesize, $memory_limit, $post_max_size);
    return $smallest;
}

function pcss_user_can($cap, $auth = null)
{
    $pc_config = pc_config();

    if (! $auth) {
        $auth = Meeting_Support_Auth::getInstance();
    }

    if (! $auth->user) {
        return false;
    }

    $user = mps_get_pc_user($auth->user['uuid']);

    if ($user) {
        $accesslevel = $user->access_level;
    } else {
        $accesslevel = 4;
    }

    $permissions = $pc_config['user_roles'][$accesslevel];

    if (isset($permissions[$cap])) {
        return true;
    } else {
        return false;
    }
}

function pcss_message($msg_id = 'none')
{
    $auth = Meeting_Support_Auth::getInstance();
    $pc_config = pc_config();
    $pc_messages = $pc_config['messages'];
    $message = $pc_messages[$msg_id]['en'];
    if ($msg_id = 'sign_in_required') {
        $message = mps_get_option('pc_message_sign_in', $message);
    }
    $message = str_replace("__CURRENT_URL__", urlencode('https://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]), $message);
    if ($auth->auth_method == 'crowd') {
        $crowd_login_url = esc_url($auth->crowd_config['login_url']);
        $message = str_replace("__CROWD_LOGIN_URL__", $crowd_login_url, $message);
    }
    return $message;
}

function mps_get_pc_user($uuid)
{

    global $wpdb;

    $user = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_users`
            WHERE `uuid` = '%s'
            ",
            $uuid
        )
    );

    return $user;
}

function mps_get_presentation($presentation_id)
{

    global $wpdb;

    $presentation_id = (int) $presentation_id;

    $presentation = $wpdb->get_row(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_presentations`
        WHERE `id` = '" . $presentation_id . "'
        "
    );

    return $presentation;
}

function mps_get_all_presentations()
{

    global $wpdb;

    $presentation = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_presentations`
        "
    );

    return $presentation;
}

function mps_get_presentations_for_uuid($uuid)
{
    /**
     * Return an array of presentations for $uuid, empty array if none.
     */

    global $wpdb;

    $presentations = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_presentations`
        WHERE `author_uuid` = '" . $uuid . "' ORDER BY last_edited DESC;
        "
    );

    // Do some postprocessing on the presentations to make them more manageable
    $presentations = array_map('mps_presentation_metadata', $presentations);

    return $presentations;
}


function mps_presentation_metadata($presentation)
{
    $presentation->files = [];
    $filenames = json_decode($presentation->filename);
    $uploaddir = wp_upload_dir();

    foreach ($filenames as $file) {
        $icon = get_file_icon_class($file);
        $url =  $uploaddir['baseurl'] . '/presentations/' . $file;
        $path =  $uploaddir['basedir'] . '/presentations/' . $file;
        $presentation->files[] = ['name' => $file, 'icon' => $icon, 'url' => $url, 'path' => $path];
    }

    return $presentation;
}

function mps_get_presentations_for_slot($slot_id)
{

    global $wpdb;

    $presentations = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_presentations`
        WHERE `slot_id` = '" . $slot_id . "'
        "
    );

    return $presentations;
}

function pc_config()
{

    return array(
        'user_roles' => array(
            '0' => array(
                'name' => 'Anonymous',
                ),
            '1' => array(
                'name' => 'Admin',
                'view_all_submissions' => true,
                'add_submission'       => true,
                'edit_submission'      => true,
                'delete_submission'    => true,
                'chair_actions'        => true,
                'rate_submission'      => true,
                'view_ratings'         => true,
                'view_users'           => true,
                'edit_users'           => true,
                'signed_in'            => true,
                'submit_status'        => true,
                'view_as_csv'          => true,
                'sort_submission'      => true,
                'email_out_from_pc'    => true,
                'assign_slots'         => true,
                ),
            '2' => array(
                'name' => 'PC Chair',
                'view_all_submissions' => true,
                'add_submission'       => true,
                'edit_submission'      => true,
                'delete_submission'    => true,
                'chair_actions'        => true,
                'rate_submission'      => true,
                'view_ratings'         => true,
                'view_users'           => true,
                'signed_in'            => true,
                'submit_status'        => true,
                'view_as_csv'          => true,
                'sort_submission'      => true,
                'email_out_from_pc'    => true,
                'assign_slots'         => true,
                ),
            '3' => array(
                'name' => 'PC Member',
                'view_all_submissions' => true,
                'chair_actions'        => true,
                'add_submission'       => true,
                'edit_submission'      => true,
                'rate_submission'      => true,
                'view_ratings'         => true,
                'view_users'           => true,
                'signed_in'            => true,
                'view_as_csv'          => true,
                'sort_submission'      => true,
                'email_out_from_pc'    => true,
                'assign_slots'         => true,
                ),
            '4' => array(
                'name' => 'Speaker',
                'add_submission'       => true,
                'signed_in'            => true,
                ),
            '5' => array(
                'name' => 'WG Chair',
                'add_submission'       => true,
                'signed_in'            => true,
                'view_all_submissions' => true,
                'sort_submission'      => true,
                'view_ratings'         => true,
                ),
            ),
    'messages' => array(
    'sign_in_required' => array(
        'en' => 'Welcome to the RIPE Programme Committee Submission System<br /> Please sign into your <a href="__CROWD_LOGIN_URL__/?originalUrl=__CURRENT_URL__">RIPE NCC Access</a> account in order to use the Submission System. If you do not have a RIPE NCC Access account, you can easily <a href="https://access.ripe.net/registration">create one<a/>.'
        ),
        'no_results_found' => array(
        'en' => 'There are currently no items to display.'
    ),
        'no_rating' => array(
        'en' => 'There is currently no rating on this item.'
    ),
        'no_file_submitted' => array(
        'en' => 'There is currently no file submitted.'
    ),
        'delete_message' => array(
        'en' => 'Are you sure that you would like to delete this submission?'
    )
    ),
    'submissions' => array(
        'allowed_files' => array('png', 'jpg', 'ppt', 'key', 'pdf', 'doc', 'xls', 'docx', 'xlsx', 'pptx', 'odt', 'odp', 'txt', 'zip'),
        'max_upload' => 50 //(MB)
    ),
    'submission_types' => array(
        '1' => 'Plenary',
        '2' => 'BoF',
        '3' => 'Tutorial',
        '4' => 'Workshop',
        '10' => 'Lightning Talk',
    ),
    'submission_duration' => array(
    '1' => array('desc' => 'Plenary: 25 minutes',
        'time' => 25),
    '2' => array('desc' => 'BoF: Please specify time in Author Comments',
        'time' => 1),
    '3' => array('desc' => 'Tutorial: Please specify time in Author Comments',
        'time' => 1),
    '4' => array('desc' => 'Workshop: Please specify time in Author Comments',
        'time' => 1),
    '10' => array('desc' => 'Lightning Talk: 10 minutes',
        'time' => 10),
    ),
    'submission_status' => array(
        '0' => 'pending',
        '1' => 'submitted',
        '2' => 'conditional',
        '3' => 'accepted',
        '4' => 'declined',
        '5' => 'cancelled',
        '6' => 'withdrawn',
        '7' => 'reconsider',
    ),
    'mail_templates' => array(
    'acceptance' => array(
        'subject' => 'Presentation accepted for {meeting_name}',
        'body' => "Dear {author_name},

Your proposal for a presentation at {meeting_name} titled '{submission_title}' has been accepted.

A RIPE Programme Committee member may contact you if further input is needed.

We have not yet scheduled the day and time of your session so please watch for the schedule update. If you have particular scheduling constraints, please let us know. We will make every attempt to accommodate where possible.

Please start making your travel plans and register for the meeting.

Kindly inform us in the case you cannot present at the event in person.

The RIPE NCC will be in touch regarding submitting a final version of your presentation to the presentation system prior to your time slot.
When preparing your final slides, please make sure you have all rights (including copyright) to reproduce the material (e.g. images) contained in the slides.

Thank you very much. We look forward to your participation at {meeting_name}.

{user_name}
on behalf of the RIPE Programme Committee"
        ),
    'rejection' => array(
        'subject' => 'Presentation rejected for {meeting_name}',
        'body' => "Dear {author_name},

We regret to inform you that your talk submission for {meeting_name} titled '{submission_title}' has not been accepted into the Plenary session.

We had a tough competition for the few remaining slots and in the end another proposal won the PC's preference.

Looking forward to your submissions for future meetings.

Thank you,
{user_name}
on behalf of the RIPE Programme Committee"
        )
    )
    );
}

function mps_get_own_submissions()
{

    global $wpdb;

    $auth = Meeting_Support_Auth::getInstance();

    if (empty($auth->user)) {
        return [];
    }

    $submissions = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->base_prefix . "ms_pc_submissions`
        WHERE `author_uuid` = '" . $auth->user['uuid'] . "'
        "
    );

    return $submissions;
}

function ms_get_submission_type_name($submission_type_id)
{

    $pc_config = pc_config();

    if (empty($submission_type_id)) {
        return '';
    }

    $name = $pc_config['submission_types'][$submission_type_id];

    return $name;
}

function ms_get_submission_type_slug($submission_type)
{
    switch ($submission_type) {
        case '1':
            return 'plenary';
            break;
        case '2':
            return 'bofs';
            break;
        case '3':
            return 'tutorials';
            break;
        case '4':
            return 'workshops';
            break;
        case '10':
            return 'lightning-talks';
            break;

        default:
            return sanitize_title($submission_type);
            break;
    }
}

function mps_get_submission_labels($subid)
{
    $auth = Meeting_Support_Auth::getInstance();

    $labels = [];

    // No labels possible if the user isn't logged in
    if (! $auth->user) {
        return $labels;
    }

    // Check to see if a user needs to update their rating of a submission
    $submission = mps_get_submission($subid);
    $my_rating = ms_get_rating($auth->user['uuid'], $subid);
    if (! $my_rating) {
        // Submission hasn't been rated at all by this user, add to the "to-rate" list.
        $labels[] = array('text' => 'Requires Rating', 'level' => 'success');
    } else {
        // Submission has been rating, let's check to see if the rating needs to be updated
        if ($submission->updated_date > $my_rating->timestamp) {
            $labels[] = array('text' => 'Requires Updated Rating', 'level' => 'danger');
        }
    }
    return $labels;
}

function ms_get_rating_info($subid)
{
    //build a nice array for the sparkLines stuff
    $result = [];
    $ratings = ms_get_ratings($subid);
    $content = [];
    $presenter = [];
    foreach ($ratings as $rating) {
        if ($rating->rating_content > 0) {
            $content[] = $rating->rating_content;
        }
        if ($rating->rating_presenter > 0) {
            $presenter[] = $rating->rating_presenter;
        }
    }
    $result['content']['values'] = implode(', ', $content);
    $result['presenter']['values'] = implode(', ', $presenter);
    $result['content']['count'] = count($content);
    $result['presenter']['count'] = count($presenter);
    if (count($content) > 0) {
        $result['content']['avg'] = round(array_sum($content) / count($content), 2);
    } else {
        $result['content']['avg'] = 0;
    }
    if (count($presenter) > 0) {
        $result['presenter']['avg'] = round(array_sum($presenter) / count($presenter), 2);
    } else {
        $result['presenter']['avg'] = 0;
    }
    return $result;
}

function ms_get_ratings($subid)
{
    global $wpdb;
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM " . $wpdb->base_prefix . "ms_pc_submission_ratings WHERE submission_id='%d' ORDER BY timestamp ASC",
            $subid
        )
    );
}

function ms_get_rating($uuid = null, $subid)
{

    global $wpdb;

    if (is_null($uuid)) {
        $auth = Meeting_Support_Auth::getInstance();
        $uuid = $auth->user['uuid'];
    }

    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "ms_pc_submission_ratings WHERE uuid='%s' AND submission_id='%d'", $uuid, $subid));

    return $result;
}

function mps_get_slot_rating_for_uuid($slot_id, $uuid)
{

    global $wpdb;

    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ms_presentation_ratings WHERE uuid='%s' AND slot_id='%d'", $uuid, $slot_id));

    return $result;
}

function mps_get_slot_ratings($slot_id)
{

    global $wpdb;

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ms_presentation_ratings WHERE slot_id='%d'", $slot_id));

    return $results;
}

function ms_delete_rating($ratingid)
{

    global $wpdb;
    $auth = Meeting_Support_Auth::getInstance();
    $uuid = $auth->user['uuid'];
    $wpdb->delete($wpdb->base_prefix . 'ms_pc_submission_ratings', array('id' => $ratingid, 'uuid' => $uuid));
    mps_log('Deleting rating for submission #' . $ratingid);
    return true;
}


function mps_delete_presentation($presentation)
{

    global $wpdb;

    $presentation_id = $presentation->id;

    mps_log('Deleting presentation #' . $presentation_id);

    $wpdb->delete($wpdb->prefix . 'ms_presentations', array( 'id' => $presentation_id ));

    $presentation_dir = mps_get_option('presentations_dir');

    if (! $presentation_dir) {
        mps_log('Cannot handle file upload, presentation directory not defined');
        wp_die();
    }

    $files = json_decode($presentation->filename);

    foreach ($files as $key => $file_name) {
        $file_path = get_home_path() . $presentation_dir . $file_name;
        if (file_exists($file_path)) {
            unlink(get_home_path() . $presentation_dir . $file_name);
        }
    }
}


function ms_cmp_submissions($a, $b)
{
    if ($a->sort == $b->sort) {
        return 0;
    }
    return ($a->sort < $b->sort) ? -1 : 1;
}

function ms_cmp_submissions_r($a, $b)
{
    if ($a->sort == $b->sort) {
        return 0;
    }
    return ($a->sort > $b->sort) ? -1 : 1;
}

function ms_get_average_content_rating($subid)
{
    $ratings = ms_get_ratings($subid);
    $count = count($ratings);
    $score = 0;
    $i = 0;
    if ($count == 0) {
        return 0;
    }
    foreach ($ratings as $rating) {
        if ($rating->rating_content > 0) {
            $i++;
            $score = $score + $rating->rating_content;
        }
    }
    if ($i > 0) {
        $average = $score / $i;
    } else {
        $average = 0;
    }
    return $average;
}

function ms_get_average_presenter_rating($subid)
{
    $ratings = ms_get_ratings($subid);
    $count = count($ratings);
    $score = 0;
    $i = 0;
    if ($count == 0) {
        return 0;
    }
    foreach ($ratings as $rating) {
        if ($rating->rating_presenter > 0) {
            $i++;
            $score = $score + $rating->rating_presenter;
        }
    }
    $average = $score/$i;
    return $average;
}

function ms_user_has_rated($subid)
{
    $auth = Meeting_Support_Auth::getInstance();
    $uuid = $auth->user['uuid'];
    global $wpdb;
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "ms_pc_submission_ratings WHERE uuid='%s' AND submission_id='%d'", $uuid, $subid));
    if (count($result) > 0) {
        return true;
    }
    return false;
}


function ms_get_mail_template($name)
{
    //returns an array with subject & body keys for that template name
    $pc_config = pc_config();
    $template = $pc_config['mail_templates'][$name];
    $auth = Meeting_Support_Auth::getInstance();
    if (is_array($template)) {
        switch ($name) {
            case 'acceptance':
                $template['body'] = mps_get_option('pc_accepted_template');
                break;
            case 'rejection':
                $template['body'] = mps_get_option('pc_declined_template');
                break;
        }
        $meeting_name = mps_get_option('meeting_name');
        $user_name = $auth->user['name'];

        $search = array('{meeting_name}', '{user_name}');
        $replace = array($meeting_name, $user_name);

        $template['subject'] = str_replace($search, $replace, $template['subject']);
        $template['body'] = str_replace($search, $replace, $template['body']);
    }
    return $template;
}

function legacy_htmlentities($string, $flags = ENT_COMPAT)
{
    // Function to wrap htmlentities so it always uses UTF8
    return htmlentities($string, $flags, "UTF-8");
}

function generate_random_string($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function get_real_room_name($room_key)
{
    $rooms = mps_get_option('rooms');
    if ($room_key == '_' || $room_key == '') {
        return '';
    }
    if (! isset($rooms[$room_key])) {
        return '';
    }
    return $rooms[$room_key]['long'];
}

function make_safe($input)
{
  // Remove spaces at start/end, double dots and quotes and slashes and $
    @$output = preg_replace('#^ +| +$|(\.\.)|\'|"|/|\\\|\$#', '', $input);
  // Replace spaces or series of spaces with _
    @$output = preg_replace('/ +/', '_', $output);
    return $output;
}

function ms_get_session_slots($session_id)
{

    global $wpdb;

    $session_id = (int) $session_id;

    $slots = $wpdb->get_results(
        "
        SELECT * FROM `" . $wpdb->prefix . "ms_slots` WHERE session_id='" . $session_id . "' ORDER BY ordering ASC
        "
    );

    return $slots;
}

function mps_recording2filename($res, $extension = 'flv')
{
    // Convert a recording row object from the database into a filename
    $filename =
        make_safe($res->presenter_name) . '-'.
        make_safe($res->presentation_title) . '-'.
        str_replace(array('-', ' ', ':'), array('', '-', ''), $res->created) . '.' . $extension;
    return $filename;
}

function ms_get_slot_children($slot_id)
{

    global $wpdb;

    return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ms_slots WHERE parent_id = %d ORDER by ordering ASC", $slot_id));
}

function ms_get_slot($slot_id)
{

    global $wpdb;

    $slot_id = (int) $slot_id;

    $slot = $wpdb->get_row(
        "
        SELECT * FROM " . $wpdb->prefix . "ms_slots WHERE id='" . $slot_id . "'
        "
    );

    return $slot;
}

function human_filesize($bytes, $decimals = 2)
{
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

function ms_get_my_presentation_rating_button($slot_id, $auth)
{
    /**
     * Function to return the html (button) for a user to rate a slot, should return 'Rate',
     * if they havent logged in or rated, with an error message for them to log in or create an account if not logged in
     */
    $html = '';
    $html .= '<div class="boot">';
    if ($auth->user) {
        $my_rating = mps_get_slot_rating_for_uuid($slot_id, $auth->user['uuid']);
        if ($my_rating) {
            if ($my_rating->rating_content == 0) {
                $my_rating->rating_content = '-';
            }
            if ($my_rating->rating_presenter == 0) {
                $my_rating->rating_presenter = '-';
            }
            // Has a rating, let's show it
            $html .= '<a data-slot-id="' . $slot_id . '" class="slot_rate nounderline btn btn-xs btn-default">' . $my_rating->rating_content . ' | ' . $my_rating->rating_presenter . '</a>';
        } else {
            // Doesn't have a rating for this slot, let's show the normal 'Rate' button
            $html .= '<a data-slot-id="' . $slot_id . '" class="slot_rate nounderline btn btn-xs btn-default"><i class="fa fa-thumbs-o-up"></i> ' . __('Rate', 'meeting-support') . '</a>';
        }
    } else {
        // User is not logged in
        if ($auth->auth_method == 'crowd') {
            $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            // Crowd
            $html .= '<a href="' . $auth->crowd_config['login_url'] . '?originalUrl=' . urlencode($pageURL) . '" class="nounderline btn btn-xs btn-default"><i class="fa fa-thumbs-o-up"></i> ' . __('Rate', 'meeting-support') . '</a>';
        } else {
            // Not Crowd
            $html .= '<a href="' . home_url('/login/') . '" class="nounderline btn btn-xs btn-default"><i class="fa fa-thumbs-o-up"></i> ' . __('Rate', 'meeting-support') . '</a>';
        }
    }
    $html .= '</div>';

    return $html;
}

function ms_get_slot_files($slot_id)
{
    /**
     * return an array of all files attached to a specific slot
     * return an empty array if none
     */
    $files = [];
    $presentations = mps_get_presentations_for_slot($slot_id);
    foreach ($presentations as $presentation) {
        $pres_files = json_decode($presentation->filename);
        foreach ($pres_files as $file) {
            $files[] = $file;
        }
    }
    return $files;
}

function ms_slot_rating_modal()
{
    /**
     * function to return the HTML to show the slot rating modal
     */

    $html = '';
    $html .= '<div class="boot">';
    $html .= '<!-- Modal -->';
    $html .= '<div class="modal fade" id="rateSlotModal" tabindex="-1" role="dialog" aria-labelledby="rateSlotModalLabel">';
    $html .= '<div class="modal-dialog" role="document">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header">';
    $html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    $html .= '<h4 class="modal-title" id="rateSlotModalLabel">Rate Presentation</h4>';
    $html .= '</div>';
    $html .= '<div class="modal-body">';

    $html .= '<form id="modalratingform" action="' . admin_url('admin-post.php') . '" method="POST" class="form" role="form">';
    $html .= '<input type="hidden" name="action" value="mps_rate_slot">';
    $html .= '<input type="hidden" id="modalslot" name="slot_id" value="">';
    $html .= '<div class="row">';
    $html .= '<div class="form-group col-sm-6">';
    $html .= '<label for="ratingcontent" class="col-xs-5 control-label">' . __('Rating Content', 'meeting-support') . '</label>';
    $html .= '<div class="col-xs-7">';
    $html .= '<select id="modalratingcontent" name="ratingcontent" class="form-control">';
    $html .= '<optgroup label="' . __('High', 'meeting-support') . '"></optgroup>';
    $html .= '<option value="5">5</option>';
    $html .= '<option value="4">4</option>';
    $html .= '<option value="3">3</option>';
    $html .= '<option value="2">2</option>';
    $html .= '<option value="1">1</option>';
    $html .= '<optgroup label="' . __('Low', 'meeting-support') . '"></optgroup>';
    $html .= '<option selected="selected" value="0">' . __('None', 'meeting-support') . '</option>';
    $html .= '</select>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="form-group col-sm-6">';
    $html .= '<label for="ratingpresenter" class="col-xs-5 control-label">' . __('Rating Delivery', 'meeting-support') . '</label>';
    $html .= '<div class="col-xs-7">';
    $html .= '<select id="modalratingpresenter" name="ratingpresenter" class="form-control">';
    $html .= '<optgroup label="' . __('High', 'meeting-support') . '"></optgroup>';
    $html .= '<option value="5">5</option>';
    $html .= '<option value="4">4</option>';
    $html .= '<option value="3">3</option>';
    $html .= '<option value="2">2</option>';
    $html .= '<option value="1">1</option>';
    $html .= '<optgroup label="' . __('Low', 'meeting-support') . '"></optgroup>';
    $html .= '<option selected="selected" value="0">' . __('None', 'meeting-support') . '</option>';
    $html .= '</select>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="clear"></div>';
    $html .= '<br />';
    $html .= '<div class="row">';
    $html .= '<div class="col-xs-12">';
    $html .= '<div class="form-group">';
    $html .= '<label for="ratingcomments" class="col-sm-2 col-xs-4 control-label">' . __('Comments', 'meeting-support') . '</label>';
    $html .= '<div class="col-sm-10 col-xs-8">';
    $html .= '<textarea style="height:auto;" rows="4" name="ratingcomments" class="form-control" id="modalratingcomments"></textarea>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="clear"></div>';
    $html .= '<div style="margin-top:20px;" class="col-sm-offset-9">';
    $html .= '<button id="modaldeletepresrating" class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i> ' . __('Delete', 'meeting-support') . '</button>';
    $html .= '<button type="submit" style="margin-left:5px;" class="btn btn-xs btn-success"><i class="fa fa-thumbs-o-up"></i> ' . __('Rate', 'meeting-support') . '</button>';
    $html .= '</div>';
    $html .= '</form>';



    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '</div><!-- /.boot -->';

    return $html;
}

function assign_presentations($auth)
{
    global $wpdb;
    $wpdb->update($wpdb->prefix . 'ms_presentations', array('author_uuid' => $auth->user['uuid']), array('author_uuid' => '', 'author_email' => $auth->user['email']));
}

function ms_upload_form($auth, $presentation, $is_ajax = false, $form_id_prefix = null)
{
    $allowed_files = mps_get_option('allowed_file_types', array());

    $return = '';
    $return .= '<div class="presentation-loading"><span class="loading-text">Loading ...</span></div>';
    $return .= '<form id="' . $form_id_prefix . 'presentation-upload-form" enctype="multipart/form-data" action="' . admin_url('admin-post.php') . '" method="POST" class="presentation-upload-form form-horizontal" role="form">';

    $return .= '<div class="container col-xs-12">';
    $return .= '<input type="hidden" name="action" value="mps_presentation_upload"/>';
    if ($presentation) {
        $return .= '<input type="hidden" name="presentation_id" value="' . $presentation->id . '"/>';
    }
    $return .=  wp_nonce_field('mps_presentation_upload', '_wpnonce', true, false);

    // upload file area
    // Only show the file selection if we are editing an existing presentation

    $return .= '<div class="form-group">';
    $return .= '<div class="row">';
    $return .= '<div class="col-xs-12 col-sm-12">';

    if ($is_ajax) {
        $return .= '<div class="show_js presentation-upload-zone" id="' . $form_id_prefix . 'presentation-upload-zone">';
        $return .= '<label for="file">+ Add file(s) or drop file(s) here</label>';
        $return .= '<div>';
        $return .= '<small>' . __('Maximum file size:', 'meeting-support') . ' ' . human_filesize(getMaximumFileUploadSize(), 0) .'B.<br />';
        $return .= __('Accepted file types:', 'meeting-support') . ' ' . implode(', ', $allowed_files) .'</small>';
        $return .= '</div>';
        $return .= '</div>';
        $return .= '<input type="file" class="hide" multiple="multiple" name="presentation_upload" id="presentation_upload" />';
    }

    if ($presentation) {
        if (!$is_ajax) {
            $return .= '<label for="presentation_file_select" class="control-label">' . __('Presentation File(s)', 'meeting-support') . '</label>';
            $return .= '<select required name="presentation_file_select" class="form-control" id="presentation_file_select">';

            foreach (json_decode($presentation->filename) as $id => $file) {
                // Get file extension
                $extension = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
                $return .= '<option value="' . $id . '">(' . $extension . ') ' . urldecode($file) . '</option>';
            }

            $return .= '<option value="999">' . __('Add new file...', 'meeting-support') . '</option>';
            $return .= '</select>';

            $return .= '<span style="display:block; margin-bottom:2px;" class="js_hide label label-info">' . __('Please select the file you want to replace. <br />If you want to keep the original file(s), do not attach a file', 'meeting-support') . '</span>';
        } else {
            foreach (json_decode($presentation->filename) as $id => $file) {
                $return .= '<div class="statusbar">';
                $return .= '<div class="filename exist">' . $file . '</div>';
                $return .= '<a href="#" class="ajax-remove">Delete</a>';
                $return .= '<div class="progressBar downloaded"></div>';
                $return .= '</div>';
            }
        }
    } else {
        if (!$is_ajax) {
            $return .= '<label for="presentation_upload" class="control-label">' . __('Presentation File(s)', 'meeting-support') . '</label>';
        }
    }

    if (!$is_ajax) {
        $return .= '<input type="file" name="presentation_upload" id="presentation_upload" />';
        $return .= '<div>';
        $return .= '<small>' . __('Maximum file size:', 'meeting-support') . ' ' . human_filesize(getMaximumFileUploadSize(), 0) .'B.<br />';
        $return .= __('Accepted file types:', 'meeting-support') . ' ' . implode(', ', $allowed_files) .'</small>';
        $return .= '</div>';
    }

    $return .= '</div>';
    $return .= '</div>';
    $return .= '</div>';

    // presenter name and email
    $return .= '<div class="form-group">';
    $return .= '<div class="row">';
    $return .= '<div class="col-xs-12 col-sm-6">';
    $return .= '<label for="author_name" class="control-label">' . __('Presenter Name', 'meeting-support') . '</label>';
    if ($presentation) {
        $return .= '<input value="' . sanitize_text_field($presentation->author_name) . '" required type="text" id="author_name" name="author_name" class="form-control" />';
    } elseif (isset($_SESSION['old_post'])) {
        $return .= '<input value="' . sanitize_text_field($_SESSION['old_post']['author_name']) . '" required type="text" id="author_name" name="author_name" class="form-control" />';
    } elseif ($auth->user) {
        $return .= '<input value="' . sanitize_text_field($auth->user['name']) . '" required type="text" id="author_name" name="author_name" class="form-control" />';
    } else {
        $return .= '<input required type="text" id="author_name" name="author_name" class="form-control" />';
    }
    $return .= '</div>';

    $return .= '<div class="col-xs-12 col-sm-6">';
    $return .= '<label for="author_email" class="control-label">' . __('Email Address', 'meeting-support') . '</label>';
    if ($presentation) {
        $return .= '<input value="' . sanitize_email($presentation->author_email) . '" required type="email" name="author_email" class="form-control" id="author_email" />';
    } elseif (isset($_SESSION['old_post'])) {
        $return .= '<input value="' . sanitize_email($_SESSION['old_post']['author_email']) . '" required type="email" name="author_email" class="form-control" id="author_email" />';
    } elseif ($auth->user) {
        $return .= '<input value="' . sanitize_email($auth->user['email']) . '" required type="email" name="author_email" class="form-control" id="author_email" />';
    } else {
        $return .= '<input required type="email" name="author_email" class="form-control" id="author_email" />';
    }
    $return .= '</div>';
    $return .= '</div>';
    $return .= '</div>';

    $sessions = mps_get_all_sessions();
    $currentday = '';

    $return .= '<div class="form-group">';
    $return .= '<div class="row">';

    $return .='<div class="col-xs-12 col-sm-6">';

    $return .= '<label for="author_affiliation" class="control-label">' . __('Affiliation', 'meeting-support') . '</label>';
    if ($presentation) {
        $return .= '<input value="' . sanitize_text_field($presentation->author_affiliation) . '" type="text" name="author_affiliation" class="form-control" id="author_affiliation" />';
    } elseif (isset($_SESSION['old_post'])) {
        $return .= '<input value="' . sanitize_text_field($_SESSION['old_post']['author_affiliation']) . '" type="text" name="author_affiliation" class="form-control" id="author_affiliation" />';
    } else {
        $return .= '<input type="text" name="author_affiliation" class="form-control" id="author_affiliation" />';
    }
    $return .= '</div>';

        // ignore session field for non-javascript
    if ($is_ajax) {
        $return .= '<div class="col-xs-12 col-sm-6">';

        $return .= '<label for="presentation_session" class="control-label">' . __('Presentation Session', 'meeting-support') . '</label>';
        if ($presentation) {
            $return .= '<select required data-original-value="' . $presentation->session_id . '" name="presentation_session" class="form-control presentation_session">';
        } elseif (isset($_SESSION['old_post'])) {
            $return .= '<select required data-original-value="' . intval($_SESSION['old_post']['presentation_session']) . '" name="presentation_session" class="form-control presentation_session">';
        } else {
            $return .= '<select required name="presentation_session" class="form-control presentation_session">';
        }
        $return .= '<option value="">' . __('Please select', 'meeting-support') . '</option>';

        foreach ($sessions as $session) {
            // Chop out sessions which have not yet been finalised, or are intermissions
            if ($session->is_intermission == 1 || trim($session->name) == '') {
                continue;
            }
            // Set the $currentday var at the beginning
            if ($currentday == '') {
                $return .= '<optgroup label="'.date('l j F', strtotime($session->start_time)).'">';
                $currentday = date('l', strtotime($session->start_time));
            }
            if ($currentday != date('l', strtotime($session->start_time))) {
                $currentday = date('l', strtotime($session->start_time));
                $return .= '</optgroup>';
                $return .= '<optgroup label="'.date('l j F', strtotime($session->start_time)).'">';
            }
            $return .= '<option value="'.$session->id.'">'.stripslashes($session->name).' ('.date('H:i', strtotime($session->start_time)).'-'.date('H:i', strtotime($session->end_time)).')</option>';
        }

        $return .= '</optgroup>';

        // $return .= '<option value="0">' . __('Other', 'meeting-support') . '</option>';

        $return .= '</select>';
        $return .= '</div>';


        $return .= '</div>';
        $return .= '</div>';

        $return .= '<div class="form-group">';
        $return .= '<div class="row">';

        $return .= '<div class="col-xs-12 col-sm-6">';
    } else {
        $return .= '<div class="col-xs-12 col-sm-6">';
    }

    $return .= '<label for="presentation_slot" class="control-label">' . __('Presentation Slot', 'meeting-support') . '</label>';
    if ($presentation) {
        $return .= '<select data-original-value="' . $presentation->slot_id . '" required name="presentation_slot" class="form-control presentation_slot">';
    } elseif (isset($_SESSION['old_post'])) {
        $return .= '<select required data-original-value="' . intval($_SESSION['old_post']['presentation_slot']) . '" required name="presentation_slot" class="form-control presentation_slot">';
    } else {
        if ($is_ajax) {
            $return .= '<select required name="presentation_slot" disabled class="form-control presentation_slot">';
        } else {
            $return .= '<select required name="presentation_slot" class="form-control presentation_slot">';
        }
    }
    $return .= '<option value="">' . __('Please select', 'meeting-support') . '</option>';

    foreach ($sessions as $session) {
        // Chop out sessions which have not yet been finalised, or are intermissions
        if ($session->is_intermission == 1 || trim($session->name) == '') {
            continue;
        }
        // Set the $currentday var at the beginning
        if ($currentday == '') {
            $return .= '<optgroup label="'.date('l j F', strtotime($session->start_time)).'">';
            $currentday = date('l', strtotime($session->start_time));
        }
        if ($currentday != date('l', strtotime($session->start_time))) {
            $currentday = date('l', strtotime($session->start_time));
            $return .= '</optgroup>';
            $return .= '<optgroup label="'.date('l j F', strtotime($session->start_time)).'">';
        }
        // Nasty fix, HTML does not yet properly stack optgroups within optgroups
        $slots = ms_get_session_slots($session->id);
        if (count($slots) > 0) {
            $return .= '<optgroup label="&nbsp;&nbsp;'.stripslashes($session->name).' ('.date('H:i', strtotime($session->start_time)).'-'.date('H:i', strtotime($session->end_time)).')">';
            foreach ($slots as $slot) {
                if ($slot->parent_id != 0) {
                    continue;
                }
                $children = ms_get_slot_children($slot->id);
                if (count($children) > 0) {
                    $return .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;'.$slot->title.'">';
                    foreach ($children as $child) {
                        if ($presentation && $presentation->slot_id == $child->id) {
                            $return .= '<option selected="selected" value="'.$child->id.'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.stripslashes($child->title).'</option>';
                        } elseif (isset($_SESSION['old_post']) && $_SESSION['old_post']['presentation_slot'] == $child->id) {
                            $return .= '<option selected="selected" value="'.$child->id.'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.stripslashes($child->title).'</option>';
                        } else {
                            $return .= '<option value="'.$child->id.'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.stripslashes($child->title).'</option>';
                        }
                    }
                    $return .= '</optgroup>';
                } else {
                    if ($presentation && $presentation->slot_id == $slot->id) {
                        $return .= '<option selected="selected" value="'.$slot->id.'">&nbsp;&nbsp;&nbsp;&nbsp;'.stripslashes($slot->title).'</option>';
                    } elseif (isset($_SESSION['old_post']) && $_SESSION['old_post']['presentation_slot'] == $slot->id) {
                        $return .= '<option selected="selected" value="'.$slot->id.'">&nbsp;&nbsp;&nbsp;&nbsp;'.stripslashes($slot->title).'</option>';
                    } else {
                        $return .= '<option value="'.$slot->id.'">&nbsp;&nbsp;&nbsp;&nbsp;'.stripslashes($slot->title).'</option>';
                    }
                }
            }
            $return .= '</optgroup>';
        }
    }
    $return .= '</optgroup>';
    if ($presentation && $presentation->slot_id == 0) {
        $return .= '<option selected="selected" value="0">' . __('Other', 'meeting-support') . '</option>';
    } elseif (isset($_SESSION['old_post']) && $_SESSION['old_post']['presentation_slot'] == 0) {
        $return .= '<option selected="selected" value="0">' . __('Other', 'meeting-support') . '</option>';
    } else {
        $return .= '<option value="0">' . __('Other', 'meeting-support') . '</option>';
    }

    $return .= '</select>';

    $return .='</div>';

    if ($is_ajax) {
        $return .= '<div class="col-xs-12 col-sm-6">';
    } else {
        $return .='</div>';
        $return .='</div>';
        $return .= '<div class="form-group">';
        $return .= '<div class="row">';

        $return .='<div class="col-xs-12 col-sm-12">';
    }

    $return .= '<label for="presentation_title" class="control-label">' . __('Presentation Title', 'meeting-support') . '</label>';
    if ($presentation) {
        $return .= '<input required value="' . sanitize_text_field($presentation->title) . '" type="text" name="presentation_title" class="form-control presentation_title" />';
    } elseif (isset($_SESSION['old_post']['presentation_title'])) {
        $return .= '<input required value="' . sanitize_text_field($_SESSION['old_post']['presentation_title']) . '" type="text" name="presentation_title" class="form-control presentation_title" />';
    } else {
        $return .= '<input required type="text" name="presentation_title" class="form-control presentation_title" />';
    }
    $return .= '</div>';
    $return .= '</div>';
    $return .= '</div>';


    # terms and conditions output
    $uploading_text = '';
    $uploading_text .= '<small class="tc-text">';
    $uploading_text .= '<span>';
    $uploading_text .= 'By uploading a presentation, you:';
    $uploading_text .= '</span>';
    $uploading_text .= '<ul>';
    $uploading_text .= '<li>Confirm you have all rights (including copyright) to reproduce the material (e.g. images) contained in the slides</li>';
    $uploading_text .= '<li>Agree that the presentation slides, recording and transcript from your presentation and relevant discussion shall be published on the RIPE Meeting website and will be freely available to anyone</li>';
    $uploading_text .= '<li>Indemnify the RIPE NCC for all possible or future claims of third parties regarding the content of your presentation slides</li>';
    $uploading_text .= '</ul>';
    $uploading_text .= '</small>';

    // captcha
    if (! $auth->user) {
        // Captcha if not logged in
        if (!$is_ajax) {
            $captcha = new Gregwar\Captcha\CaptchaBuilder;
            $captcha->setDistortion(true);
            $captcha->setIgnoreAllEffects(true);
            $captcha->build();

            $_SESSION['phrase'] = $captcha->getPhrase();

            $return .= '<div class="clear"></div>';
            $return .= '<div class="form-group">';
            $return .= '<div class="row">';
            $return .= '<div class="col-xs-12 col-sm-6">';
            $return .= '<label for="submissionurl" class="control-label">' . __('Verification Text', 'meeting-support') . '</label>';
            $return .= '<div class="row">';
            $return .= '<div class="col-xs-12 col-sm-6">';
            $return .= '<div class="captcha"><img src="' . $captcha->inline() . '" /></div>';
            $return .= '</div>';
            $return .= '<div class="col-xs-12 col-sm-6" style="margin: 10px 0;">';
            $return .= '<input required class="form-control" placeholder="' . __('Verification Text', 'meeting-support') . '" name="captcha" type="text">';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '</div>';
            $return .= '<div class="col-xs-12">';
            $return .= $uploading_text;
            $return .= '</div>';
        } else {
            $return .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
            $return .= '<script>var captcha_callback</script>';

            $return .= '<div class="row">';

            $return .= '<div class="col-xs-12 col-sm-6">';
            $return .= $uploading_text;
            $return .= '</div>';

            $return .= '<div class="col-xs-12 col-sm-6 text-center">';
            $return .= '    <div id="i-recaptcha" class="g-recaptcha"';
            $return .= '          data-sitekey="' . mps_get_option('meeting_irecaptcha_key') . '"';
            $return .= '          data-callback="captcha_callback"';
            $return .= '          data-badge="inline"';
            $return .= '          data-size="invisible">';
            $return .= '    </div>';
            $return .= '</div>';

            $return .= '</div>';
        }
    }

    $return .= '</div>';

    $return .= '<div class="row">';
    $return .= '<div class="col-sm-12">';
    $return .= '<div id="presentation-errors-area" class="alert alert-danger" role="alert"></div>';
    $return .= '</div>';
    $return .= '</div>';

    if ($auth->user) {
            $return .= '<div class="col-sm-12">';
            $return .= $uploading_text;
            $return .= '</div>';
    }

    $return .= $tc_block;

    $return .= '<div class="container col-xs-12">';
    $return .= '<div class="form-group bottom-buttons">';
    $return .= '<input type="submit" value="' . __('Submit', 'meeting-support') . '" class="pull-right btn btn-primary" id="' . $form_id_prefix . 'submit-upload" />';
    if ($is_ajax) {
        $return .= '<button type="button" class="btn btn-default pull-left close-upload-form" >Cancel</button>';
    }

    $return .= '</div>';
    $return .= '</div>';

    $return .= '</form>';

    return $return;
}

function ms_upload_form_modal($auth, $presentation)
{
    /**
     * function to return the HTML to show the upload form in modal
     */

    $html = '';
    $html .= '<div class="boot">';
    $html .= '<div class="modal fade" id="uploadPresentationModal" tabindex="-1" role="dialog" aria-labelledby="uploadPresentationModalLabel">';
    $html .= '<div class="modal-dialog modal-lg" role="document">';
    $html .= '<div class="modal-content">';

    $html .= '<div class="modal-body">';
    $html .= '<div class="form-wrapper">';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div><!-- /.boot -->';

    return $html;
}

function mps_get_all_videos($auth_method)
{
    /**
     * Fetch all videos from the right place, depending on the authentication method used
     */
    switch ($auth_method) {
        case 'crowd':
            // Using crowd as authentication, grab videos from the seperate database.table
            global $wpdb;
            $database_name = $wpdb->prefix . 'recorder';
            $table_name = 'recorder_recording';
            $rcdb = mps_create_rcdb($database_name);
            $videos = $rcdb->get_results('SELECT * FROM recorder_recording ORDER BY id ASC');
            return $videos;
            break;
        case 'local':
            global $wpdb;
            $locale = mps_get_current_locale();

            $videos = $wpdb->get_results(
                "
                SELECT * FROM `" . $wpdb->prefix . "ms_videos`
                WHERE `locale` = '" . $locale . "'
                "
            );
            return $videos;
            break;
        default:
            mps_log('Bad $auth_method passed');
            return [];
            break;
    }
}

function mps_delete_video($auth_method, $video_id)
{
    $video_id = (int) $video_id;
    switch ($auth_method) {
        case 'crowd':
            // Using crowd as authentication, grab video from the seperate database.table
            global $wpdb;
            $database_name = $wpdb->prefix . 'recorder';
            $table_name = 'recorder_recording';
            $rcdb = mps_create_rcdb($database_name);
            $rcdb->delete($table_name, array('id' => $video_id));
            return;
            break;
        case 'local':
            # code...
            # // TODO
            break;
        default:
            mps_log('Bad $auth_method passed');
            return [];
    }
}

function mps_update_video($auth_method, $video_id, $video)
{
    $video_id = (int) $video_id;
    switch ($auth_method) {
        case 'crowd':
            // Using crowd as authentication, grab video from the seperate database.table
            global $wpdb;
            $database_name = $wpdb->prefix . 'recorder';
            $table_name = 'recorder_recording';
            $rcdb = mps_create_rcdb($database_name);
            if ($video_id > 0) {
                // Updating existing video
                $rcdb->update($table_name, $video, array('id' => $video_id));
            } else {
                // Adding a new video
                $rcdb->insert($table_name, $video);
            }
            return;
            break;
        case 'local':
            global $wpdb;
            $videos = mps_get_option('videos', array());
            if ($video_id > 0) {
                // Updating existing video
                $wpdb->update($wpdb->prefix . 'ms_videos', $video, array('id' => $video_id));
            } else {
                // Adding a new video
                $wpdb->insert($wpdb->prefix . 'ms_videos', $video);
            }
            break;
        default:
            mps_log('Bad $auth_method passed');
            return [];
    }
}


function mps_get_video($auth_method, $video_id)
{
    $video_id = (int) $video_id;
    global $wpdb;

    switch ($auth_method) {
        case 'crowd':
            // Using crowd as authentication, grab video from the seperate database.table
            $incomplete_videos = mps_get_option('incomplete_videos', array());
            $database_name = $wpdb->prefix . 'recorder';
            $table_name = 'recorder_recording';
            $rcdb = mps_create_rcdb($database_name);
            $video = $rcdb->get_row('SELECT * FROM recorder_recording WHERE id=' . $video_id);

            // Give the filename of the video too, will save us from doing future lookups.
            if (is_object($video)) {
                $video->filename = mps_recording2filename($video);
            } else {
                $video = new stdClass;
                $video->filename = mps_recording2filename($video);
            }

            // Incomplete flag
            if (in_array($video->id, $incomplete_videos)) {
                $video->is_incomplete = true;
            } else {
                $video->is_incomplete = false;
            }
            return $video;
        break;
        case 'local':
            $video = $wpdb->get_row(
                "
                SELECT * FROM `" . $wpdb->prefix . "ms_videos`
                WHERE `id` = '" . $video_id . "'
                "
            );
            $video->presentation_id = $video->slot_id;
            return $video;
            break;
        default:
            mps_log('Bad $auth_method passed');
            return [];
    }
}

function mps_steno_exists($session_id)
{
    $uploaddir = wp_upload_dir();
    $path = $uploaddir['basedir'];
    $stenodir = realpath($path.'/../../archive/steno/');
    if (file_exists($stenodir . '/' . $session_id . '.txt')) {
        return true;
    }
    return false;
}

function mps_chat_exists($session_id)
{
    $uploaddir = wp_upload_dir();
    $path = $uploaddir['basedir'];
    $chatdir = realpath($path.'/../../archive/chat/');
    if (file_exists($chatdir . '/' . $session_id . '.log')) {
        return true;
    }
    return false;
}


function show_archive_day_list($sessions)
{
    $output = '';
    foreach ($sessions as $session) {
        $output .= '<h4>'.stripslashes($session->name).'</h4>';
        // Links to the agenda (always there)
        $output .= '<a href="'.$session->url.'"><i class="fa fa-file-pdf-o"></i> Agenda and Presentations</a>';



        $uploaddir = wp_upload_dir();
        $path = $uploaddir['basedir'];
        $chatdir = realpath($path.'/../../archive/chat/');
        $stenodir = realpath($path.'/../../archive/steno/');
        // Links to the Chat Logs (if available)
        if (file_exists($chatdir.'/'.$session->id.'.log')) {
            $output .= ' | <a href="' . home_url() . '/archives/chat/'.$session->id.'"><i class="fa fa-file-text-o"></i> Chat Logs</a>';
        }

        // Links to the Steno (if available)
        if (file_exists($stenodir.'/'.$session->id.'.txt')) {
            $output .= ' | <a href="' . home_url() . '/archives/steno/'.$session->id.'"><i class="fa fa-file-text-o"></i> Stenography Transcripts</a>';
        }

        $output .= '<div class="clear"></div>';

        // Links to videos in this session (if available)
        $videos = ms_get_session_videos($session->id);
        if (! empty($videos)) {
            $output .= '<br />';
            $output .= '<b><i style="color:green;" class="fa fa-video-camera"></i> Webcast Recordings</b>';
            $output .= '<ul>';
            foreach ($videos as $video) {
                $output .= '<li><a href="' . home_url() . '/archives/video/'.$video->id.'">'.htmlspecialchars(stripslashes($video->presenter_name)).' - '.stripslashes(htmlspecialchars($video->presentation_title)).'</a></li>';
            }
            $output .= '</ul>';
        }

        $output .= '<hr />';
    }
    return $output;
}

function ms_get_session_videos($sessionid)
{
    if ($sessionid == 999) {
        return;
    }
    $rcdb = mps_create_rcdb('');
    $videos = $rcdb->get_results($rcdb->prepare("SELECT * FROM recorder_recording WHERE session_id='%d' AND status='FINISHED' ORDER BY created ASC", $sessionid));
    return $videos;
}

function ms_get_slot_videos($slotid, $auth_method = 'crowd')
{
    if ($slotid == 999) {
        return;
    }

    if ($auth_method == 'crowd') {
        $rcdb = mps_create_rcdb('');
        $videos = $rcdb->get_results($rcdb->prepare("SELECT * FROM recorder_recording WHERE presentation_id='%d' AND status='FINISHED' ORDER BY created ASC", $slotid));
        return $videos;
    } else {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ms_videos WHERE slot_id = %d AND locale = %s", $slotid, mps_get_current_locale()));
    }
}

function mps_create_rcdb($database = null)
{

    if (!$database) {
        global $wpdb;
        $database = $wpdb->prefix . 'recorder';
    }
    $username = 'recorder';
    $password = mps_get_option('recorder_password');
    $rcdb = new wpdb($username, $password, $database, 'localhost');
    return $rcdb;
}

function get_videofeed_view($slotid)
{
    $output = '';

    $rcdb = mps_create_rcdb();

    $videos = $rcdb->get_results($rcdb->prepare("SELECT * FROM recorder_recording WHERE status='FINISHED' AND presentation_id='%d'", $slotid));
    if (! empty($videos)) {
        $output .= '<center>';
        foreach ($videos as $video) {
            $output .= '<a class="nounderline" style="color:green;" href="' . home_url() . '/archives/video/'.$video->id.'"><i class="download-icon fa fa-video-camera"></i></a>';
        }
        $output .= '</center>';
    }
    return $output;
}

function get_filelist_view($slotid)
{
    $presentations = ms_get_slot_presentations($slotid);
    $output = '';
    if (! empty($presentations)) {
        foreach ($presentations as $presentation) {
            $files = json_decode($presentation->filename);
            $output .= '<center>';
            foreach ($files as $file) {
                $output .= '<a href="' . home_url() . '/presentations/' . urlencode($file) . '">';
                $output .= '<i class="download-icon fa '.get_file_icon_class($file).'"></i>';
                $output .= '</a>';
                $output .= '&nbsp;';
            }
            $output .= '</center>';
        }
    }
    return $output;
}


function get_speaker_bio_view($slotid)
{
    // Don't show the profile if it'll generate a 404
    $page = get_page_by_path('speakers');
    if (! $page) {
        return;
    }
    if (strpos($page->post_content, '[speakers]') === false) {
        return;
    }

    $presentations = ms_get_slot_presentations($slotid);
    $output = '<center>';
    if (! empty($presentations)) {
        foreach ($presentations as $presentation) {
            //dd($presentation);
            if ($presentation->author_uuid != '') {
                $speaker = mps_get_speaker_by_uuid($presentation->author_uuid);
                if ($speaker) {
                    if ($speaker->allowed) {
                        $output .= '<a data-toggle="tooltip" title="Bio for ' . sanitize_text_field($speaker->name) . '" target="_blank" href="' . home_url() . '/speakers/' . urlencode($speaker->slug) . '">';
                        $output .= '<i style="font-size: 1.5em;" class="fa fa-user"></i>';
                        $output .= '</a>';
                        $output .= '&nbsp;';
                    }
                }
            }
        }
    }
    $output .= '</center>';
    return $output;
}

function mps_speaker_bio_upload_form($user)
{
    $speaker = mps_get_speaker_by_uuid($user['uuid']);
    $lang = mps_get_short_current_locale();
    $return = '';
    $return .= '<h2>' . sanitize_text_field($user['name']) . ' - Speaker Bio</h2>';
    $bio = '';
    $return .= '<p>This is not a mandatory field. If you upload a presenter bio, you can edit or delete it at any time during the meeting. Please read our <a href="https://www.ripe.net/about-us/legal/ripe-ncc-privacy-statement" target="_blank">privacy statement</a> for more information.</p>';
    $is_draft = false;
    if ($speaker) {
        if (array_key_exists($lang, $speaker->bio_texts_draft)) {
            $bio = $speaker->bio_texts_draft[$lang];
            $is_draft = true;
        } else {
            if (array_key_exists($lang, $speaker->bio_texts)) {
                $bio = $speaker->bio_texts[$lang];
            }
        }

        // Do we have a form repopulation to do
        if (isset($_SESSION['bio'])) {
            $bio = $_SESSION['bio'];
            unset($_SESSION['bio']);
        }

        $return .= '<div class="boot">';
        if (! $speaker->allowed || $is_draft) {
            $return .= '<div class="alert alert-warning">';
            $return .= __('This bio is currently being reviewed and will be published shortly.', 'meeting-support');
            $return .= '</div>';
        } elseif ($bio != '') {
            $return .= '<div class="alert alert-success">';
            $return .= __('This bio has been <a target="_blank" href="' . home_url('speakers/' . $speaker->slug) . '">published</a>, you can make changes below.', 'meeting-support');
            $return .= '</div>';
        }
        $return .= '</div>';

        $return .= mps_flash('speaker-bio');

        $return .= '<div class="boot">';
        $return .= '<form method="POST" action="' . admin_url('admin-post.php') . '">';
        $return .= '<input type="hidden" name="action" value="mps_update_speaker_bio">';
        $return .= wp_nonce_field('mps_update_speaker_bio');
        $return .= '<textarea ' . ($is_draft ? 'readonly="readonly' : '') . ' placeholder="Write your personal bio here. HTML is not allowed" style="width: 100%; padding: 1.5em; border: 1px grey dashed" rows="16" id="speaker-bio" name="speaker_bio">';
        $return .= escape_multiline_text($bio);
        $return .= '</textarea>';
        $return .= '<div class="pull-right">';
        if (! $is_draft) {
            $return .= '<div class="label label-warning"><span id="bio-text-words-to-go"></span> more words needed</div> ';
            $return .= '<div class="label label-success"><span id="bio-text-words-left"></span> maximum words left</div> ';
            $return .= '<input id="update-bio" type="submit" class="btn btn-sm btn-default" value="Update Bio">';
        }
        $return .= "</div>";
        $return .= "</form>";
        $return .= "</div>";
    } else {
        $return .= '<div class="boot">';
        $return .= '<form method="POST" action="' . admin_url('admin-post.php') . '">';
        $return .= '<input type="hidden" name="action" value="mps_update_speaker_bio">';
        $return .= wp_nonce_field('mps_update_speaker_bio');
        $return .= '<textarea placeholder="Write your personal bio here. HTML is not allowed" style="width: 100%; padding: 1.5em; border: 1px grey dashed" rows="16" id="speaker-bio" name="speaker_bio">';
        $return .= '</textarea>';
        $return .= '<div class="pull-right">';
        if (! $is_draft) {
            $return .= '<div class="label label-warning"><span id="bio-text-words-to-go"></span> more words needed</div> ';
            $return .= '<div class="label label-success"><span id="bio-text-words-left"></span> maximum words left</div> ';
            $return .= '<input id="update-bio" type="submit" class="btn btn-sm btn-default" value="Update Bio">';
        }
        $return .= "</div>";
        $return .= "</form>";
        $return .= "</div>";
    }

    return $return;
}

function ends_with($haystack, $needle)
{
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}


function mps_get_current_locale()
{
    $languages = apply_filters('wpml_active_languages', null);
    if (! $languages) {
        return get_locale();
    }

    foreach ($languages as $l) {
        if ($l['active']) {
            $locale = $l['default_locale'];
            break;
        }
    }
    return $locale;
}

function mps_get_short_current_locale()
{
    $full_locale = mps_get_current_locale();
    $boom = explode('_', $full_locale);
    return $boom[0];
}

function mps_get_pcss_area_structure()
{
    $pcss_content = '<h2>Submissions:</h2>
    <ul>
        <li><a title="Plenary" href="plenary">Plenary</a></li>
        <li><a title="BoFs" href="bofs">BoFs</a></li>
        <li><a title="Tutorials" href="tutorials">Tutorials</a></li>
        <li><a title="Lightning Talks" href="lightning-talks">Lightning Talks</a></li>
    </ul>
    <h2><b>Meeting Schedule</b></h2>
    <ul>
        <li><strong>20 July:</strong> Registrations open</li>
        <li><strong>22 July:</strong> Announce Meeting CFP</li>
        <li><strong>28 August:</strong> 1st submission deadline</li>
        <li><strong>02 September:</strong> Draft agenda/accepted presentations</li>
        <li><strong>25 September:</strong> 2nd submission deadline</li>
        <li><strong>30 September:</strong> Final plenary programme published</li>
        <li><strong>24-28 October:</strong> Meeting in Madrid, Spain</li>
    </ul>';
    $plenary_content = '[submission_list type="Plenary"]';
    $bofs_content = '[submission_list type="BoF"]';
    $tutorials_content = '[submission_list type="Tutorial"]';
    $workshops_content = '[submission_list type="Workshop"]';
    $lt_content = '[submission_list type="Lightning Talk"]';
    $tagged_content = '[submission_list type="Tagged"]';
    $structure = array(
        array(
            'name' => 'PCSS',
            'hide' => true,
            'content' => $pcss_content,
            'children' => array(
                array('name' => 'Plenary', 'hide' => false, 'content' => $plenary_content),
                array('name' => 'BoFs', 'hide' => false, 'content' => $bofs_content),
                array('name' => 'Tutorials', 'hide' => false, 'content' => $tutorials_content),
                array('name' => 'Workshops', 'hide' => false, 'content' => $workshops_content),
                array('name' => 'Lightning Talks', 'hide' => false, 'content' => $lt_content),
                array('name' => 'Tagged', 'hide' => true, 'content' => $tagged_content)
            )
        )
    );
    return $structure;
}

function mps_get_pcss_area_by_path($path)
{
    $structure = mps_get_pcss_area_structure();
    foreach ($structure as $first_level) {
        if ($path == sanitize_title($first_level['name'])) {
            unset($first_level['children']);
            return $first_level;
        }
        foreach ($first_level['children'] as $second_level) {
            if ($path == sanitize_title($first_level['name']) . '/' . sanitize_title($second_level['name'])) {
                unset($second_level['children']);
                return $second_level;
            }
        }
    }
    return false;
}

function mps_exclude_page_from_nav($page_id)
{
    $page_id = (int) $page_id;
    $excluded_pages_key = 'ep_exclude_pages';
    $excluded_pages = get_option($excluded_pages_key, '');
    $boom = explode(',', $excluded_pages);

    if (! in_array($page_id, $boom)) {
        // Only add it to the array if it's not in there already
        $boom[] = $page_id;
        $excluded_pages = implode(',', $boom);
        update_option($excluded_pages_key, $excluded_pages);
    }

}

function mps_log($payload)
{
    if (is_array($payload) || is_object($payload)) {
        $payload = json_encode($payload);
    }
    error_log('----[MPS]----');
    error_log('[MPS] ' . $payload);
    $auth = Meeting_Support_Auth::getInstance();
    if ($auth->user) {
        error_log('[MPS] ' . ucfirst($auth->auth_method) . ': ' . $auth->user['name'] . ' - ' . $auth->user['email'] . ' - ' . $auth->user['uuid']);
    } else {
        error_log('[MPS] Not Logged In');
    }
    if (function_exists('wp_get_current_user')) {
        $wp_user = wp_get_current_user();
        if ($wp_user->ID > 0) {
            error_log('[MPS] Wordpress User: ' . $wp_user->user_email);
        }
    }
    error_log('[MPS] IP Address: ' . $_SERVER['REMOTE_ADDR']);
    error_log('-------------');
}

function mps_generate_password($pwlength = 8)
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = []; //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $pwlength; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function mps_get_ripencc_login_link($text = 'Sign in with RIPE NCC Access', $use_http_referer = false)
{

    $auth = Meeting_Support_Auth::getInstance();
    $html = '';
    if (empty($auth->user) && $auth->auth_method == 'crowd') {
        if ($use_http_referer == true) {
            $pageURL = $_SERVER['HTTP_REFERER'];
        } else {
            $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        $html .= '<a class="sign-in" href="' . $auth->crowd_config['login_url'] . '?originalUrl=' . urlencode($pageURL) . '">';
        $html .= __($text, 'meeting-support');
        $html .= '</a>';
    }
    return $html;
}
