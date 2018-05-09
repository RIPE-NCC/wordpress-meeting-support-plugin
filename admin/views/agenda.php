<?php
/**
 * Represents the view for the agenda dashboard.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */

$meeting_timezone = mps_get_option('meeting_timezone');

$meeting_start_day = mps_get_option('meeting_start_date');
$meeting_end_day = mps_get_option('meeting_end_date');

$meeting_length_seconds = abs(strtotime($meeting_end_day) - strtotime($meeting_start_day));
$meeting_length_days = ($meeting_length_seconds / 60 / 60 / 24) + 1;

$agenda_start_time = mps_get_option('agenda_start_time');
$agenda_end_time = mps_get_option('agenda_admin_end_time');

$time_increments = mps_get_option('meeting_increments', 15); //minutes

$max_concurrent_sessions = 3;

$rooms = mps_get_option('rooms');

$intermission_config = mps_get_option('intermission_config');

// TODO Calculate the lowest common multiple properly from $max_concurrent_sessions
$lcm = 12;

$calendar_columns = $lcm * $meeting_length_days;

if (! $meeting_timezone || ! $meeting_start_day || ! $meeting_end_day || ! $agenda_start_time || ! $agenda_end_time) {
    die('<div class="notice notice-error is-dismissible"><p>Cannot render agenda page until all settings in the main config page have been entered</p></div>');
}

// Make datetimes objects for us to iterate with
$timeslot = new DateTime($meeting_start_day . ' ' . $agenda_start_time, new DateTimeZone($meeting_timezone));
$dateslot = new DateTime($meeting_start_day . ' ' . $agenda_start_time, new DateTimeZone($meeting_timezone));

$agenda_start = new DateTime($meeting_start_day . ' ' . $agenda_start_time, new DateTimeZone($meeting_timezone));
$agenda_end = new DateTime($meeting_start_day . ' ' . $agenda_end_time, new DateTimeZone($meeting_timezone));

$current_meeting_time = new DateTime('now', new DateTimeZone($meeting_timezone));

// Thickbox (WP Modal)
add_thickbox();
?>


