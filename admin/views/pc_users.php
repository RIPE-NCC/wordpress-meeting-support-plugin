<?php
/**
 * Represents the view for the PC User management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */

$users = $this->mps_get_all_pc_users();

$access_levels = array(
	0 => 'Anonymous',
	1 => 'Admin',
	2 => 'PC Chair',
	3 => 'PC Member',
	4 => 'Speaker',
	5 => 'WG Chair'
);

add_thickbox();
if ($this->auth->auth_method == 'local') {
	$user_suggestions = mps_get_all_local_users();
}
?>


<div class="wrap">
	<h1>Meeting Support - PC User Management</h1>
	<?php if ( isset( $_GET['updated'] ) ) { ?>
	<div class="updated">
        <p>PC User updated</p>
    </div>
    <?php } ?>
	<?php if ( isset( $_GET['error'] ) ) { ?>
	<div class="error">
        <p>Could not add user</p>
    </div>
    <?php } ?>
	<br>
	<form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
		<?php wp_nonce_field( 'mps_add_pc_user' );?>
		<input type="hidden" name="action" value="mps_add_pc_user"/>
		<table id="frmAddPCUser" class="form">
			<tr><td><b>Add PC User</b></td></tr>
			<tr>
				<th scope="row">Email Address</th>
				<td>
				<?php if ($this->auth->auth_method == 'local') { ?>
				<select name="new_user_email" required="required">
					<?php foreach ($user_suggestions as $user_suggestion) { ?>
					<option value="<?php echo $user_suggestion->email;?>"><?php echo (sanitize_text_field($user_suggestion->name) . ' (' . sanitize_email($user_suggestion->email) . ')');?></option>
					<?php } ?>
				</select>
				<?php } else { ?>
				<input required type="email" placeholder="address@address.com" name="new_user_email" value=""/>

				<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">Access Level</th>
				<td>
					<select required name="new_user_access_level">
					<?php foreach ($access_levels as $id => $level) {
						echo '<option value="' . $id . '">' . $level . '</option>';
					} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<input type="submit" class="button button-primary" value="Add User"/>
				</th>
			</tr>
		</table>
	</form>
	<table id="PCUsersTable" class="table">
		<thead>
			<tr>
				<th>UUID</th>
				<th>Name</th>
				<th>Email</th>
				<th>Access Level</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user) {
				$userinfo = $this->get_user_info($user->uuid);
			?>
			<tr>
				<td><?php echo $user->uuid;?></td>
				<td><?php echo htmlspecialchars($userinfo['name']);?></td>
				<td><?php echo htmlspecialchars($userinfo['email']);?></td>
				<!-- select for access level -->
				<td data-search="<?php echo $access_levels[$user->access_level];?>">
					<select class="selectUserAccessLevel">
						<?php foreach ($access_levels as $id => $level) {
							if ($user->access_level == $id) { ?>
								<option selected value="<?php echo $id;?>"><?php echo $level;?></option>
							<?php } else { ?>
								<option value="<?php echo $id;?>"><?php echo $level;?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
				<td>
					<button type="submit" class="button button-primary btnEditPCUser" data-user-id="<?php echo $user->uuid;?>">Update User</button>
					<button type="submit" class="button button-secondary btnDeletePCUser" data-user-id="<?php echo $user->uuid;?>">Delete User</button>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<div class="ajax_loading"></div>
