<?
$page = 'search';
$_SERVER['css'][] = '/m/assets/css/findr1.css';
include "templates/header.html";
include "templates/nav-bar.html";
include "templates/search/sub_nav.php";
?>
    <div class="container-fluid" style="padding-top: 50px;">
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
        jQuery(document).ready(function() {
            if (document.getElementById('search-type').textContent == "ABs Near Address")
                ebindr.findr2.what = "ab-address";
        });
    </script>
    <script type="text/javascript">
        jQuery( document ).ready(function() {
            ebindr.initializeFindr( function(){

                /*ebindr.include( "/m/assets/js/jquery-1.12.4.min.js" );
                 ebindr.include( "/m/assets/js/bootstrap3.3.7.min.js" );
                 ebindr.include( "/m/assets/js/datatables/compress.min.js" );*/

                ebindr.findr2.start();
                ebindr.findr2.initIFrame( 'findr2-search-frame' );
            } );
        });
    </script>
<? include "templates/footer.html"; ?>