<div class="wrap">
    <?php if (isset($_GET['updated'])) { ?>
    <div class="updated">
        <p>Agenda updated</p>
    </div>
    <?php } ?>
    <?php if (isset($_GET['error'])) { ?>
    <div class="error">
        <p><?php echo htmlspecialchars($_GET['message']);?></p>
    </div>
    <?php } ?>
    <h1>Meeting Support - Agenda Management</h1>
    <br>
    <a href="#TB_inline?width=600&height=420&inlineId=editSessionThickbox" class="button button-primary thickbox btnAddSession">Add Session</a>
    <br>
    <br>
    <table id="agendaTable">
        <?php
        // Iterate over all time slots, just adding $time_increments minutes each time
        echo '<thead>';
        echo '<tr>';
        echo '	<th colspan="3" class="rowTime">&nbsp;</th>';
        for ($i = $meeting_length_days; $i > 0; $i--) {
            echo '	<th colspan="' . $lcm . '"><b>' . $dateslot->format('l') . '</b><br>' . $dateslot->format('j F') . '</th>';
            $dateslot->modify('+1 day');
        }
        unset($dateslot);
        $dateslot = $agenda_start;
        echo '	</tr>';
        echo '</thead>';
        echo '<tbody>';
        for ($timeslot; $timeslot <= $agenda_end; $timeslot->modify('+'.$time_increments.' minutes')) {
            echo '<tr>';
            // First Column
            if ($timeslot->format('i') % 30 == 0) {
                echo '<td colspan="3">'.$timeslot->format('H:i').'</td>';
            } else {
                echo '<td colspan="3">&nbsp;<!-- ' . $timeslot->format('H:i') . ' --></td>';
            }
            // Loop through the number of days
            for ($i = $meeting_length_days; $i > 0; $i--) {
                // Are there any sessions running at this time?
                $this_slot = new DateTime($dateslot->format('Y-m-d') . ' ' . $timeslot->format('H:i'));
                $sessions_now = mps_sessions_at_datetime($this_slot, false);
                if ($sessions_now) {
                    // If the session has started at this slot, then we need to add it to the table
                    if ($sessions_now[0]->start_time == $this_slot->format('Y-m-d H:i:s')) {
                        $session_length_seconds = (strtotime($sessions_now[0]->end_time) - strtotime($sessions_now[0]->start_time));
                        $session_length_minutes = $session_length_seconds / 60;

                        // Don't let sessions overflow from the table, in case we need to add stuff after (like Sponsors)
                        if (date('H:i', strtotime($sessions_now[0]->end_time)) > $agenda_end_time) {
                            $session_length_seconds = (strtotime(substr($sessions_now[0]->end_time, 0, -8) . $agenda_end_time . ':00') - strtotime($sessions_now[0]->start_time));
                            $session_length_minutes = $session_length_seconds / 60;
                        }
                        $rowspan = $session_length_minutes / $time_increments;
                        foreach ($sessions_now as $session_now) {
                            $session_now->realtime = new DateTime($session_now->start_time, new DateTimeZone($meeting_timezone));
                            if ($session_now->is_intermission == '1') {
                                echo '<td style="background-color: ' . esc_html($intermission_config['colour']) . ';" colspan="' . ( $lcm / count($sessions_now) ) . '" rowspan="' . $rowspan . '">';
                                echo '<a style="color: ' . esc_html($intermission_config['text_colour']) . ';" data-session-id="' . $session_now->id . '" class="thickbox btnEditSession" href="#TB_inline?width=600&height=420&inlineId=editSessionThickbox">';
                                echo ( $session_now->hide_title == 0 ? ( esc_html(stripslashes($session_now->name)) ) : '' );
                                echo '</a>';
                                echo '</td>';
                            } else {
                                echo '<td style="background-color: ' . esc_html($rooms[$session_now->room]['colour']) . '" colspan="' . ( $lcm / count($sessions_now) ) . '" rowspan="' . $rowspan . '">';
                                echo '<a style="color: ' . esc_html($rooms[$session_now->room]['text_colour']) . '" data-session-id="' . $session_now->id . '" class="thickbox btnEditSession" href="#TB_inline?width=600&height=420&inlineId=editSessionThickbox">';
                                echo ( $session_now->hide_title == 0 ? ( esc_html(stripslashes($session_now->name)) ) : '' );
                                echo '</a>';
                                if ($session_now->name !== '' && $session_now->realtime < $current_meeting_time) {
                                    echo '<br>';
                                    echo '<br>';
                                    echo '<div class="boot">';
                                    echo '<button class="btn-xs btn btn-' . (mps_chat_exists($session_now->id) == true ? 'success' : 'default') . ' edit-chat-log" data-session-id="' . $session_now->id . '">Chat</button>';
                                    echo ' ';
                                    echo '<button class="btn-xs btn btn-' . (mps_steno_exists($session_now->id) == true ? 'success' : 'default') . ' edit-steno-log" data-session-id="' . $session_now->id . '">Steno</button>';                                    
                                    echo '</div>';
                                }
                                echo '</td>';
                            }
                        }
                    }
                    // If this slot is not the start of the session, then we need to print nothing, as the row has been filled by the session starting colspan
                } else {
                    // Is not currently part of a session, so let's put a <td> in
                    echo '<td colspan="' . $lcm . '">&nbsp;</td>';
                }
                $dateslot->modify('+1 day');
            }
            $dateslot->modify('-' . $meeting_length_days . ' days');
            // Reset the $dateslot back to the first day
            echo '</tr>';
        }

        echo '<tr class="managesponsorsrow">';
        echo '<td colspan="3"></td>';
        for ($i = $meeting_length_days; $i > 0; $i--) {
            echo '<td colspan="' . $lcm . '">';
            echo '<a href="#TB_inline?width=200&height=650&inlineId=editDaySponsorThickbox" data-date="' . $dateslot->format('Y-m-d') . '" class="thickbox button-primary btnEditAgendaSponsor">Manage Sponsors for ' . $dateslot->format('l') . '</a>';
            echo '</td>';
            $dateslot->modify('+1 day');
        }
        $dateslot->modify('-' . $meeting_length_days . ' days');
        echo '</tr>';

        echo '<tr class="sponsorhead">';
        echo '<td colspan="3"></td>';
        for ($i = $meeting_length_days; $i > 0; $i--) {
            $sponsor_config = mps_get_option('sponsor_day_' . $dateslot->format('Y-m-d'), array());
            if ($sponsor_config) {
                if ($sponsor_config['title'] == '') {
                    echo '<td colspan="' . $lcm . '"></td>';
                } else {
                    echo '<td class="sponsorcells" colspan="' . $lcm . '">';
                    if ($sponsor_config['title_url'] != '') {
                        echo '<a href="' . $sponsor_config['title_url'] . '">';
                    }
                    echo $sponsor_config['title'];
                    if ($sponsor_config['title_url'] != '') {
                        echo '</a>';
                    }
                    echo '</td>';
                }
            }
            $dateslot->modify('+1 day');
        }
        $dateslot->modify('-' . $meeting_length_days . ' days');

        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="3"></td>';
        for ($i = $meeting_length_days; $i > 0; $i--) {
            $sponsor_config = mps_get_option('sponsor_day_' . $dateslot->format('Y-m-d'), array());
            if ($sponsor_config) {
                if ($sponsor_config['title'] == '') {
                    echo '<td colspan="' . $lcm . '"></td>';
                } else {
                    echo '<td class="sponsorcells" colspan="' . $lcm . '">';
                    echo $sponsor_config['body'];
                    echo '</td>';
                }
            }
            $dateslot->modify('+1 day');
        }
        $dateslot->modify('-' . $meeting_length_days . ' days');

        echo '</tr>';
        echo '</tbody>';
        ?>

    </table>
