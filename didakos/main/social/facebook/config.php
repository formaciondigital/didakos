<?php

//Get app data

$t_keys = Database::get_main_table(TABLE_REDES_SOCIALES);   
$sql = "select * from $t_keys where name='facebook'";
$result = api_sql_query($sql,__FILE__,__LINE__);
$temp_row = Database::fetch_array($result);
define('CONSUMER_KEY', $temp_row['consumer_key']);
define('CONSUMER_SECRET', $temp_row['consumer_secret']);

// Get proxy configuration

$settings = Database::get_main_table (TABLE_MAIN_SETTINGS_CURRENT);
$sql = "Select selected_value from". $settings. "where variable='proxy'";
$res = 	api_sql_query($sql,__FILE__,__LINE__);
$temp_row = Database::fetch_array($res);   

if ($temp_row['selected_value'] != '');
{
    $proxy = $temp_row['selected_value'];

}
$sql = "Select selected_value from". $settings. "where variable='proxyuserpwd'";
$res = 	api_sql_query($sql,__FILE__,__LINE__);
$temp_row = Database::fetch_array($res);   
if ($temp_row['selected_value'] != '');
{
    $proxyuserpwd = $temp_row['selected_value'];
}
$sql = "Select selected_value from". $settings. "where variable='proxyauth'";
$res = 	api_sql_query($sql,__FILE__,__LINE__);
$temp_row = Database::fetch_array($res);   
if ($temp_row['selected_value'] != '');
{
    $proxyauth = $temp_row['selected_value'];
}

?> 
