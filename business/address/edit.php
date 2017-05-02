<? include "../../templates/header.html"; ?>
<? include "../../templates/nav-bar.html"; ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">

                <h2>Edit Address</h2>
                <iframe id="reportIframe" class="mochaIframe" src="" marginwidth="0" marginheight="0" scrolling="auto" style="height: 565px; width: 98%;" frameborder="0"></iframe>

            </div>
        </div>
    </div>

    <script>jQuery.noConflict();</script>
    <script src="/m/assets/js/mootools/core-1.2.4.js"></script>
    <script src="/m/assets/js/mootools/more-1.2.4.2.js"></script>

    <script src="/m/assets/js/ebindr.js.php?flex"></script>

<? $_menuPage='/m/editr/e2m lite button ba/?ebindr2&bid='.$_SESSION['bid'].'&key2='.$_GET['aid']; ?>
<? include "../../templates/business/js_editr.php"; ?>
<? include "../../templates/footer.html"; ?>