</div>

<div id="editDaySponsorThickbox" style="display:none;">
    <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_day_sponsor');?>
        <input type="hidden" name="action" value="mps_edit_day_sponsor"/>
        <input type="hidden" name="date" value="0"/>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Title</th>
                    <td>
                        <input style="width:100%;" name="sponsor_title"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Title Link URL</th>
                    <td>
                        <input style="width:100%;" name="sponsor_title_url"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Sponsor HTML</th>
                    <td>
                        <textarea style="width:100%;" rows="15" name="sponsor_body"></textarea>
                    </td>
                </tr>
                <br>
            </tbody>
        </table>
        <!-- submit -->
        <?php submit_button('Submit');?>
    </form>
</div>

<div id="editChatThickbox" style="display:none;">
    <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_chat_log');?>
        <input type="hidden" name="action" value="mps_edit_chat_log"/>
        <input type="hidden" name="session_id" value="0"/>

        <textarea rows="20" style="padding:1em; width: 100%" name="chat_content"></textarea>

        <!-- submit -->
        <?php submit_button('Submit');?>
    </form>
</div>

<div id="editStenoThickbox" style="display:none;">
    <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_steno_log');?>
        <input type="hidden" name="action" value="mps_edit_steno_log"/>
        <input type="hidden" name="session_id" value="0"/>

        <textarea rows="20" style="padding:1em; width: 100%" name="steno_content"></textarea>

        <!-- submit -->
        <?php submit_button('Submit');?>
    </form>
</div>

<div id="editSessionThickbox" style="display:none;">
    <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_session');?>
        <input type="hidden" name="action" value="mps_edit_session"/>
        <input type="hidden" name="session_id" value="0"/>
        <table id="edit-session-table" class="form-table" style="margin-top: 0px;">
            <tbody>
                <tr>
                    <th scope="row">Session Name</th>
                    <td>
                        <input type="text" name="session_name"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Session Start</th>
                    <td>
                        <input required type="datetime-local" name="session_start" step="<?php echo $time_increments * 60;?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Session End</th>
                    <td>
                        <input required type="datetime-local" name="session_end" step="<?php echo $time_increments * 60;?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Session Room</th>
                    <td>
                        <select required name="session_room">
                            <option value="_">None</option>
                            <?php foreach ($rooms as $room) {
                                echo '<option value="' . htmlspecialchars($room['short']) . '">' . htmlspecialchars($room['long']) . '</option>';
} ?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Session URL</th>
                    <td>
                        <input type="text" name="session_url"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Is Intermission</th>
                    <td>
                        <input type="checkbox" name="is_intermission"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Is Ratable</th>
                    <td>
                        <input type="checkbox" name="is_rateable"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Is Streamed</th>
                    <td>
                        <input type="checkbox" name="is_streamed"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Is Social</th>
                    <td>
                        <input type="checkbox" name="is_social"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Hide Title</th>
                    <td>
                        <input type="checkbox" name="hide_title"/>
                    </td>
                </tr>
                <br>
            </tbody>
        </table>
        <!-- submit -->
        <div class="buttons">
            <?php submit_button('Submit', 'primary', 'submit-session');?> <?php submit_button('Delete', 'delete', 'delete-session'); ?>
        </div>
    </form>
</div>
<div class="ajax_loading"></div>
<script>
    var meeting_start = '<?php echo $meeting_start_day . 'T' .$agenda_start_time;?>';
</script>
