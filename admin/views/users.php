<?php
/**
 * Represents the view for the user management interface.
 *
 * @since      1.0.0
 *
 * @package    Meeting_Support
 * @subpackage Meeting_Support/admin
 */

$users = $this->mps_get_all_users();

add_thickbox();
?>


<div class="wrap">
	<?php if (isset($_GET['updated'])) { ?>
	<div class="updated">
        <p>User updated</p>
    </div>
    <?php } ?>
	<?php if (isset($_GET['error']) && $_GET['error_message']) { ?>
	<div class="error">
        <p><?php echo htmlspecialchars($_GET['error_message']);?></p>
    </div>
    <?php } ?>
	<h1>Meeting Support - User Management</h1>
	<br>
	<a href="#TB_inline?width=350&height=250&inlineId=editUserThickbox" class="button button-primary thickbox btnAddUser">Add User</a>
	<br>
	<br>
	<table id="usersTable">
		<thead>
			<tr>
				<th>UUID</th>
				<th>Name</th>
				<th>Email</th>
				<th>Last Login</th>
				<th>Active</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user) { ?>
			<tr>
				<td><?php echo $user->uuid;?></td>
				<td><?php echo $user->name;?></td>
				<td><?php echo $user->email;?></td>
				<td><?php echo $user->last_login;?></td>
				<td><?php echo ($user->is_active == 1 ? 'Yes' : 'No');?></td>
				<td>
					<a href="#TB_inline?width=350&height=250&inlineId=editUserThickbox" type="button" class="button button-primary btnEditUser thickbox" data-user-id="<?php echo $user->uuid;?>">Edit User</a>
					<form class="frmResetUserPassword" method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
						<?php wp_nonce_field( 'mps_reset_password_user' );?>
						<input type="hidden" name="action" value="mps_reset_password_user"/>
						<input type="hidden" name="user_uuid" value="<?php echo $user->uuid;?>"/>

						<input type="submit" class="button btnResetUserPassword" value="Reset Password"/>
					</form>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<div id="editUserThickbox" style="display:none;">
	<form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
		<!-- add wp_nonce and identifier -->
		<?php wp_nonce_field( 'mps_edit_user' );?>
		<input type="hidden" name="action" value="mps_edit_user"/>
		<input type="hidden" name="user_uuid" value="0"/>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Name</th>
					<td>
						<input  type="text" name="user_name"/>
					</td>
				</tr>
				<tr>
					<th scope="row">Email</th>
					<td>
						<input required type="email" name="user_email"/>
					</td>
				</tr>
				<tr>
					<th scope="row">Is Active</th>
					<td>
						<input checked type="checkbox" name="is_active"/>
					</td>
				</tr>
				<br>
			</tbody>
		</table>
		<!-- submit -->
		<?php submit_button( 'Submit' );?>
		<p class="delete">
			<input type="submit" name="delete" id="delete" class="button button-secondary" value="Delete User" formnovalidate/>
		</p>
	</form>
</div>
<div class="ajax_loading"></div>
