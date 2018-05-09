<?php
/**
 * Represents the view for the slots management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */

$sessions = mps_get_all_sessions();
$cur_opt_day = '';
add_filter('wp_default_editor', create_function('', 'return "html";'));

if (! $sessions) {
    die('<div class="notice notice-error is-dismissible"><p>Cannot render Slots page until at least one session has been created</p></div>');
}

?>

<div class="wrap">
    <?php if (isset($_GET['updated'])) { ?>
    <div class="updated">
        <p>Updated</p>
    </div>
    <?php } ?>
    <?php if (isset($_GET['error']) && isset($_GET['message'])) { ?>
    <div class="error">
        <p><?php echo htmlspecialchars($_GET['message']);?></p>
    </div>
    <?php } ?>
    <h1>Meeting Support - Slot Management</h1>
    <br>
    <hr>
    <div class="container boot">
        <div class="col-md-6 well">
            Select Session: <select id="session_select">
            <?php
            foreach ($sessions as $session) {
                // We don't want intermissions or sessions with no name
                if ($session->is_intermission || trim($session->name) == '') {
                    continue;
                }
                // Are we showing a new day? If so then let's do an opt group.
                if ($cur_opt_day != date('dmy', strtotime($session->start_time)) && $cur_opt_day != '') {
                    echo '</optgroup>';
                }
                if ($cur_opt_day != date('dmy', strtotime($session->start_time))) {
                    echo '<optgroup label="' . date('l d F', strtotime($session->start_time)) . '">';
                }
                echo '<option value="' . $session->id . '">[' . date('H:i', strtotime($session->start_time)) . '] [' . $session->room . '] ' . $session->name . '</option>';
                $cur_opt_day = date('dmy', strtotime($session->start_time));
            }
            ?>
            </select> <a id="add_slot" href="#TB_inline?width=800&height=570&inlineId=edit_slot_thickbox" class="thickbox button button-primary">Add Slot</a>
            <hr>
            <table id="slot_management">
            </table>
        </div>

        <!-- Shortcode preview pane -->
        <div class="col-md-6">
            <pre id="shortcode_hinter">[session_slots session=""]</pre>
            <div id="slot_preview">
                Loading...
            </div>
        </div>
    </div>


    <!-- begin modal -->
    <?php add_thickbox(); ?>
    <div id="edit_slot_thickbox" style="display: none;" role="dialog" tabindex="-1">
        <div class="modal-body">
            <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
                <?php wp_nonce_field('mps_edit_slot');?>
                <input type="hidden" name="action" value="mps_edit_slot"/>
                <input type="hidden" name="slot_id" value=""/>


                Slot Session:<br />
                <select required name="session_id" id="session_select_modal">
                    <?php
                        $cur_opt_day = '';
                    foreach ($sessions as $session) {
                        // Dont show sessions which shouldn't have slots in them
                        if ($session->is_intermission == 1 || trim($session->name) == '') {
                            continue;
                        }
                        if ($cur_opt_day == '') {
                            echo '<optgroup label="'.date('l j F', strtotime($session->start_time)).'">';
                            $cur_opt_day = date('l', strtotime($session->start_time));
                        }
                        if ($cur_opt_day != date('l', strtotime($session->start_time))) {
                            $cur_opt_day = date('l', strtotime($session->start_time));
                            echo '</optgroup>';
                            echo '<optgroup label="'.date('l j F', strtotime($session->start_time)).'">';
                        }
                        echo '<option value="'.$session->id.'">'.stripslashes($session->name).' ('.date('H:i', strtotime($session->start_time)).'-'.date('H:i', strtotime($session->end_time)).')</option>';
                    }
                    ?></optgroup>
                </select>
                <br />
                Slot Title:<br />
                <input style="width:100%;" required class="form_control" type="text" name="slot_title" id="slot_title"/>
                <br />
                <?php wp_editor('', 'slot_content', array('wpautop' => false, 'media_buttons' => false, 'editor_height' => 250));?>
                <div class="clear"></div>
                <br />
                Slot Parent:
                <select required class="form-control" required name="slot_parent_id" id="slot_parent_id">
                    <option value="0">No parent</option>
                </select>
                <div class="clear"></div>
                <br />
                Slot Ratable?
                <input class="form_control" type="checkbox" id="slot_ratable" name="slot_rateable" checked="checked"/>
                <input style="float: right;" type="submit" class="button button-primary">
            </form>
        </div>
    </div>
       <!-- end modal -->

    <div class="ajax_loading"></div>
