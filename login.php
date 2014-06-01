<?php
require_once 'script/config/twConfig.php';
require_once 'lib/tmhOAuth.php';
require_once 'lib/tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => $CONSUMER_KEY,
  'consumer_secret' => $CONSUMER_SECRET,
));

session_start();


request_token($tmhOAuth, $SITE);


// Step 1: Request a temporary token
function request_token($tmhOAuth, $SITE)
{
  $code = $tmhOAuth->request(
    'POST',
    $tmhOAuth->url('oauth/request_token', ''),
    array(
      //'oauth_callback' => tmhUtilities::php_self()
	  'oauth_callback' => getCallBackUrl($SITE)
    )
  );

  if ($code == 200) {
    $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    authorize($tmhOAuth);
  } else {
    outputError($tmhOAuth);
  }
}


// Step 2: Direct the user to the authorize web page
function authorize($tmhOAuth)
{
  $authurl = $tmhOAuth->url("oauth/authorize", '') .  "?oauth_token={$_SESSION['oauth']['oauth_token']}";
  header("Location: {$authurl}");

  // in case the redirect doesn't fire
  echo '<p>To complete the OAuth flow please visit URL: <a href="'. $authurl . '">' . $authurl . '</a></p>';
}



function getCallBackUrl($SITE)
{
	return ($SITE . '/process.php');
}

function outputError($tmhOAuth)
{
  echo 'There was an error: ' . $tmhOAuth->response['response'] . PHP_EOL;
}
?>