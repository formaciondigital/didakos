<?php
session_start();

if(!extension_loaded('oauth')) {
	echo "You should install PECL lib OAuth";
	exit;
}
	
include('../includes/config.php');
require_once('../linkedin/Request.php');
require_once('../linkedin/Profile.php');
require_once('../linkedin/Connection.php');

/* Startup the autorisation precess and create an communication channel */
$linkedIn = new LinkedIn_Request();

/* Get the profile information */
$profile = $linkedIn->pullProfile($profileFields);

/* Get all connections from the profile */
$connections = $linkedIn->pullConnections();

?>
<h1>Profile</h1>
<?php echo $profile->getFirstname(); ?>&nbsp;<?php echo $profile->getLastname(); ?>
<?php echo $profile->getHeadline(); ?>
<?php echo $profile->getCurrentstatus(); ?>

<h1>Connections</h1>
<?php
foreach($connections As $connection)
{
	echo $connection->getFirstname() . ' ' . $connection->getLastname();
	echo "<br/>";
	echo $connection->getHeadline();
	echo "<hr>";
}
?>