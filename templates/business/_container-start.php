<style type="text/css">
    .ebindr-mobile-sidenav li a {
        padding: 10px 15px;
        font-size: 11pt;
    }
</style>

<?
$url = 'http://'.$_SERVER['SERVER_NAME'].'/m/api/business/basic/business/basic/' . $_SESSION['bid'];
$dba = json_decode( e2mobile::apiGet( $url ) );
$dba = $dba->results;
$dba = $dba[0];
?>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span3" style="padding-bottom: 10px;">

            <h3 style="padding-bottom: 10px;"><a alt="Company Name" href="/m/business.html?info=business-names"><?=$dba->Name?></a> <a alt="Edit Company Name" class="btn btn-primary" id="edit-complaint" href="/m/business/dba/edit.html?did=<?=$dba->DID?>"><i class="icon-edit icon-white"></i></a></h3>

            <div class="nav">
                <ul class="nav nav-tabs nav-stacked biz-collapse">
                    <li<?= ( $page == 'info' ? ' class="active"' : '' ) ?>><a alt="Record Information" href="/m/business.html">Record Information</a></li>
                    <li<?= ( $page == 'complaints' ? ' class="active"' : '' ) ?>><a alt="Complaints" href="/m/business/complaints.html">Complaints</a></li>
                    <li<?= ( $page == 'ratings' ? ' class="active"' : '' ) ?>><a alt="Ratings Info" href="/m/business/ratings.html">Ratings Info</a></li>
                    <li<?= ( $page == 'journal' ? ' class="active"' : '' ) ?>><a alt="Journal" href="/m/business/journal.html">Journal</a></li>
                    <li<?= ( $page == 'sales' ? ' class="active"' : '' ) ?>><a alt="Sales" href="/m/business/sales.html">Sales</a></li>
                    <li<?= ( $page == 'general' ? ' class="active"' : '' ) ?>><a alt="General" href="/m/business/general.html">General</a></li>
                    <li<?= ( $page == 'snapshot' ? ' class="active"' : '' ) ?>><a alt="Snapshot" href="/m/business/snapshot.html">Snapshot</a></li>
                    <li class="divider"></li>
                    <li class="force_update" <?= ( $page == 'force-update' ? ' class="active"' : '' ) ?>><a href="#">Force Update</a></li>
                </ul>
            </div>


        </div><!--/span-->

        <div class="span9">