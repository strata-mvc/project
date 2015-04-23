<h4><?php _e( 'App Passwords', 'it-l10n-ithemes-security-pro' ); ?></h4>

<script type="text/template" id="tmpl-app-inputs-template">
	<tr class="app-password" id="{{data.id}}">
		<td><em><?php _e( 'Application:', 'it-l10n-ithemes-security-pro' ); ?></em> <label style="font-weight: bold; " for="itsec_app_pass_pass_{{data.id}}">{{data.name}}</label></td>
		<td>
			<input class="pass-pass" id="itsec_app_pass_pass_{{data.id}}" name="itsec_app_pass[{{data.id}}][pass]" type="hidden" value="{{data.pass}}" readonly="readonly"/>
			<input class="pass-name" id="itsec_app_pass_name_{{data.id}}" name="itsec_app_pass[{{data.id}}][name]" type="hidden" value="{{data.name}}" readonly="readonly"/>
			<input class="pass-id" id="itsec_app_pass_id_{{data.id}}" name="itsec_app_pass[{{data.id}}][id]" type="hidden" value="{{data.id}}" readonly="readonly"/>
			<input class="button button-small button-primary delete-button" type="button" value="<?php _e( 'Remove', 'it-l10n-ithemes-security-pro' ); ?>"/>
		</td>
	</tr>
</script>

<div id="app-inputs">
	<table id="app-passwords"></table>

	<input type="button" class="button" name="itsec_two_factor_get_new_app_pass" id="itsec_two_factor_get_new_app_pass" value="<?php _e( 'Generate App Password', 'it-l10n-ithemes-security-pro' ); ?>"/>

	<div id="app-inputs-form" style="display: none;">
		<label for="itsec_new_app_pass_name"><strong><?php _e( 'Application name (required):', 'it-l10n-ithemes-security-pro' ); ?></strong>
		</label><input class="regular-text" id="itsec_new_app_pass_name" name="itsec_new_app_pass_name" type="text"/><br/>
		<label for="itsec_new_app_pass_pass"><strong><?php _e( 'Password:', 'it-l10n-ithemes-security-pro' ); ?></strong>
		</label><input class="regular-text" id="itsec_new_app_pass_pass" name="itsec_new_app_pass_pass" type="text" readonly="readonly"/>
		<button class="button button-small button-primary" id="itsec-app-pass-add-new"><?php _e( 'Add', 'it-l10n-ithemes-security-pro' ); ?></button>
	</div>
</div>