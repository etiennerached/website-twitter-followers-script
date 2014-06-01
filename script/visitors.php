<?php
require_once 'script/config/twConfig.php';
require_once 'script/dbmodels/history.php';

$history = new History();
$items = $history->getHistoryDetails();
?>

<div class="lastvisitors">
<div class="content">
<ul class="uList">

<?php
foreach($items as $item)
{
		echo "<li class='liList'>";
		echo "<a href='http://twitter.com/" . $item['name'] . "' title='@" . $item['name'] . "' rel='nofollow' target='_blank'>";
		echo "<img src='http://api.twitter.com/1/users/profile_image?user_id=" . $item['id'] . "&size=normal' />";
		echo "</a>";
		//print_r($resp);
		echo "</li>";
}
?>
</ul>
</div>
</div>