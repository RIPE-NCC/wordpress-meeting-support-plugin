<?php
/**
 * Represents the view for the speaker management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */

$speakers = mps_get_all_speakers();
$speakers_page = get_page_by_path('speakers');
add_thickbox();
?>


<div class="wrap">
    <?php if (isset($_GET['updated'])) { ?>
    <div class="updated">
        <p>Speaker updated</p>
    </div>
    <?php } ?>
    <?php if (isset($_GET['error']) && $_GET['message']) { ?>
        <div class="error">
            <p><?=htmlspecialchars($_GET['message'])?></p>
        </div>
    <?php } ?>
    <h1>Meeting Support - Speaker Management</h1>
    <br>
    <?php if (! $speakers_page) {
        echo '<div class="notice notice-error">';
        echo '<p><a target="_blank" href="' . site_url('/speakers/') .'">Speakers</a> page does not exist ';
        echo '</div>';
    }?>
    <a href="#TB_inline?width=350&height=360&inlineId=editSpeakerThickbox" class="button button-primary thickbox edit-speaker" data-speaker-id="0">Add Speaker</a>
    <br>
    <br>
    <table id="speakersTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>URL</th>
                <th>Bio(s)</th>
                <th>Allowed</th>
                <th>Tag(s)</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($speakers as $speaker) { ?>
            <tr class="<?=($speaker->allowed && ! empty($speaker->bio_texts)) ? 'speaker-allowed' : 'speaker-not-allowed'?>">
                <td><?=sanitize_text_field($speaker->name)?></td>
                <td><a target="_blank" href="<?=site_url('/speakers/' . sanitize_text_field($speaker->slug))?>"><?=sanitize_text_field($speaker->slug)?></a></td>
                <td>
                    <?php foreach ($speaker->bio_texts as $language => $bio) {
                        echo '<a href="#TB_inline?width=550&height=500&inlineId=editSpeakerBioThickbox" type="button" class="button button-primary edit-speaker-bio thickbox" data-speaker-id="' . $speaker->id . '" data-bio-language="' . $language . '">' . $language;
                        if (array_key_exists($language, $speaker->bio_texts_draft)) {
                            echo ' <i style="color: red;" class="fa fa-exclamation"></i>';
                        }
                        echo '</a> ';
                    } ?>
                    <?php foreach ($speaker->bio_texts_draft as $language => $bio) {
                        if (array_key_exists($language, $speaker->bio_texts)) {
                            continue;
                        }
                        echo '<a href="#TB_inline?width=550&height=500&inlineId=editSpeakerBioThickbox" type="button" class="button button-primary edit-speaker-bio thickbox" data-speaker-id="' . $speaker->id . '" data-bio-language="' . $language . '">' . $language;
                        if (array_key_exists($language, $speaker->bio_texts_draft)) {
                            echo ' <i style="color: red;" class="fa fa-exclamation"></i>';
                        }
                        echo '</a> ';
                    } ?>
                    <a href="#TB_inline?width=550&height=500&inlineId=editSpeakerBioThickbox" type="button" class="button button-primary edit-speaker-bio thickbox" data-speaker-id="<?=$speaker->id?>" data-bio-language="">+</a>
                </td>
                <td><?=($speaker->allowed == 1 ? 'Yes' : 'No')?></td>
                <td><?=$speaker->tags?></td>
                <td>
                    <a href="#TB_inline?width=350&height=360&inlineId=editSpeakerThickbox" type="button" class="button button-primary edit-speaker thickbox" data-speaker-id="<?=$speaker->id?>">Edit</a>
                    <a href="#" type="button" class="button delete-speaker" data-speaker-id="<?=$speaker->id?>">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div id="editSpeakerThickbox" style="display:none;">
    <form method="POST" action="<?=admin_url('admin-post.php')?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_speaker');?>
        <input type="hidden" name="action" value="mps_edit_speaker"/>
        <input type="hidden" name="speaker_id" value="0"/>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Name</th>
                    <td>
                        <input style="width: 100%;" required type="text" name="speaker_name"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">UUID</th>
                    <td>
                        <input style="width: 100%;" required type="text" name="speaker_uuid"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Slug</th>
                    <td>
                        <input style="width: 100%;" required type="text" name="speaker_slug"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Tag(s)</th>
                    <td>
                        <input style="width: 100%;" placeholder="tag1, tag2, tag3" type="text" name="speaker_tags"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Is Allowed</th>
                    <td>
                        <input type="checkbox" name="speaker_allowed"/>
                    </td>
                </tr>
                <br>
            </tbody>
        </table>
        <!-- submit -->
        <div style="float: right;">
            <?php submit_button('Update Speaker');?>
        </div>
    </form>
</div>
<div id="editSpeakerBioThickbox" style="display:none;">
    <form method="POST" action="<?=admin_url('admin-post.php')?>">
        <!-- add wp_nonce and identifier -->
        <?php wp_nonce_field('mps_edit_speaker_bio');?>
        <input type="hidden" name="action" value="mps_edit_speaker_bio"/>
        <input type="hidden" name="speaker_id" value="0"/>
        <label for="bio_language">Bio Language</label>
        <br>
        <input style="width: 100%;" type="text" value="" name="bio_language" id="bio_language" placeholder="2 letter language code (en, fr, ru, de)"/>
        <br>
        <br>
        <label for="speaker_bio">Speaker Bio <small id="bio-draft-warning">Updated Version, save to apply</small></label>
        <textarea style="width: 100%;" rows="19" name="speaker_bio" id="speaker_bio"></textarea>
        <!-- submit -->
        <div style="float: right;">
            <?php submit_button('Update Bio');?>
        </div>
    </form>
</div>
<div class="ajax_loading"></div>
