<?php
/**
 * Represents the view for the Presentation management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */

$presentations = mps_get_all_presentations();
$presentation_dir = mps_get_option('presentations_dir');
$sessions = mps_get_all_sessions();
add_thickbox();
?>


<div class="wrap">
    <h1>Meeting Support - Presentation Management</h1>
    <?php if (isset($_GET['updated'])) { ?>
    <div class="updated">
        <p>Presentation updated</p>
    </div>
    <?php } ?>
    <?php if (isset($_GET['error'])) { ?>
    <div class="error">
        <p>Could not update presentation</p>
    </div>
    <?php } ?>
    <br>
    <div class="boot">
        <table id="presentationsTable" class="table">
            <thead>
                <tr>
                    <th style="width:1px;">#</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Session</th>
                    <th>Slot</th>
                    <th>Files</th>
                    <th>Submission Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presentations as $presentation) {
                    $session_info = ms_get_session_data($presentation->session_id);
                    $slot_info = ms_get_slot($presentation->slot_id);
                    if (! $session_info || ! $slot_info) {
                        echo '<tr class="danger">';
                    } else {
                        echo '<tr>';
                    }
                    echo '<td>' .$presentation->id . '</td>';
                    echo '<td>' . sanitize_text_field($presentation->title) . '</td>';
                    echo '<td><a href="mailto:' . sanitize_email($presentation->author_email) . '">' . $presentation->author_name . '</a></td>';
                    echo '<td>' . $session_info->name . '</td>';
                    echo '<td>' . @$slot_info->title . '</td>';
                    echo '<td>';
                    foreach (json_decode($presentation->filename) as $file) {
                        echo '<a target="_blank" href="/' . $presentation_dir . sanitize_file_name($file) . '"><i class="fa ' . get_file_icon_class($file) . '"></i></a>&nbsp;';
                    }
                    echo '</td>';
                    echo '<td>' . $presentation->submission_date . '</td>';
                    echo '<td>';
                    echo '<a href="#TB_inline?width=600&height=660&inlineId=editPresentationThickbox" class="thickbox edit-presentation btn btn-xs btn-default" data-presentation-id="' . $presentation->id . '">Edit</a>';
                    echo '</td>';
                    echo '</tr>';
}?>

            </tbody>
        </table>
    </div>
</div>


<div id="editPresentationThickbox" style="display:none;">
    <form method="POST" action="<?php echo admin_url('admin-post.php');?>" enctype="multipart/form-data">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_presentation');?>
        <input type="hidden" name="action" value="mps_edit_presentation"/>
        <input type="hidden" name="presentation_id" value="0"/>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Presentation Title</th>
                    <td>
                        <input required style="width:100%" type="text" name="presentation_title"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Author Name</th>
                    <td>
                        <input required style="width:100%" type="text" name="presentation_author"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Author Affiliation</th>
                    <td>
                        <input style="width:100%" type="text" name="presentation_author_affiliation"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Author Email</th>
                    <td>
                        <input required style="width:100%" type="text" name="presentation_author_email"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Author UUID</th>
                    <td>
                        <input required style="width:100%" type="text" name="presentation_author_uuid"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Presentation Session</th>
                    <td>
                        <select required style="width:100%" name="presentation_session">
                            <?php foreach ($sessions as $session) {
                                if ($session->is_intermission) {
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
                    <th scope="row">Presentation Files</th>
                    <td>
                        <div id="presentation_files"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Add File</th>
                    <td>
                        <input type="file" name="presentation_new_file"/>
                    </td>
                </tr>
                <br>
            </tbody>
        </table>
        <!-- submit -->

        <div class="buttons">
            <?php submit_button('Submit', 'primary', 'submit-presentation');?> <?php submit_button('Delete', 'delete', 'delete-presentation'); ?>
        </div>
    </form>
</div>


<div class="ajax_loading"></div>
