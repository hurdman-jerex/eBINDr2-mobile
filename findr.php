<?php $template_folder = 'bootstrap3.3.7/';
$page = 'search';
$_SERVER['css'][] = '/m/assets/css/findr2.css';
include "templates/" . $template_folder . "header.html";
include "templates/" . $template_folder . "search/sub_nav.php";
?>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div id="findr2-search-container">
                    <div class="" id="search-result" style="padding-top: 10px;">
                        <div id="mfindr2-search-result-wrapper" class="col-lg-12" aria-live="assertive" role="status">
                            <!-- HTML will be inserted here -->
                        </div>
                    </div>
                </div>
                <div id="findr2-loading" style="display: none; text-align: center;">
                    <img src="/ebindr/images/findr1/loading2.gif" alt="Searching ..." style="padding-top: 100px;" /><br /><br />Searching
                </div>
            </div>
        </div>
    </div>
<? include "templates/" . $template_folder . "scripts.html"; ?>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            if (document.getElementById('search-type').textContent == "ABs Near Address")
                ebindr.findr2.what = "ab-address";
        });
    </script>
    <script type="text/javascript">
        jQuery( document ).ready(function() {
            ebindr.initializeMobileFindr( function(){
                ebindr.findr2.start();
                ebindr.findr2.initFindrWrapper();
            } );
        });
    </script>

<? include "templates/" . $template_folder . "footer.html"; ?>