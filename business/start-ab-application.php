<? include "../templates/header.html"; ?>
<? include "../templates/nav-bar.html"; ?>
<? $page = 'start-ab-application'; include "../templates/business/_container-start.html"; ?>

<?php
$bid = $_SESSION['bid'];
$mergecode = '/m/report/lite button orderentry/?ebindr2=y&noheader&bid='.$bid.'&lid=1&cid=0&bbbid=9999&key1=&key2=&reportr=/m/report//?noheader&ebindr2=y&bid='.$bid.'&lid=1&cid=0&bbbid=9999';
$title = 'Start AB Application'
?>
<div class="row-fluid">
    <div class="span12">
        <? include "../templates/business/iframe.html"; ?>
    </div>
</div>

<? include "../templates/business/_container-end.html"; ?>

<script>jQuery.noConflict();</script>
<script src="/m/assets/js/mootools/core-1.2.4.js"></script>
<script src="/m/assets/js/mootools/more-1.2.4.2.js"></script>

<script src="/m/assets/js/ebindr.js.php?flex"></script>

<script type="text/javascript">
    jQuery( document ).ready(function() {
        var _menupage = '<?=$mergecode?>';
        ebindr.initialize( function(){
            ebindr.initIFrame( 'frame_g', { contentURL : _menupage } );
        } );
    });
</script>

<? include "../templates/footer.html"; ?>
