<nav class="navbar navbar-inverse navbar-fixed-top">
<div class="navbar-inner">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#ebindr-mobile-navbar-collapse" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#"><b style="color: #fff;">eBINDr</b> Mobile</a>
      
      <div class="dropdown btn-group pull-right">
        <button type="button" class="btn btn-default navbar-btn dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-user"></i> <?=$_SESSION['username']?><b class="caret"></b></button>
          <ul class="dropdown-menu">
              <li><a href="/m/sign-out.html">Sign Out</a></li>
          </ul>
      </div>

        <?php include "menu.php" ?>
    </div>

  </div><!-- /.container-fluid -->
</div>
</nav>
