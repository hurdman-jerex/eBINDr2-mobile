<? include "../templates/header.html"; ?>
<? include "../templates/nav-bar.html"; ?>

<style type="text/css">
    .ebindr-mobile-sidenav li a {
        padding: 10px 15px;
        font-size: 11pt;
    }
</style>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span3" style="padding-bottom: 10px;">

            <h3 style="padding-bottom: 10px;">Quick Launch</h3>

            <div class="nav">
                <ul id="quick-launch" class="nav nav-tabs nav-stacked biz-collapse">
                    <li id="editorderbtn"></li>
                </ul>
            </div>


        </div><!--/span-->

        <div class="span9">

            <!-- Content here -->

        </div><!--/span-->
    </div><!--/row-->


</div><!--/.fluid-container-->

<script>jQuery.noConflict();</script>
<script src="/m/assets/js/mootools/core-1.2.4.js"></script>
<script src="/m/assets/js/mootools/more-1.2.4.2.js"></script>

<script src="/m/assets/js/ebindr.js.php?flex"></script>

<script>
    jQuery( document ).ready(function() {
        //ebindr.current.bid = '<?=$_SESSION['bid']?>';
        ebindr.initialize();
    });
</script>

<? include "../templates/footer.html"; ?>