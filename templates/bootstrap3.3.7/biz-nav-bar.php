<?
$url = 'http://'.$_SERVER['SERVER_NAME'].'/m/api/business/basic/business/basic/' . $_SESSION['bid'];
$dba = json_decode($bbapi->get($url))->results;
$dba = $dba[0];
?>

<style type="text/css">
.container-fluid>.navbar-collapse, .container-fluid>.navbar-header, .container>.navbar-collapse, .container>.navbar-header {
    margin-right: 0 !important;
    margin-left: 0 !important;
}
</style>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#biz-navbar-collapse" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/m/business-info.html" style="color: #333"><?= $dba->name ?></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="biz-navbar-collapse">
      <ul class="nav navbar-nav">
        <li<?= ( $page == 'info' ? ' class="active"' : '' ) ?>><a href="/m/business-info.html">Record Information</a></li>
        <li<?= ( $page == 'complaints' ? ' class="active"' : '' ) ?>><a href="/m/business/complaints.html">Complaints</a></li>
        <li<?= ( $page == 'ratings' ? ' class="active"' : '' ) ?>><a href="/m/business/ratings.html">Ratings Info</a></li>
        <li<?= ( $page == 'journal' ? ' class="active"' : '' ) ?>><a href="/m/business/journal.html">Journal</a></li>
        <li<?= ( $page == 'sales' ? ' class="active"' : '' ) ?>><a href="/m/business/sales.html">Sales</a></li>
        <li<?= ( $page == 'snapshot' ? ' class="active"' : '' ) ?>><a href="/m/business/snapshot.html">Snapshot</a></li>
        <li class="divider"></li>
        <li class="force_update" <?= ( $page == 'force-update' ? ' class="active"' : '' ) ?>><a href="#">Force Update</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>