<? $url = "http://".$_SERVER['SERVER_NAME']."/m/api/business/info/" . $_SESSION['bid'];
$business= json_decode($bbapi->get($url));
$business=$business->results;
$business=$business[0];
 ?>
 
 <? //include "templates/bootstrap3.3.7/biz-nav-bar.php";?>
 
 <? //echo '<pre>'.print_r( $_SESSION, true ) .'</pre>'; ?>
 
<style type="text/css">
.container-fluid>.navbar-collapse, .container-fluid>.navbar-header, .container>.navbar-collapse, .container>.navbar-header {
    margin-right: 0 !important;
    margin-left: 0 !important;
}
    .rating {
        border: 2px solid #0099cc;
        color: #0099cc;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        padding: 0 5px;
        display: inline-block;
        text-align: center;
        min-width: 20px;
        background-color: #e6e6e6;
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), to(#e6e6e6));
        background-image: -webkit-linear-gradient(top, #ffffff, #e6e6e6);
        background-image: -o-linear-gradient(top, #ffffff, #e6e6e6);
        background-image: linear-gradient(to bottom, #ffffff, #e6e6e6);
        background-image: -moz-linear-gradient(top, #ffffff, #e6e6e6);
        background-repeat: repeat-x;
    }

    .ab {
        margin: 10px 0;
    }
</style>
 
 <? //echo '<pre>'.print_r( $business, true ).'</pre>'; ?>
 <!--<h3><? //=$business->name?></h3>-->
 <div class="list-group">
  <a href="/m/business.html?info=rating-accreditation" class="list-group-item">
    <? if ($business->member == 'y'): ?>
        <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
    <? endif; ?>
    
    <div class="rating"><h5><?=$business->Letter?></h5></div>
  </a>
  <a href="/m/business.html?info=address" class="list-group-item"><i class="glyphicon glyphicon-map-marker"></i> <?=$business->street1?> <?=$business->street2?> <?=$business->city?>, <?=$business->stateprov?> <?=$business->postalcode?></a>
  <a href="/m/business.html?info=people-contacts" class="list-group-item"><i class="glyphicon glyphicon-user"></i> <?=$business->firstname?> <?=$business->lastname?>, <?=$business->title?></a>
  <a href="/m/business.html?info=phone-fax" class="list-group-item"><i class="glyphicon glyphicon-earphone"></i> <abbr title="Phone">P:</abbr> <?=$business->number?>, </a>
  <a href="/m/business.html?info=phone-fax" class="list-group-item"><i class="glyphicon glyphicon-print"></i> <abbr title="Fax">F:</abbr> <?=$business->faxnumber?></a>
  <a href="/m/business.html?info=email-website" class="list-group-item"><i class="glyphicon glyphicon-envelope"></i> <?=$business->email?></a>
  <a href="/m/business.html?info=email-website" class="list-group-item"><i class="glyphicon glyphicon-globe"></i> <?=$business->url?></a>
  <a href="/m/business.html?info=tobs" class="list-group-item"><i class="glyphicon glyphicon-folder-close"></i> <?=$business->TOB?></a>
</div>