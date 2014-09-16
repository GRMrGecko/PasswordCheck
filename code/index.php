<?
//
//  Copyright (c) 2014 Mr. Gecko's Media (James Coleman). http://mrgeckosmedia.com/
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//

require_once("header.php");

connectToDatabase();
$counts = databaseQuery("SELECT value FROM settings WHERE name='email'");
$count = databaseFetchAssoc($counts);
?>
Total count of email and passwords in database is <?=number_format($count['value'])?>.<br />
<?
$counts = databaseQuery("SELECT value FROM settings WHERE name='hashed'");
$count = databaseFetchAssoc($counts);
?>
Total count of hashed passwords in database is <?=number_format($count['value'])?>.<br /><br />
<div class="jumbotron">
	<div class="centered">
		<h2>Check your email</h2>
		<p>
			This checks your email address against leaks of passwords and email addresses. If a match is found, you can have my server email you the password through gmail via ssl with settings to automatically permanently delete sent emails.
		</p>
		<p>
			<div class="row">
				<div class="col-lg-8">
					<div class="input-group">
						<input class="form-control email" type="text" placeholder="Email Address" id="email_field" name="email" value="<?=htmlspecialchars($_REQUEST['email'], ENT_COMPAT | ENT_HTML401, 'UTF-8', true)?>" />
						<span class="input-group-btn">
							<button class="btn btn-default" id="email_check">Check</button>
						</span>
					</div>
				</div>
			</div>
		</p>
		<span id="email_loader"></span>
		<script type="text/javascript">
		$("#email_check").click(function() {
			$("#email_loader").load("<?=$_MGM['installPath']?>api/email", {email: $("#email_field").val()}, function(response, status, xhr) {
				
			});
		});
		</script>
	</div>
</div>
<div class="jumbotron">
	<div class="centered">
		<h2>Check your password</h2>
		<p>
			<span style="color: #ff0000">Only enter your password on a website you trust!</span><br />If you trust me and what I say below, go ahead and enter your password.<br /><br />
			This field uses <a href="https://en.wikipedia.org/wiki/JavaScript" target="_blank">JavaScript</a> to check the strength of your password. Clicking the check button will <a href="https://en.wikipedia.org/wiki/Hash_function" target="_blank">hash</a> your password using JavaScript and send the hash to my server to check against my hash database for leaked passwords.
		</p>
		<p>
			<style>
			.password {
				width:;
			}
			#password_score {
				height: 5px;
			}
			.score_0 {
				width: 1%;
				background-color: #ff0000;
			}
			.score_1 {
				width: 25%;
				background-color: #ff7f00;
			}
			.score_2 {
				width: 50%;
				background-color: #ffff00;
			}
			.score_3 {
				width: 75%;
				background-color: #7f007f;
			}
			.score_4 {
				width: 100%;
				background-color: #00ff00;
			}
			</style>
			<div class="row">
				<div class="col-lg-8">
					<div class="input-group">
						<input class="form-control password" type="password" placeholder="Password" id="password_field" />
						<span class="input-group-btn">
							<input class="btn btn-default" type="button" id="password_show" value="Show">
						</span>
					</div>
					<div id="password_score" class="score_0">&nbsp;</div>
					<div id="password_stats"></div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-8">
					<div class="input-group">
						<input class="form-control sha1" type="text" placeholder="SHA1" id="sha1_field" name="sha1" value="<?=htmlspecialchars($_REQUEST['sha1'], ENT_COMPAT | ENT_HTML401, 'UTF-8', true)?>" />
						<span class="input-group-btn">
							<button class="btn btn-default" id="hash_check">Check</button>
						</span>
					</div>
				</div>
			</div>
			<script type="text/javascript">
			$("#password_show").click(function() {
				if ($("#password_field").attr("type")=="password") {
					$("#password_field").attr("type", "text");
					$("#password_show").val("Hide");
				} else {
					$("#password_field").attr("type", "password");
					$("#password_show").val("Show");
				}
			});
			$("#password_field").bind("input paste", function(event){
				var result = zxcvbn($(this).val());
				$("#password_score").attr("class", "score_"+result.score);
				$("#password_stats").html("Entropy: "+result.entropy+"<br />Estimated time for hackers to crack: "+result.crack_time_display+"<br />Estimated time for hackers to crack in seconds: "+result.crack_time);
				$("#sha1_field").val(CryptoJS.SHA1($(this).val()).toString());
			});
			</script>
		</p>
		<span id="hash_loader"></span>
		<script type="text/javascript">
		$("#hash_check").click(function() {
			$("#hash_loader").load("<?=$_MGM['installPath']?>api/hash", {sha1: $("#sha1_field").val()}, function(response, status, xhr) {
			
			});
		});
		</script>
	</div>
</div>
This server does not log anything and it is <a href="https://en.wikipedia.org/wiki/Transport_Layer_Security" target="_blank">ssl encrypted</a>. Any activity done on this page is safe from anyone including myself. If you don't trust me, download my source code and re-implement this on your own server.<br /><br />
If you would like to see the top 500 passwords in this database, visit <a href="https://gec.im/passwords.csv">https://gec.im/passwords.csv</a>.<br /><br />
If you find another leak of passwords, email me at <a href="mailto:james@coleman.io">james@coleman.io</a> and I will see if I can get data to import.<br /><br />
Recommended password database software to use includes: <a href="https://lastpass.com/" target="_blank">https://lastpass.com/</a> <a href="https://agilebits.com/onepassword" target="_blank">https://agilebits.com/onepassword</a> <a href="http://keepass.info/" target="_blank">http://keepass.info/</a><br /><br />
Source code for this site is at <a href="https://github.com/GRMrGecko/PasswordCheck" target="_blank">https://github.com/GRMrGecko/PasswordCheck</a><br /><br />
External code used is <a href="https://code.google.com/p/crypto-js/" target="_blank">CryptoJS</a>, <a href="https://developers.google.com/recaptcha/docs/php" target="_blank">recaptchalib</a>, <a href="https://github.com/dropbox/zxcvbn" target="_blank">zxcvbn</a>, <a href="https://jquery.com/" target="_blnak">jQuery</a>, and <a href="http://getbootstrap.com/" target="_blank">Bootstrap</a>.
<?
require_once("footer.php");
closeDatabase();
?>