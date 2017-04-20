<? include "../templates/header.html"; ?>
<? include "../templates/nav-bar.html"; ?>
<? $page = 'accreditation'; include "../templates/business/_container-start.html"; ?>

<? include "../templates/business/accreditation.html"; ?>

<? include "../templates/business/_container-end.html"; ?>

    <script>jQuery.noConflict();</script>
    <script src="/m/assets/js/mootools/core-1.2.4.js"></script>
    <script src="/m/assets/js/mootools/more-1.2.4.2.js"></script>

    <script src="/m/assets/js/ebindr.js.php?flex"></script>
<? $_menuPage = '/m/report/e button m/?noheader&bid='.$_SESSION['bid'].'&lid=1&ebindr2=y' ?>
    <script type="text/javascript">
        jQuery( document ).ready(function() {
            var _menupage = '<?=$_menuPage?>';
            ebindr.initialize( function(){
                ebindr.button.activate('.button32, #ms');
                ebindr.initIFrame( 'frame_m', { contentURL : _menupage } );
            } );
        });
    </script>

<? include "../templates/footer.html"; ?>