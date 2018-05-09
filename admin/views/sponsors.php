<?php
/**
 * Represents the view for the user management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */
add_thickbox();
wp_enqueue_media();
$sections = $this->mps_get_all_sponsor_sections();

?>


<div class="wrap">
	<?php if (isset($_GET['updated'])) { ?>
	<div class="updated">
        <p>Updated</p>
    </div>
    <?php } ?>
	<?php if (isset($_GET['error']) && isset($_GET['error_message'])) { ?>
	<div class="error">
        <p><?php echo htmlspecialchars($_GET['error_message']);?></p>
    </div>
    <?php } ?>
	<h1>Meeting Support - Sponsor Management</h1>
	<br>
	<a href="#TB_inline?width=350&height=250&inlineId=editSponsorSectionThickbox" class="button button-primary thickbox btnAddSponsorSection">Add Sponsor Section</a>
	<a href="#TB_inline?width=350&height=350&inlineId=editSponsorThickbox" class="button button-primary thickbox btnAddSponsor">Add Sponsor</a>

	<br>
	<br>
	<hr>
	<?php foreach ($sections as $section) { ?>
		<h3 style="color: <?php echo $section->text_colour;?>"><?php echo $section->name;?>
			<a href="#TB_inline?width=350&height=250&inlineId=editSponsorSectionThickbox" data-section-id="<?php echo $section->id;?>" class="button button-small button-primary thickbox btnEditSponsorSection">Edit</a></h3> <pre>[sponsors section="<?php echo $section->id;?>"]</pre>
			<br>
			<?php
			$sponsors = $this->mps_get_section_sponsors($section->id);
			$i = 1;
			$count = count($sponsors);
			foreach ($sponsors as $sponsor) {
				// Loop through the sponsors for that specific section
				echo '<div data-id="' . $sponsor->id .'" class="sponsor_container">';
				if ($i != 1) {
					echo '<input class="btnMoveSponsorUp" value="<" type="button"/>';
				}
				if ($i < $count) {
					echo '<input class="btnMoveSponsorDown" value=">" type="button"/>';
				}

				echo '<a type="button" href="#TB_inline?width=350&height=350&inlineId=editSponsorThickbox" class="button button-small thickbox btnEditSponsor">Edit</a>';
				//echo '<input class="btnEditSponsor" value="Edit" type="button"/>';
				echo '<input class="btnDeleteSponsor" value="x" type="button"/>';
				echo '<img title="' . sanitize_text_field($sponsor->name) . '" class="mps_sponsor_logo" src="' . esc_url($sponsor->image_url) . '">';
				echo '</div>';
				$i++;
			} ?>


	<?php }	?>
</div>

<div id="editSponsorSectionThickbox" style="display:none;">
	<form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
		<!-- add wp_nonce and identifier -->
		<?php wp_nonce_field( 'mps_edit_sponsor_section' );?>
		<input type="hidden" name="action" value="mps_edit_sponsor_section"/>
		<input type="hidden" name="section_id" value="-1"/>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Name</th>
					<td>
						<input required type="text" name="section_name"/>
					</td>
				</tr>
				<tr>
					<th scope="row">Title Colour</th>
					<td>
						<input required type="color" name="section_text_colour"/>
					</td>
				</tr>
				<tr>
					<th scope="row">Grayscale Logo Images?</th>
					<td>
						<input type="checkbox" name="section_is_grayscale"/>
					</td>
				</tr>
				<br>
			</tbody>
		</table>
		<!-- submit -->
		<?php submit_button( 'Submit' );?>
	</form>
</div>

<div id="editSponsorThickbox" style="display:none;">
	<form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
		<!-- add wp_nonce and identifier -->
		<?php wp_nonce_field( 'mps_edit_sponsor' );?>
		<input type="hidden" name="action" value="mps_edit_sponsor"/>
		<input type="hidden" name="sponsor_id" value="-1"/>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Section</th>
					<td>
						<select required name="sponsor_section_id">
							<?php
							foreach ($sections as $section) {
								echo '<option value="' . esc_html($section->id) . '">' . esc_html($section->name) . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">Name</th>
					<td>
						<input required type="text" name="sponsor_name"/>
					</td>
				</tr>
				<tr>
					<th scope="row">Image</th>
					<td>
						<label for="upload_image">
						    <input required id="upload_image" type="text" size="20" name="sponsor_logo_url" placeholder="https://" />
						    <input id="upload_image_button" class="button" type="button" value="Pick Logo" />
						    <br />Enter a URL or upload a logo
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">URL</th>
					<td>
						<input required type="text" name="sponsor_url"/>
					</td>
				</tr>
				<br>
			</tbody>
		</table>
		<!-- submit -->
		<?php submit_button( 'Submit' );?>
	</form>
</div>
<div class="ajax_loading"></div>
