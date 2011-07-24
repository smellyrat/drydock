{* ReCAPTCHA tpl for image/traditional layout *}
	<tr>
		<td class="postblock">Verification Code</td>
		<td>
			{literal}<script type="text/javascript">var RecaptchaOptions = { theme: 'custom', lang: 'en', custom_theme_widget: 'recaptcha_widget'};</script>{/literal}
			<div id="recaptcha_widget" style="display: none;">
				<div id="recaptcha_image"></div>
				<input id="recaptcha_response_field" name="recaptcha_response_field" type="text" /> <a href="javascript:Recaptcha.reload()">Reload reCAPTCHA</a>
				<script type="text/javascript" src="http://api.recaptcha.net/challenge?k={$reCAPTCHAPublic}&lang=en"></script>
			</div>
		</td>
	</tr>