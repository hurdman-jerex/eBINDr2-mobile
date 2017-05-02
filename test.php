<?
$page = 'search';
$_SERVER['css'][] = '/m/assets/css/findr1.css';
include "templates/header.html";
include "templates/nav-bar.html";
include "templates/search/sub_nav.php";
?>
<div class="container-fluid" style="padding-top: 40px;">
    <div class="row-fluid">
        <div class="span12">
            <div id="findr2-search-container">
                <div class="" id="search-result" style="padding-top: 10px;">
                    <iframe id="findr2-search-frame" src="" marginwidth="0" marginheight="0" scrolling="auto" style="height: 565px; width: 100%;" frameborder="0" >

                    </iframe>
                </div>
            </div>
            <div id="findr2-loading" style="display: none; text-align: center;">
                <img src="/ebindr/images/findr1/loading2.gif" alt="Searching ..." style="padding-top: 100px;" /><br /><br />Searching
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    window.addEvent( 'domready', function(e) {
        if (document.getElementById('search-type').textContent == "ABs Near Address")
            ebindr.findr2.what = "ab-address";
    });
</script>
<script type="text/javascript">
    jQuery( document ).ready(function() {
        ebindr.initializeFindr( function(){
            ebindr.findr2.start();
            ebindr.findr2.initIFrame( 'findr2-search-frame' );
        } );
    });
</script>
<? include "templates/footer.html"; ?>

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