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

$publickey = "";
$privatekey = "";

function matchEmails($email) {
	global $_MGM;
	$query = "SELECT * FROM email WHERE";
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
	} else if ($domain=="hotmail.com" || $domain=="outlook.com" || $domain=="live.com") {
		$query .= " `email`=%s";
		array_push($arguments, $user."@hotmail.com");
		$query .= " OR `email`=%s";
		array_push($arguments, $user."@outlook.com");
		$query .= " OR `email`=%s";
		array_push($arguments, $user."@live.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@hotmail.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@outlook.com");
		$query .= " OR `email` LIKE %s";
		array_push($arguments, str_replace("%", "\\%", $user)."+%@live.com");
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

if ($_MGM['path'][1]=="email") {
	connectToDatabase();
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
						array_push($passwords, array($entry['password'], $entry['leak']));
					}
				}
				if ($count) {
					$to = $_REQUEST['email'];
					$subject = "Password(s) requested.";
					$message = "The password(s) that were found and requested by you or someone else via https://gec.im/passwords/ are listed below:\n\n";
					for ($i=0; $i<count($passwords); $i++) {
						$message .= $passwords[$i][0]." - ".$passwords[$i][1]."\n";
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
						?><h3 style="color: #ff0000"><?=$count?> password(s) were emailed.</h3><?
					} else {
						?><h3 style="color: #ff0000">Error sending email, please contact <a href="mailto:james@coleman.io">james@coleman.io</a>.</h3><?
					}
				} else {
					?><h3>Failed as email address could not be found.</h3><?
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
				<p>
					<span id="recaptcha_place"></span>
					<input type="hidden" name="email" value="<?=htmlspecialchars($_REQUEST['email'], ENT_COMPAT | ENT_HTML401, 'UTF-8', true)?>" />
					<button type="submit" class="btn btn-primary" id="email_me">Email password(s) to me</button>
				</p>
				<script type="text/javascript">
				Recaptcha.create("<?=$publickey?>",
					"recaptcha_place",
					{
						theme: "red",
						callback: Recaptcha.focus_response_field
					}
				);
				$("#email_me").click(function() {
					$("#email_loader").load("<?=$_MGM['installPath']?>api/email", {email: $("#email_field").val(), sendemail: "1", recaptcha_challenge_field: $("#recaptcha_challenge_field").val(), recaptcha_response_field: $("#recaptcha_response_field").val()}, function(response, status, xhr) {
				
					});
				});
				</script>
				<?
			} else {
				?><h3>There were no passwords found in this database.</h3><?
			}
		}
	}
	closeDatabase();
	exit();
} else if ($_MGM['path'][1]=="hash") {
	connectToDatabase();
	if (!empty($_REQUEST['sha1'])) {
		$entries = databaseQuery("SELECT * FROM `sha1` WHERE `hash`=%s", $_REQUEST['sha1']);
		$entry = databaseFetchAssoc($entries);
		if ($entry!=null) {
			?><h3 style="color: #ff0000">Password seems to have been leaked to hackers via <?=$entry['leak']?>.</h3><?
		} else {
			?><h3>No leaks known in this database.</h3><?
		}
	} else {
		?><h3>Enter a SHA1 hash.</h3><?
	}
	closeDatabase();
	exit();
}