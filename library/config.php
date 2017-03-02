<?php
/**
* Mobile Config
*/
define( 'MOBILE_APPLICATION_FILENAME', 'mreport' );
$task = new db($variables["db"], $variables["host"]);
$branding = (NOT_BRANDED=="1"?"n":"Y");
// if we are in the print layout setup the template name
if(@eregi("$bindr",$variables[0])) $_GET[noheader]=" ";
// set mobile layout
$layout_template = "layout_mobile";
// setup avaliable templates
$device = new mobileDisplay(array($layout_template, "table", "auth", "header"));
$parse = new parse(); // initialize the parser
$reportr = new reportr($variables["db"], $variables["host"]);
if($_SERVER[SCRIPT_NAME]=='/sbq') {
    $parse->params[bid]=$parse->params[staff];
    if(eregi("[a-z]", $parse->params[bid])) $parse->params["bid"]=$reportr->background->get_var("select bid from staff where initials='".$_COOKIE["reportr_username"]."' union select bid from common.staff where initials='".$_COOKIE["reportr_username"]."' limit 1");
}

// if CUSTOM_HEADER is set then we'll use the customized header and we'll
// watch header calls within the application closer
if(isset($CUSTOM_HEADER)) $device->variable("custom_header", $CUSTOM_HEADER);

$device->variable("db", $variables["db"]);
$device->variable("application_name", APPLICATION_NAME);
$device->variable("application_power", APPLICATION_POWER);
$device->variable("application_owner", APPLICATION_OWNER);
foreach($_POST as $key => $value) {
    if($key!='which_table' && $key!='EXCEPTIONLIST' && !ereg("^limit",$key)) {
        $poststr.="$key=".urlencode(stripslashes($value))."&";
        $postinputs.="<input type=hidden name='$key' id='$key' value=\"".stripslashes($value)."\">\r\n";
    }
}
$reportr->poststr=$poststr;
foreach($_GET as $key => $value) {
    if($key!='which_table' && !ereg("^limit",$key)) {
        $poststr.="$key=".urlencode(stripslashes($value))."&";
        $postinputs.="<input type=hidden name='$key' id='$key' value=\"".stripslashes($value)."\">\r\n";
    }
}
if(isset($_COOKIE["reportr_username"])) $device->variable("postinputs", $postinputs);
//$device->variable("printr_string", USE_URI . '/?print=true&'.$poststr);
$device->variable("current_location", USE_URI);//.$poststr);
$device->variable("printr_string", USE_URI . '/?print=true&');//.$poststr);
$device->variable("exportr_string", $variables[0] . "," . $variables[2] . "," . $variables[3] . "," . urlencode($variables[4]) . $reportr->extension."tid=". $variables[1]);//."&".$poststr);
if(isset($_GET[editr]))
    $device->variable("current_query", $variables[0].".editr");
else
    $device->variable("current_query", $variables[0]);
if(isset($_COOKIE["reportr_username"]) and !empty($_COOKIE["reportr_username"])) {
    $device->variable("application_logged_in", ucfirst($_COOKIE["reportr_username"]));
}

// language definitions
$device->variable("lang", $variables["lang"]);
$device->variable("print_page", PRINT_PAGE);
$device->variable("export_data", EXPORT_DATA);
$device->variable("report_error", REPORT_ERROR);
$device->variable("version", APPLICATION_VERSION);
$device->variable("heirarchy", $reportr->heirarchy()); // add the heirarchy to the templates

if(strpos($_SERVER['SERVER_NAME'], "bureaudata")) {
    $device->variable("HEADER", $device->buffer("header"));
}

/**
* END OF CONFIG
*/
?>
