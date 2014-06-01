<?php
require_once 'script/config/twConfig.php';
require_once 'lib/tmhOAuth.php';
require_once 'lib/tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => $CONSUMER_KEY,
  'consumer_secret' => $CONSUMER_SECRET,
));

session_start();


if (isset($_REQUEST['oauth_verifier']) )
{
	access_token($tmhOAuth, $SITE);
}
elseif(isset($_REQUEST['denied']) )
{
	header('Location: ' . $SITE);
}
?>
<?php
if (isset($_SESSION['access_token']))
{
	include_once("script/processgui.php");
}
else
{
	header('Location: ' . $SITE);
}
?>

<?php //******** Functions *******\\

// Step 3: This is the code that runs when Twitter redirects the user to the callback. Exchange the temporary token for a permanent access token
function access_token($tmhOAuth, $SITE)
{
	$tmhOAuth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
	$tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

	$code = $tmhOAuth->request(
		'POST',
		$tmhOAuth->url('oauth/access_token', ''),
		array(
		'oauth_verifier' => $_REQUEST['oauth_verifier']
		)
	);

	if ($code == 200)
	{
		$_SESSION['access_token'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
		unset($_SESSION['oauth']);
		$tmhOAuth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
		$tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];
		$tmhOAuth->config['user_id'] = $_SESSION['access_token']['user_id'];
		$tmhOAuth->config['screen_name'] = $_SESSION['access_token']['screen_name'];

		saveUser($tmhOAuth);
		$isAdded = addUserToLadder($tmhOAuth);

		if($isAdded == 1)
		{
			postOnTwitter($tmhOAuth);
			followUsers($tmhOAuth);
		}

		header('Location: ' . tmhUtilities::php_self() );
	}
	else
	{
		header('Location: ' . $SITE);
	}
}


// Step 4: Now the user has authenticated, do something with the permanent token and secret we received
function verify_credentials($tmhOAuth)
{
  $tmhOAuth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
  $tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

  $code = $tmhOAuth->request(
    'GET',
    $tmhOAuth->url('1/account/verify_credentials')
  );

  if ($code == 200) {
    $resp = json_decode($tmhOAuth->response['response']);
    echo '<h1>Hello ' . $resp->screen_name . '</h1>';
	print_r($tmhOAuth->response['headers']);
    echo '<p>The access level of this token is: ' . $tmhOAuth->response['headers']['X-Access-Level'] . '</p>';
  } else {
    outputError($tmhOAuth);
  }
}

function saveUser($tmhOAuth)
{
	require_once("script/dbmodels/userInfo.php");
	$userInfo = new UserInfo();

	$id = $tmhOAuth->config['user_id'];
	$name = $tmhOAuth->config['screen_name'];
	$token = $tmhOAuth->config['user_token'];
	$secret = $tmhOAuth->config['user_secret'];
	$created = time();
	$userInfo->newUser($id, $name, $token, $secret, $created);
}

function addUserToLadder($tmhOAuth)
{
	require_once("script/dbmodels/history.php");
	$history = new History();

	$id = $tmhOAuth->config['user_id'];
	$created = time();
	$items = $history->getHistory();

	if(is_array($items) && count($items)>=1)
	{
		//search the array for the current user, if user doesn't exist, add him to ladder
		foreach($items as $item)
		{
			if($item == $id)
			{
				return 0;
			}
		}
		$history->newHistory($id, $created);
		return 1;
	}
	else
	{
		$history->newHistory($id, $created);
		return 1;
	}
	return 0;
}

function outputError($tmhOAuth)
{
	echo 'There was an error: ' . $tmhOAuth->response['response'] . PHP_EOL;
}

function wipe($SITE)
{
	session_destroy();
	//header('Location: ' . tmhUtilities::php_self());
	header('Location: ' . $SITE);
}

function postOnTwitter($tmhOAuth)
{
	//***** Replace the below images with your own (you can increase/decrease the number) *****\\
	$message = array();
	$message[] = "Get #FREE #Followers: http://goo.gl/a0R05";
	$message[] = "Get FREE Followers: http://goo.gl/a0R05 #FreeFollowers #TeamFollowBack #InstantFollowBack #TeamAutoFollow #ff";
	$message[] = "FREE FOLLOWERS: #FreeFollowers http://goo.gl/a0R05";
	$message[] = "Want FREE FOLLOWERS? Click here: http://goo.gl/a0R05 #FreeFollowers";
	$message[] = "GET MORE BEST FRIENDS http://goo.gl/a0R05 #FreeFollowers";
	$message[] = "http://goo.gl/a0R05 FREE Followers #TeamFollowBack #InstantFollowBack #TeamAutoFollow #ff";
	$message[] = "GET FREE Followers, please visit: http://goo.gl/a0R05 #FreeFollowers #TeamFollowBack";
	$message[] = "Want some FREE followers? Check http://goo.gl/a0R05 #FreeFollowers #TeamFollowBack";
	$message[] = "#TeamFollowBack #InstantFollowBack #TeamAutoFollow #ff DO YOU WANT NEW FOLLOWERS? http://goo.gl/a0R05";
	$message[] = "THIS SITE IS NUMBER ONE FOR NEW FOLLOWERS http://goo.gl/a0R05 |#ff #FollowBack #TeamFollowBack"

	$randomNumber = rand( 0,(count($message)-1) );


	$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
		'status' => $message[$randomNumber]
	));

	if ($code == 200)
	{
		//If message is posted successfully, do something
		//mail("yourEmail", '200',tmhUtilities::pr(json_decode($tmhOAuth->response['response'])));
	}
	else
	{
		//If message is NOT posted successfully, do something
		//mail("yourEmail", 'err',$tmhOAuth->response['response']);
		error_log($tmhOAuth->response['response']);
	}
}

//follow users in the ladder
function followUsers($tmhOAuth)
{
	require_once("script/dbmodels/history.php");
	$history = new History();
	$items = $history->getHistory();

	$id = $tmhOAuth->config['user_id'];

	foreach($items as $item)
	{
		if($item == $id)
		{
			continue;
		}
		//follow user
		$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/friendships/create'), array(
		  'user_id' => $item, 'follow' => true
		));

		if ($code == 200)
		{
			//tmhUtilities::pr(json_decode($tmhOAuth->response['response']));
		}
		else
		{
			//mail("yourEmail", 'err',$tmhOAuth->response['response']);
			error_log($tmhOAuth->response['response']);
		}
	}

	//follow VIPs, you can add a code below to force the user to follow your donators/subscribers
}
?>