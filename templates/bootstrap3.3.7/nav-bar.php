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
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="ebindr-mobile-navbar-collapse">
      <ul class="nav navbar-nav">
      <li <? if( $page == 'search' ) { ?> class="active" <? } ?>><a href="/m/index.html">Search</a></li>
                            
              <li <? if( $page == 'info' ) { ?> class="active" <? } ?> class="dropdown">
                                
                  <a class="dropdown-toggle" href="/m/business.html" data-toggle="dropdown">Business <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                                    <? if (securityCheck('/^b/', true)) { ?>
                      <li><a href="/m/business.html">Record Information</a></li>
                                    <? } ?>
                                    <? if (securityCheck('/^c/', true)) { ?>
                      <li><a href="/m/business/complaints.html">Complaints</a></li>
                                    <? } ?>
                      <li><a href="/m/business/ratings.html">Rating</a></li>
                                    <? if (securityCheck('bj') || securityCheck('b*')) { ?>
                      <li><a href="/m/business/journal.html">Journal</a></li>
                                    <? } ?>
                                    <? if (securityCheck('bs') || securityCheck('b*')) { ?>
                      <li><a href="/m/business/sales.html">Sales</a></li>
                                    <? } ?>
                                    <? if (securityCheck('is') || securityCheck('i*')) { ?>
                      <li><a href="/m/business/snapshot.html">Snapshot</a></li>
                                    <? } ?>
                      <li class="divider"></li>
                      <li class="force_update"><a href="#">Force Update</a></li>
                  </ul>
                  
              </li>
                            
              <li <? if( $page == 'abnearme' ) { ?> class="active" <? } ?>><a href="/m/abnearme.html">AB Near Me &trade;</a></li>
            </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</div>
</nav>
