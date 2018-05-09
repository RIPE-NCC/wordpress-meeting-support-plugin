<?php
/**
 * Represents the view for the slots management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */


if ($this->auth->auth_method == "crowd") {
    $modal_height = '620';
} else {
    $modal_height = '300';
}

$videos = mps_get_all_videos($this->auth->auth_method);
$videos = array_reverse($videos);

$incomplete_videos = mps_get_option('incomplete_videos', array());

if (isset($_GET['show_all'])) {
    $show_all = true;
    $show_all_button_url = remove_query_arg('show_all');
    $show_all_button_text = 'Show only FINISHED';
} else {
    $show_all = false;
    $show_all_button_url = add_query_arg('show_all', true);
    $show_all_button_text = 'Show all Videos';
}

if (isset($_GET['autorefresh'])) {
    $autorefresh = true;
    $autorefresh_button_url = remove_query_arg('autorefresh');
    $autorefresh_button_text = 'Disable Autorefresh';
} else {
    $autorefresh = false;
    $autorefresh_button_url = add_query_arg('autorefresh', true);
    $autorefresh_button_text = 'Enable Autorefresh';
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
    <h1>Meeting Support - Videos <a id="add-video" href="#TB_inline?width=800&height=<?php echo $modal_height;?>&inlineId=edit_video_thickbox" class="thickbox button button-primary">Add Video</a></h1>
    <?php if ($this->auth->auth_method == 'crowd'):?>
    <br>
    <a class="button button-secondary" href="<?php echo $show_all_button_url;?>"><?php echo $show_all_button_text;?></a> 
    <a class="button button-secondary" href="<?php echo $autorefresh_button_url;?>"><?php echo $autorefresh_button_text;?></a> 
    <?php endif; ?>
    <hr>
    <div class="container boot">
        <div class="col-xs-12">
            <table id="video-admin-table" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($this->auth->auth_method == 'crowd'): ?>
                        <th>Presentation Title</th>
                        <th>Presenter Name</th>
                        <?php endif; ?>
                        <th>Session Name</th>
                        <th>Slot Name</th>
                        <?php if ($this->auth->auth_method == 'crowd'): ?>
                        <th>Status</th>
                        <th style="width:1px;">FLV</th>
                        <th style="width:1px;">MP4</th>
                        <?php else: ?>
                        <th style="width:1px;">Link</th>
                        <?php endif; ?>
                        <th style="width:1px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $video_archives_url = get_site_url(null, 'archive/video/');
                    $video_archives_dir = get_home_path() . 'archive/video/';
                    foreach ($videos as $video) {
                        if ($this->auth->auth_method == 'crowd') {
                            if ($video->status !== 'FINISHED' && $show_all == false) {
                                continue;
                            }
                            $flv_url = $video_archives_url . mps_recording2filename($video);
                            $mp4_url = $video_archives_url . mps_recording2filename($video, 'mp4');
                            $public_url = get_site_url(null, 'archives/video/' . $video->id);
                            $flv_path = $video_archives_dir . mps_recording2filename($video);
                            $mp4_path = $video_archives_dir . mps_recording2filename($video, 'mp4');
                            $session_info = ms_get_session_data($video->session_id);
                            $slot_info = ms_get_slot($video->presentation_id);
                            echo '<tr class="' . ((! $session_info || ! $slot_info) ? 'danger' : 'success') . '">';
                            echo '<td>' . $video->id . '</td>';
                            echo '<td>' . sanitize_text_field(stripslashes($video->presentation_title)) . '</td>';
                            echo '<td>' . sanitize_text_field(stripslashes($video->presenter_name)) . '</td>';
                            echo '<td>' . @$session_info->name . '</td>';
                            echo '<td>' . @$slot_info->title . '</td>';
                            echo '<td>' . $video->status . (in_array($video->id, $incomplete_videos) ? '<i title="Marked as incomplete" style="color: red; margin-left: 3px; display: inline" class="fa fa-2x fa-exclamation"></i>' : '') .  '</td>';
                            echo '<td><a target="_blank" href="' . $public_url . '"><i class="fa fa-2x '. (file_exists($flv_path) ? 'file-exists' : 'file-missing') .' fa-file-video-o"></i></a></td>';
                            echo '<td><a target="_blank" href="' . $public_url . '"><i class="fa fa-2x '. (file_exists($mp4_path) ? 'file-exists' : 'file-missing') .' fa-file-video-o"></i></a></td>';
                            echo '<td><a data-video-id="' . $video->id . '" href="#TB_inline?width=800&height=' . $modal_height . '&inlineId=edit_video_thickbox" class="btn-edit-video thickbox button button-primary">Edit</a></td>';
                            echo '</tr>';
                        } else {
                            $session_info = ms_get_session_data($video->session_id);
                            $slot_info = ms_get_slot($video->slot_id);
                            echo '<tr>';
                            echo '<td>' . $video->id . '</td>';
                            echo '<td>' . @$session_info->name . '</td>';
                            echo '<td>' . @$slot_info->title . '</td>';
                            echo '<td>' . $video->video_url . '</td>';
                            echo '<td><a data-video-id="' . $video->id . '" href="#TB_inline?width=800&height=620&inlineId=edit_video_thickbox" class="btn-edit-video thickbox button button-primary">Edit</a></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
            <hr>
        </div>
    </div>


    <!-- begin modal -->
    <?php add_thickbox(); ?>
    <?php if ($this->auth->auth_method == 'crowd'): ?>
<div id="edit_video_thickbox" style="display:none;">
    <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_video');?>
        <input type="hidden" name="action" value="mps_edit_video"/>
        <input type="hidden" name="video_id" value=""/>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Rename the file in the system</th>
                    <td>
                        <input type="checkbox" id="accept_danger" name="rename_file"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Flag this video as incomplete</th>
                    <td>
                        <input type="checkbox" name="incomplete_video"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Presenter Name</th>
                    <td>
                        <input readonly="readonly" required style="width:100%" type="text" name="presenter_name"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Presentation Title</th>
                    <td>
                        <input readonly="readonly" required style="width:100%" type="text" name="presentation_title"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Presentation Session</th>
                    <td>
                        <select required style="width:100%" name="presentation_session">
                            <?php
                            $sessions = mps_get_all_sessions();
                            echo '<option value="0">None</option>';
                            foreach ($sessions as $session) {
                                if ($session->is_intermission || trim($session->name) == '') {
                                    continue;
                                }
                                echo '<option value="'.$session->id.'">'.stripslashes($session->name).' (' . $session->room . ') ('.date('H:i', strtotime($session->start_time)).'-'.date('H:i', strtotime($session->end_time)).')</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Presentation Slot</th>
                    <td>
                        <select required style="width:100%" name="presentation_slot">
                          </select>                    
                    </td>
                </tr>
                <tr>
                    <th scope="row">Room</th>
                    <td>
                        <select name="room">
                            <?php
                            $rooms = mps_get_option('rooms');
                            foreach ($rooms as $room) {
                                echo '<option value="' . htmlspecialchars($room['short']) . '">' . htmlspecialchars($room['long']) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Status</th>
                    <td>
                        <input required style="width:100%" type="text" name="status"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Created</th>
                    <td>
                        <input placeholder="yyyy-mm-dd hh:mm:ss" readonly="readonly" required style="width:100%" type="text" name="created"/>
                    </td>
                </tr>
                <br>
            </tbody>
        </table>
        <!-- submit -->

        <!-- submit -->
        <div class="buttons">
            <?php submit_button('Submit', 'primary', 'submit-video');?> <?php submit_button('Delete', 'delete', 'delete-video', true, array('formnovalidate' => 'formnovalidate')); ?>
        </div>
    </form>
</div>
<?php else: ?>
<div id="edit_video_thickbox" style="display:none;">
    <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_video');?>
        <input type="hidden" name="action" value="mps_edit_video"/>
        <input type="hidden" name="video_id" value="-1"/>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Presentation Session</th>
                    <td>
                        <select required style="width:100%" name="presentation_session">
                            <?php
                            $sessions = mps_get_all_sessions();
                            echo '<option value="0">None</option>';
                            foreach ($sessions as $session) {
                                if ($session->is_intermission || trim($session->name) == '') {
                                    continue;
                                }
                                echo '<option value="'.$session->id.'">'.stripslashes($session->name).' (' . $session->room . ') ('.date('H:i', strtotime($session->start_time)).'-'.date('H:i', strtotime($session->end_time)).')</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Presentation Slot</th>
                    <td>
                        <select required style="width:100%" name="presentation_slot">
                          </select>                    
                    </td>
                </tr>
                <tr>
                    <th scope="row">Video URL</th>
                    <td>
                        <input required style="width:100%" type="url" name="video_url"/>
                    </td>
                </tr>
                <br>
            </tbody>
        </table>
        <!-- submit -->

        <!-- submit -->
        <div class="buttons">
            <?php submit_button('Submit', 'primary', 'submit-video');?> <?php submit_button('Delete', 'delete', 'delete-video', true, array('formnovalidate' => 'formnovalidate')); ?>
        </div>
    </form>
</div>
<?php endif;?>

       <!-- end modal -->
<div class="ajax_loading"></div>

<?php if ($autorefresh) { ?>
<script>
setTimeout(function(){
   window.location.reload(1);
}, 10000);
</script>
<?php } ?>