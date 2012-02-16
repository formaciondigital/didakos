<?php

/*
 * Config details for the example
 */

define('CALLBACK_URL', 'http://version08.formaciondigital.com/main/social/linkedin/linkedin');
define('BASE_API_URL', 'https://api.linkedin.com');

define('REQUEST_PATH', '/uas/oauth/requestToken?oauth_callback=' . urlencode(CALLBACK_URL));
define('AUTH_PATH', '/uas/oauth/authorize');
define('ACC_PATH', '/uas/oauth/accessToken');

define('CUSTOMER_KEY', 'vu5O1DjAiE40zPS2rdtimmYyaucEeX7CHgHp9FDRSGQ8HhbKRoj6KraIkl5TXJ0T');
define('CUSTOMER_SECRET', '4ISa093CY4VBLmE5DpkjAlkTO_x074Bz4FJD_saJXOQzVWqzxnEBRr5xptg6dMUw');

$profileFields = array(	'id',
						'first-name',
						'last-name',
						'headline',
						'location',
						'industry',
						'distance',
						'relation-to-viewer',
						'current-status',
						'api-standard-profile-request');
