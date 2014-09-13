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

$publickey = "";
$privatekey = "";

function matchEmails($email) {
	$query = "SELECT * FROM users WHERE";
	$arguments = array();
	$email = strtolower($email);
	
	preg_match("/([^@]*)@(.*)$/i", $email, $matches);
	$user = $matches[1];
	$domain = $matches[2];
	if ($domain=="gmail.com" || $domain=="googlemail.com") {
		$query .= " `email`=%s";
		array_push($arguments, $user."@gmail.com");
		$query .= " OR `email`=%s";
		array_push($arguments, $user."@googlemail.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@gmail.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@googlemail.com");
	} else if ($domain=="ymail.com" || $domain=="yahoo.com") {
		$query .= " `email`=%s";
		array_push($arguments, $user."@ymail.com");
		$query .= " OR `email`=%s";
		array_push($arguments, $user."@yahoo.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@ymail.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@yahoo.com");
	} else if ($domain=="hotmail.com" || $domain=="outlook.com") {
		$query .= " `email`=%s";
		array_push($arguments, $user."@hotmail.com");
		$query .= " OR `email`=%s";
		array_push($arguments, $user."@outlook.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@hotmail.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@outlook.com");
	} else {
		$query .= " `email`=%s";
		array_push($arguments, $user."@".$domain);
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@".$domain);
	}
	$queryAarray = array($query);
	for ($i=0; $i<count($arguments); $i++) {
		array_push($queryAarray, $arguments[$i]);
	}
	$results = call_user_func_array('databaseQuery', $queryAarray);
	return $results;
}

$counts = databaseQuery("SELECT value FROM settings WHERE name='passwords'");
$count = databaseFetchAssoc($counts);
?>
Total count of passwords in database is <?=number_format($count['value'])?>.<br /><br />
<div class="jumbotron">
	<div class="centered">
		<h1>Check your email</h1>
		<p>
			<form role="form" id="search_start_form">
				<div class="row">
					<div class="col-lg-8">
						<div class="input-group">
							<input class="form-control search-query" type="text" placeholder="Email Address" id="search_start_field" name="email" value="<?=htmlspecialchars($_REQUEST['email'], ENT_COMPAT | ENT_HTML401, 'UTF-8', true)?>" />
							<span class="input-group-btn">
								<button class="btn btn-default" type="submit">Check</button>
							</span>
						</div>
					</div>
				</div>
			</form>
		</p>
		<?
		if (!empty($_REQUEST['email']) && $_REQUEST['sendemail']==1) {
			if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
				?><h3>What was entered is not an email address.</h3><?
			} else {
				require_once('recaptchalib.php');
				$resp = recaptcha_check_answer($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

				if (!$resp->is_valid) {
					?><h3>Wrong captcha value.</h3><?
				} else {
					$entries = matchEmails($_REQUEST['email']);
					$count = 0;
					$passwords = array();
					while ($entry = databaseFetchAssoc($entries)) {
						if (!empty($entry['password'])) {
							$count++;
							array_push($passwords, $entry['password']);
						}
					}
					if ($count) {
						$to = $_REQUEST['email'];
						$subject = "Password(s) requested.";
						$message = "The password(s) that were found and requested by you or someone else via https://gec.im/passwords/ are listed below:\n\n";
						for ($i=0; $i<count($passwords); $i++) {
							$message .= $passwords[$i]."\n";
						}
						$message .= "\nIf any of the password(s) listed is one you currently use, make sure that you change your password as soon as possible! Hackers released the password(s) listed and are probably working on the list to try and login to websites you use.\n\nI recomemnd that you use a password database to create secure passwords: https://lastpass.com/ https://agilebits.com/onepassword http://keepass.info/\nThis is a free service provided by James Coleman at https://mrgecko.org/";
						$additionalHeaders = array("Reply-To" => "James Coleman <james@coleman.io>");

						$ch = curl_init();//Using custom email server which automatically deletes sent email.
						curl_setopt_array($ch, array(
							CURLOPT_URL => "http://127.0.0.1:28001/",
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_POST => true,
							CURLOPT_POSTFIELDS => http_build_query(array(
								"user" => "password@birdim.com",
								"from" => "password@gec.im",
								"from-name" => "Password Check",
								"to" => $to,
								"subject" => $subject,
								"message" => $message,
								"headers" => json_encode($additionalHeaders)
								)),
							CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded")
							));
						$result = curl_exec($ch);
						curl_close($ch);
						$response = json_decode($result);
						if ($response->success) {
							?>
							<h3 style="color: #ff0000"><?=$count?> password(s) were emailed.</h3>
							<?
						} else {
							?>
							<h3 style="color: #ff0000">Error sending email, please contact <a href="mailto:james@coleman.io">james@coleman.io</a>.</h3>
							<?
						}
					} else {
						?>
						<h3>Failed as email address could not be found.</h3>
						<?
					}
				}
			}
		} else if (!empty($_REQUEST['email'])) {
			if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
				?><h3>What was entered is not an email address.</h3><?
			} else {
				$entries = matchEmails($_REQUEST['email']);
				$count = 0;
				while ($entry = databaseFetchAssoc($entries)) {
					if (!empty($entry['password'])) {
						$count++;
					}
				}
				if ($count) {
					require_once('recaptchalib.php');
					?>
					<h3 style="color: #ff0000"><?=$count?> password(s) found for your email address.</h3>
					<p><form action="<?=generateURL("?sendemail=1")?>" method="post">
						<?=recaptcha_get_html($publickey, null, true)?>
						<input type="hidden" name="email" value="<?=htmlspecialchars($_REQUEST['email'], ENT_COMPAT | ENT_HTML401, 'UTF-8', true)?>" />
						<button type="submit" class="btn btn-primary">Email password(s) to me</button>
					</form></p>
					<?
				} else {
					?>
					<h3>There were no passwords found.</h3>
					<?
				}
			}
		}
		?>
	</div>
</div>
This is a service to check to see if your password was leaked via the leak <a href="http://lifehacker.com/5-million-gmail-passwords-leaked-check-yours-now-1632983265" target="_blank">http://lifehacker.com/5-million-gmail-passwords-leaked-check-yours-now-1632983265</a> and verify that it is a password you use. This server does not log anything and it is <a href="https://en.wikipedia.org/wiki/Transport_Layer_Security" target="_blank">ssl encrypted</a>. All that is required for you to check your password is your email address. There is no need to enter your password and also if you enter your password it will fail the search as it only wants an email address!<br /><br />
When you tell my server to email you your password, it will send an email via ssl gmail to your account. The email account which is used has a strong random password and has a filter to auto delete emails sent. If the password that you receive is one you use, quickly change it as hackers have it and are likely trying to get into your accounts now!<br /><br />
If you would like to see the top 500 passwords in this database, visit <a href="https://gec.im/passwords.csv">https://gec.im/passwords.csv</a>.<br /><br />
If you find another leak of passwords, email me at <a href="mailto:james@coleman.io">james@coleman.io</a> and I will see if I can get data to import.<br /><br />
Recommended password database software to use includes: <a href="https://lastpass.com/" target="_blank">https://lastpass.com/</a> <a href="https://agilebits.com/onepassword" target="_blank">https://agilebits.com/onepassword</a> <a href="http://keepass.info/" target="_blank">http://keepass.info/</a><br /><br />
Source code for this site is at <a href="https://github.com/GRMrGecko/PasswordCheck" target="_blank">https://github.com/GRMrGecko/PasswordCheck</a>
<?
require_once("footer.php");
?>