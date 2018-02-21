<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:variable name="host">http://accounts.wizards.com</xsl:variable>
	
	<xsl:template match="/data">
		<html>
			<head>
				<title>Wizards Account Page</title>
				<script type="application/javascript"><![CDATA[
addEventListener(
	"load",
	function(eve) {
		//login("", "", "true", "playlocation+name");
	},
	false
);

function getSamlWidget() {
	return document.getElementById("samlWidget").contentWindow;
}

function login(username, password, rememberMeFlag, onLoginError, requiredFieldSets) {
	var samlWidget = getSamlWidget();

	if (samlWidget) {
			try {
			samlWidget.dropSession();

			if (samlWidget.authenticate(username, password, rememberMeFlag, false) == samlWidget.ESSOConstants.essoTrue) {
				// OK, login and password check out, but we still need to:
				// - check version (migrate legacy)
				// - check t&c (get new terms signed)
				// - check required fields (get required fields)
				// - check for additional actions (e.g., claim win)
				// We do all of the above like this:
				// - Save the session token & remember me tokens to special cookies.
				// - Navigate (GET) to controller action CheckLoginComplete action.
				//   This action checks all of the above in sequence.
				// - If it hits anything that needs doing, then it returns the modal form that does that operation.
				//   In that modal form, it reproduces the hidden inputs for session and remember me tokens.
				//   That form posts to an action URL to receive the specific post for that operation.
				//   If successful, then that action redirects back to the CheckLoginComplete action and we loop.
				//   If unsuccessful, then that action redraws its form for the user to try again.
				// - If it doesn't hit anything that needs doing, then CheckLoginComplete returns the CompleteLogin view.
				//   This view renders script which calls InternalWidget's handleLoggedIn method.

				var sessionTokenObj = samlWidget.authenticateEntity.getSAMLToken();
				if (sessionTokenObj) {
					var sessionTokenString = $.param(sessionTokenObj);
					var rememberMeTokenObj = {
						RememberMeToken: samlWidget.authenticateEntity.getRememberMeToken()
					};

					var rememberMeTokenString = $.param(rememberMeTokenObj);

					$.cookie("loginSessionToken", sessionTokenString, { path: '/', domain: AccountsCookieDomain });
					$.cookie("loginRememberMeToken", rememberMeTokenString, { path: '/', domain: AccountsCookieDomain });

					window.location.href = "/Widget/CompleteLogin?requiredFieldSets=" + requiredFieldSets;

					debug("login ending");
					return false; // handled, so cancel any fallback behavior
				}
			}
		} catch (e) {
			debug(e);
		}
	}

	debug("login ending");
	return false; // handled, so cancel any fallback behavior
}

function debug(message) {
	console.log(message);
}

				]]></script>
			</head>
			<body>
				<iframe style="display: none;" id="samlWidget" src="/getResource.php/mtg/samlWidget"/>
				<form onsubmit="login(this.username, this.password, 'true', 'playlocation+name'); return false">
					<label>
						<input name="username" placeholder="username" required="required"/>
					</label>
					<label>
						<input name="password" placeholder="password" required="required"/>
					</label>
				</form>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>