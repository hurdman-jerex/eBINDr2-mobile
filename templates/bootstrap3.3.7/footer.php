<!--<script src="/m/assets/js/bootstrap.js"></script>-->  
<script src="/m/assets/bootstrap3.3.7/js/bootstrap.min.js"></script>  
  <? if ($page != 'abnearme')  { ?>
    
    <script src="/m/assets/js/m.js"></script>
    <? } ?>
    <script>
        jQuery('.force_update').click( function() {
            var xmlhttp;
            if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp=new XMLHttpRequest();
            } else {// code for IE6, IE5
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange= function() {
                if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                    alert(xmlhttp.responseText);
                    //jQuery('#force_success').fadeIn('slow', function() {});
                    //jQuery('#force_success').fadeOut(5000, function() {});                    
                }
            }
            xmlhttp.open("GET","/report/merge/update.htm/?NOASK&BYPASS=5g9f4ds8r&ebindr2=y&bid=<?=$_SESSION['bid'];?>",true);
            xmlhttp.send();
            return false;
        });
        jQuery('#force_exit').click( function() {
            jQuery('#force_success').hide();
        });
    </script>
    <style type="text/css">
    body { padding-bottom: 70px; }
    </style>
  </body>

</html>