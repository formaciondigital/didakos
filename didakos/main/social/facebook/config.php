<?php

//Get app data

$t_keys = Database::get_main_table(TABLE_REDES_SOCIALES);   
$sql = "select * from $t_keys where name='facebook'";
$result = api_sql_query($sql,__FILE__,__LINE__);
$temp_row = Database::fetch_array($result);
define('CONSUMER_KEY', $temp_row['consumer_key']);
define('CONSUMER_SECRET', $temp_row['consumer_secret']);

?> 
