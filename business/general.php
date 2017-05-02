<? include "../templates/header.html"; ?>
<? include "../templates/nav-bar.html"; ?>
<? $page = 'general'; include "../templates/business/_container-start.html"; ?>

    <div class="row-fluid">
        <div class="span12">
            <? include "../templates/business/general.php"; ?>
        </div>
    </div>

<? include "../templates/business/_container-end.html"; ?>

    <script>jQuery.noConflict();</script>
    <script src="/m/assets/js/mootools/core-1.2.4.js"></script>
    <script src="/m/assets/js/mootools/more-1.2.4.2.js"></script>

    <script src="/m/assets/js/ebindr.js.php?flex"></script>
<? $_menuPage = '/m/report/e2m lite button bg/?noheader&bid='.$_SESSION['bid'].'&lid=1&ebindr2=y' ?>
    <script type="text/javascript">
        jQuery( document ).ready(function() {
            var _menupage = '<?=$_menuPage?>';
            ebindr.initialize( function(){
                ebindr.initIFrame( 'frame_g', { contentURL : _menupage } );
            } );
        });
    </script>

<? include "../templates/footer.html"; ?>