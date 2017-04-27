<?
$page = 'search';
$_SERVER['css'][] = '/m/assets/css/findr1.css';
include "templates/header.html";
include "templates/nav-bar.html";
//include "templates/search/_container-start.html";
?>

<?
$mybindr->addparm( 'type', 'f' );
$mybindr->addparm( 'staff', $_COOKIE["reportr_username"] );
list($q)=$mybindr->getquery("e button sort" );
$q=$mybindr->ResolvePipes($q);
$result = mysql_db_query( LOCAL_DB, $q, $mybindr->db );
?>
<div id="findr2_contentWrapper" class="subnav subnav-fixed">
<div id="findr2-body">
    <ul class="findr-btns grad-lite-blue" style="background: #fff;">
        <li class="more" id="more-search" style="float: right;"><img src="/ebindr/images/arrowdown.gif" /></li>
        <li class="spacer" style="float: right;"></li>
        <? $i=0; while( $row = mysql_fetch_assoc($result) ) : ?>
            <li class="<?=$row['class']?><?=($i==0? ' left' : '' )?>"><?=$row['name']?></li>
            <li class="spacer"></li>
            <? $i++; endwhile; ?>
        <li class="editorderbtn">Edit Order</li>

        <?php
        if (eregi("atlanta|hurdmantest", $_SERVER['SERVER_NAME'])) //change server name to atlanta.ebindr.com
        {
            echo '<li class="spacer"></li>';
            echo '<li class="arb">ARB Num</li>';
        }
        ?></ul>
    <div style="clear: both;"></div>
    <div class="search-bar">
        <div id="search-by-type">Begins With *</div>
        <div class="search-type" id="search-type">Business Name</div>
        <div style="float: left; margin-top: 4px;"><input type="text" id="search-q" /></div>
    </div>
    <div id="more-list" class="hide-more">
        <div class="top"></div>
        <div class="list">
            <ul></ul>
            <div style="clear: both;"></div>
        </div>
        <div class="bottom"></div>
    </div>
    <div style="clear: both;"></div>
</div>
</div>
<div style="clear: both;"></div>
<div id="findr2-search-container"></div>
<div id="findr2-loading" style="display: none; text-align: center;">
    <img src="/ebindr/images/findr1/loading2.gif" alt="Searching ..." style="padding-top: 100px;" /><br /><br />Searching
</div>
<script type="text/javascript">
    window.addEvent( 'domready', function(e) {
        if (document.getElementById('search-type').textContent == "ABs Near Address")
            ebindr.findr2.what = "ab-address";
    });
</script>
<!--<div class="subnav">
    <ul class="nav nav-pills">
        <?/* while( $row = mysql_fetch_assoc($result) ) : */?>
            <?/* if( $nav == $row['class'] ){
                $label = $row['name'];
                $searchId = $row['id'];
            } */?>
            <li class="<?/*=$row['class']*/?><?/*= ($nav == $row['class'] ? ' active' : '') */?>"><a alt="<?/*=$row['name']*/?>" href="search.html?type=<?/*=$row['class']*/?>"><?/*=$row['name']*/?></a></li>
        <?/* endwhile; */?>
    </ul>
</div>

<form class="well form-search" method="post" action="" id="searchform" style="margin-top: 10px;" onsubmit="show_loader();">
    <input type="hidden" name="type" value="<?/*= $searchId */?>" />
    <div class="input-append">
        <input id="searchbox" class="input-xlarge" type="text" placeholder="<?/*= $label */?>" name="search" value="<?/*= (isset($_POST['search']) ? $_POST['search'] : '') */?>"><button type="submit" class="btn"><i class="icon-search"></i> Search</button>
        <span id="loader">&nbsp;&nbsp;<img src="/m/assets/img/159.gif" alt="searching..." height="25"/></span>
    </div>
</form>

<div class="" id="search-result">
    <iframe src="http://hurdmantest.ebindr.com/m/report/e2m%20lite%20findr%20n/?noheaderfindr&e2mfindr&ebindr2=y&find=test%25&bid=22311421&lid=1&key1=2000099" marginwidth="0" marginheight="0" scrolling="auto" style="height: 565px; width: 100%;" frameborder="0" >

    </iframe>
</div>
<script type="text/javascript">
    jQuery.noConflict();
    jQuery('#loader').fadeOut(100);
    jQuery('#search-result').hide().fadeIn(1000);
    function show_loader(){
        jQuery('#loader').fadeIn(300);
    }

</script>-->
    <div class="" id="search-result" style="padding-top: 10px;">
        <iframe id="findr2-search-frame" src="" marginwidth="0" marginheight="0" scrolling="auto" style="height: 565px; width: 100%;" frameborder="0" >

        </iframe>
    </div>
<script type="text/javascript">
    jQuery( document ).ready(function() {
        ebindr.initializeFindr( function(){
            ebindr.findr2.start();
            ebindr.initIFrame( 'findr2-search-frame' );
        } );
    });
</script>
<? //include "templates/search/_container-end.html"; ?>
<? include "templates/footer.html"; ?>
