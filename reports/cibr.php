<?php

/* REPORT CIBR */

$search=urldecode($regs[1]);
if(ereg("^([^0-9]+)-([0-9]{5})$", $search, $regs)) {
    $search=array("category"=>$regs[1], "zip"=>$regs[2]);
} elseif(isset($_GET["namesearch"])) {
    $search=array("name"=>$regs[1]);
} elseif(ereg("^([-() .0-9]+)$", $search, $regs)) {
    $search=array("phone"=>ereg_replace("[^0-9]","",$regs[1]));
} elseif(ereg("^(.+@.+[.].+)$", $search, $regs)) {
    $search=array("email"=>$regs[1]);
} elseif(ereg("^(.+[.].+)$", $search, $regs)) {
    $search=array("url"=>$regs[1]);
} elseif(ereg("^(.+)$", $search, $regs)) {
    $search=array("name"=>$regs[1]);
} else {
    echo "Incorrect search parameters";
    exit();
}
require DIR_LIBRARY."cibr.php";
$c = new cibr();
$c->paramfile="/var/tmp/cibr_params";
$c->noheader=isset($_GET["noheader"]);
$c->InternalSearch($search);
echo $c->results;