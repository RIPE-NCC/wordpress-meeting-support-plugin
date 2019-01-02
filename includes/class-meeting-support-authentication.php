<?php

/**
 * Handles all authentication-based functionality
 *
 * @link       https://www.ripe.net
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/includes
 */

/**
 * Executed on plugin load
 *
 * This class defines all code necessary to handle proper user authentication
 *
 * @since      1.0.0
 * @package    Meeting_Support
 * @subpackage Meeting_Support/includes
 * @author     Oliver Payne <opayne@ripe.net>
 */
class Meeting_Support_Auth
{
    public $user;

    public $auth_method;

    public $crowd_config;

    private static $instance;

    public function __construct()
    {
        // Set auth method to local, this is overwritten in the crowd.inc.php file (if it exists)
        $this->auth_method = 'local';

        $this->user = $this->get_current_user();
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Populate the user array with as much info as possible
     * @return array User information
     */
    private function get_current_user()
    {
        switch ($this->auth_method) {
            case 'crowd':
                return $this->get_current_user_crowd();
                break;
            case 'local':
                return $this->get_current_user_local();
                break;
            default:
                return [];
        }
    }


    private function get_current_user_crowd()
    {
        $cookie_name = $this->crowd_config['cookie'];
        if (isset($_COOKIE[$cookie_name])) {
            $token = $_COOKIE[$cookie_name];
            $current_user = $this->ms_crowd_validate_token($token);
            // We have a valid session, and the email address of the user
            if ($current_user) {
                $details = $this->ms_crowd_get_details($current_user['email']);
                $uuid = $details['uuid'];
                $user = [
                    'uuid' => $uuid,
                    'email' => $current_user['email'],
                    'name' => $current_user['display-name'],
                    'is_active' => $current_user['active'] ? 1 : 0
                ];
                return $user;
            }
        }
        return [];
    }

    private function get_current_user_local()
    {
        if ((isset($_SESSION['user']) && (isset($_SESSION['user']['uuid'])))) {
            // Add a check to see if the session is still valid
            $user = $this->getUserByUUID($_SESSION['user']['uuid']);
            if (! $user) {
                $_SESSION['user'] = [];
            } else {
                $_SESSION['user'] = (array) $user;
            }
        } else {
            $_SESSION['user'] = [];
        }

        return $_SESSION['user'];
    }

    private function ms_crowd_validate_token($token)
    {
        $response = $this->ms_crowd_request('usermanagement/latest/session/' . $token);
        if (! $response) {
            return false;
        }
        return $response['user'];
    }

    private function ms_crowd_request($url, $method = 'GET', $data = [])
    {
        $client_options = [
            'verify' => false,
            'base_uri' => $this->crowd_config['host'] . $this->crowd_config['path'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];

        $client = new \GuzzleHttp\Client($client_options);

        $data['auth'] = [
            $this->crowd_config['appusername'], $this->crowd_config['apppassword']
        ];

        // Try and make the request, return false if anything bad happens (and log it!)
        try {
            $response = $client->request($method, $url, $data);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Request was bad for some reason, we should log this
            $exception = json_decode($e->getResponse()->getBody(), true);
            if ($exception['reason'] != 'INVALID_SSO_TOKEN') {
                if (isset($exception['reason'])) {
                    mps_log($exception['reason']);
                }
                if (isset($exception['message'])) {
                    mps_log($exception['message']);
                }
            }
            return false;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            mps_log('ServerException');
            mps_log(json_encode($e));
            return false;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            mps_log('ClientException');
            mps_log(json_encode($e));
            return false;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            mps_log('RequestException');
            mps_log(json_encode($e->getResponse()));
            return false;
        }
        $body = json_decode($response->getBody(), true);
        return $body;
    }

    private function ms_crowd_get_attributes($username)
    {
        // Get attributes for the specified user name
        $cache = get_transient('ms_crowd_attrs_'.substr($username, 0, 30));
        if ($cache !== false) { //false if no cache
            return $cache;
        } else {
            $attrs = $this->ms_crowd_request("usermanagement/latest/user/attribute?username=" . urlencode($username));
            array_walk_recursive($attrs, 'ms_crowd_sanitise');
            set_transient('ms_crowd_attrs_'.substr($username, 0, 30), $attrs, 60);
            return $attrs;
        }
    }

    private function ms_crowd_get_details($username) // email address
    {
        // Get attributes for the specified user name
        $cache = get_transient('ms_crowd_dets_'.substr($username, 0, 31));
        if ($cache !== false) {
            return $cache;
        } else {
            $attrs = $this->ms_crowd_get_attributes($username);
            // Find UUID
            $uuid = '<none>';

            if (! isset($attrs['attributes'])) {
                return false;
            }
            foreach ($attrs['attributes'] as $attribute) {
                if ($attribute['name'] == 'uuid') {
                    $uuid = $attribute['values'][0];
                }
            }

            // Get other user details
            $details = $this->ms_crowd_request(
                "usermanagement/latest/search?entity-type=user&expand=user&restriction=uuid=".$uuid
            );
            $details = $details['users'][0];
            $details['uuid'] = $uuid;
            set_transient('ms_crowd_dets_'.substr($username, 0, 31), $details, 60);
            return $details;
        }
    }

    private function ms_crowd_get_details_by_uuid($uuid)
    {
        // Get attributes for the specified UUID
        // First try the cache, if the UUID is not found there get it from Crowd
        if (false === ($details = get_transient("crowd_$uuid"))) {
            // transient data not there/expired, lets get it again
            $details = $this->ms_crowd_request("usermanagement/latest/search?entity-type=user&expand=user&restriction=uuid=".$uuid);
            @$details = $details['users'][0];
            $details['uuid'] = $uuid;
            set_transient("crowd_$uuid", $details, 60);
        }
        // Let's sanitise everything that comes from crowd, just in case...
        array_walk_recursive($details, 'ms_crowd_sanitise');
        return $details;
    }

    public static function v4()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public function resetPassword($uuid)
    {

        // Add a check to see if the session is still valid
        global $wpdb;

        // Get the user of the person we're resetting the password for
        $user = $this->getUserByUUID($uuid);

        // If we don't match a user, we can't reset the password
        if (! $user) {
            return false;
        }

        // Generate a new password to use for the reset
        $new_password = generate_random_string(8);
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);


        // Update the user row with the new password
        $wpdb->update($wpdb->base_prefix . 'ms_users', ['password' => $new_password_hash], ['uuid' => $uuid]);

        $loader = new Twig_Loader_Filesystem(realpath(plugin_dir_path(__FILE__) . '../templates/mail'));
        $twig = new Twig_Environment($loader, []);

        // Send mail to user with their new password
        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(false);
        $mail->setFrom(
            mps_get_option('sender_email_address', 'ws@ripe.net'),
            mps_get_option('sender_email_name', 'RIPE NCC Web Services')
        );

        $mailcontent = $twig->render('reset_user_password.twig', [
            'name' => $user->name,
            'email' => $user->email,
            'new_password' => $new_password,
            'meeting_name' => mps_get_option('meeting_name'),
            'login_url' => get_site_url(null, 'login')
        ]);

        $mail->addAddress($user->email);
        $mail->Subject = 'Password reset requested for ' . mps_get_option('meeting_name', 'meeting website');
        $mail->Body = $mailcontent;
        $mail_sent = $mail->send();

        mps_log("Sending new password to " . $user->email . ", Success: " . ($mail_sent == 1 ? 'Yes' : 'No'));

        return true;
    }


    public function getCrowdUserByUUID($uuid)
    {
        $user = $this->ms_crowd_get_details_by_uuid($uuid);

        // Make sure it's a real user
        if (! isset($user['display-name'])) {
            return false;
        }

        $return = [];
        $return['name'] = $user['display-name'];
        $return['email'] = $user['email'];
        $return['uuid'] = $user['uuid'];
        $return['active'] = $user['active'];
        $return['timeofregistration'] = $user['created-date'];
        return $return;
    }

    public function getCrowdUserByEmail($email)
    {
        $user = $this->ms_crowd_get_details($email);

        if (! $user) {
            return false;
        }

        $return = [];
        $return['name'] = $user['display-name'];
        $return['email'] = $user['email'];
        $return['uuid'] = $user['uuid'];
        return $return;
    }

    public function getUserByUUID($uuid)
    {
        global $wpdb;

        $user = $wpdb->get_row(
            $wpdb->prepare(
                "
				SELECT * FROM `" . $wpdb->base_prefix . "ms_users`
				WHERE `uuid` = %s
				AND is_active = 1
				",
                $uuid
            )
        );

        return $user;
    }

    public function getLocalUserByEmail($email)
    {
        global $wpdb;

        $email = sanitize_email($email);

        $user = $wpdb->get_row(
            $wpdb->prepare(
                "
				SELECT * FROM `" . $wpdb->base_prefix . "ms_users`
				WHERE `email` = %s
				",
                $email
            )
        );

        return (array) $user;
    }

    public function getUserByEmail($email)
    {
        /**
         * Return a user array, based on the $email given
         */
        switch ($this->auth_method) {
            case 'crowd':
                return $this->getCrowdUserByEmail($email);
                break;
            case 'local':
                return $this->getLocalUserByEmail($email);
                break;
            default:
                return [];
        }
    }

    public function getLoginLink()
    {
        /**
         * return the login url link, regardless of the auth method
         */
        switch ($this->auth_method) {
            case 'crowd':
                return $this->crowd_config['login_url'];
                break;
            case 'local':
                return '/login/';
                break;
            default:
                return '/login/';
        }
    }

    public function updateLastLogin($uuid)
    {
        global $wpdb;
        $wpdb->update($wpdb->base_prefix . "ms_users", ['last_login' => date("Y-m-d H:i:s")], ['uuid' => $uuid]);
    }
}
