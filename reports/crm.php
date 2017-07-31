<?php

/* REPORT CRM */
$layout_template = 'crmeditr';
$device->variable("postinputs", "");
$current_query = $variables[0];
if(!empty($variables[2])) $current_query .= "." . $variables[2];
$device->define("current_query", $current_query.".editr");
$device->define("bid", "'".$_REQUEST["mybid"]."'");
$_SESSION['currentBID'] = $_REQUEST["mybid"];
include _MOBILECRM;


ob_start();
$crm = new mobileCrm();
//include MOBILE_TEMPLATE_URI . 'layouts/crmeditr.php';
include MOBILE_TEMPLATE_URI . 'components/crm/sendemail.php';;
$content = ob_get_contents();
ob_end_clean();

$device->define( "content", $content );
echo $device->buffer($layout_template);
//echo $content;
exit();