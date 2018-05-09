<?php
/**
 * Represents the view for the slots management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */

$candidates = mps_get_all_pc_candidates();
foreach ($candidates as $candidate) {
    $candidate->votes = mps_get_pc_candidate_votes($this->auth, $candidate->id)['votes'];
    $candidate->aged_votes = mps_get_pc_candidate_votes($this->auth, $candidate->id)['aged_votes'];
}

usort(
    $candidates,
    function ($a, $b) {
        return strcmp($b->votes, $a->votes);
    }
);

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
    <h1>Meeting Support - PC Elections</h1>
    <br>
    <hr>
    <div class="container boot">
            <div class="row">
            <div class="col-xs-6">
                <table id="pc-elections-admin-table" class="table">
                    <thead>
                        <tr>
                            <th>Candidate Name</th>
                            <th># Votes</th>
                            <th># Safe Votes</th>
                            <th>% Safe</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($candidates as $candidate) {
                            //$votes_count = mps_get_pc_candidate_votes($candidate->id);
                            echo '<tr>';
                            echo '<td>' . $candidate->name . '</td>';
                            echo '<td>' . $candidate->votes . '</td>';
                            echo '<td>' . $candidate->aged_votes . '</td>';
                            echo '<td>0</td>';
                            echo '<td><button data-candidate-id="' . $candidate->id . '" class="delete-candidate btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></button></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <hr>
            </div>
            <div class="col-xs-6">
                <h4>Add Candidate</h4>
                <form class="form-inline" method="POST" action="<?php echo admin_url('admin-post.php');?>">
                    <?php wp_nonce_field('mps_edit_pc_candidate');?>
                    <input type="hidden" name="action" value="mps_edit_pc_candidate"/>
                    <div class="form-group">
                        <input type="text" class="form-control" name="candidate_name" placeholder="Jane Doe">
                    </div>
                    <button type="submit" class="btn btn-default">Add Candidate</button>
                </form>
            </div>
        </div>
    </div>

       <!-- end modal -->
<div class="ajax_loading"></div>