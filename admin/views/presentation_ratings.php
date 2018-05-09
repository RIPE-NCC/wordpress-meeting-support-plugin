<?php
/**
 * Represents the view for the Presentation management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */
add_thickbox();
$ratings = mps_get_all_presentation_ratings();
$sessions = mps_get_all_sessions();
// Sort ratings by slot
$sorted_ratings = array();
$current_day = '';
foreach ($ratings as $rating) {
    $sorted_ratings[$rating->slot_id][] =  $rating;
}
?>


<div class="wrap"><button id="btnRandomRater" title="Pick a random person who has rated a presentation" style="float: right;">Random</button>
    <h1>Meeting Support - Presentation Ratings </h1>
    <br>
    <div class="boot">

        <table id="presentationRatingsTable" class="table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Slot</th>
                    <th>Session</th>
                    <th style="width: 1px;">C: #</th>
                    <th style="width: 1px;">C: Avg.</th>
                    <th style="width: 1px;">D: #</th>
                    <th style="width: 1px;">D: Avg.</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sorted_ratings as $slot => $ratings) {
                    $stats = mps_get_presentation_rating_stats($ratings);
                    $slot_info = ms_get_slot($slot);
                    $session_info = ms_get_session_data($slot_info->session_id);
                    // Try and get author(s) for this slot
                    $presentations = mps_get_presentations_for_slot($slot_info->id);
                    $authors = array();
                    foreach ($presentations as $presentation) {
                        $authors[] = sanitize_text_field($presentation->author_name);
                    }
                    echo '<tr>';
                    echo '<td>' . implode(', ', $authors) . '</td>';
                    echo '<td>' . $slot_info->title . '</td>';
                    echo '<td>' . $session_info->name . '</td>';
                    echo '<td>' . $stats['rating_content_count'] . '</td>';
                    echo '<td>' . $stats['rating_content_average'] . '</td>';
                    echo '<td>' . $stats['rating_delivery_count'] . '</td>';
                    echo '<td>' . $stats['rating_delivery_average'] . '</td>';
                    echo '<td>';
                    foreach ($ratings as $rating) {
                        if (trim($rating->rating_comment != '')) {
                            echo nl2br(escape_multiline_text($rating->rating_comment));
                            echo '<hr>';
                        }
                    }
                    echo '</td>';
                    echo '</tr>';
                }?>
            </tbody>
        </table>
    </div>
</div>
<br>
<div class="boot">
    <div class="col-xs-5 well">
        <h3>Export <small>choose sessions to export presentation rating for:</small></h3>
        <form method="POST" action="<?php echo admin_url('admin-post.php');?>">
            <?php wp_nonce_field('mps_export_presentation_ratings');?>
            <input type="hidden" name="action" value="mps_export_presentation_ratings"/>
            <?php foreach ($sessions as $session) {
                if ($session->is_intermission || $session->name == '') {
                    continue;
                }
                if ($current_day != $session->start_time) {
                    echo '<b>';
                    echo date('l H:i', strtotime($session->start_time));
                    echo '</b>';
                    $current_day = $session->start_time;
                }
                echo '<div class="checkbox">';
                echo '<label>';
                echo '<input name="export_sessions[]" checked="checked" type="checkbox" value="' . $session->id . '"/> ' . $session->name . ' [' . $session->room .']';
                echo '</label>';
                echo '</div>';
            } ?>
            <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-download"></i> Download</button>
        </form>
    </div>
</div>

<div id="random-rater-winner" style="display:none;">
   <p>Winner</p>
</div>

<div class="ajax_loading"></div>
