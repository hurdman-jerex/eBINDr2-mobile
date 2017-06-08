<!-- Placed at the end of the document so the pages load faster -->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="/m/assets/js/jquery/jquery-1.12.4.min.js"><\/script>')</script>

<script type="text/javascript" language="javascript" src="/m/assets/bootstrap3.3.7/js/bootstrap.min.js"></script>

<script type="text/javascript" language="javascript" src="/m/assets/js/docs.min.js"></script>

<script type="text/javascript" src="/m/assets/js/core/ajax.js"></script>
<script type="text/javascript" src="/m/assets/js/core/modal.js"></script>

<!--<script type="text/javascript" language="javascript" src="/m/assets/js/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="/m/assets/js/datatables/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" language="javascript" src="/m/assets/js/datatables/dataTables.responsive.min.js"></script>
<script type="text/javascript" language="javascript" src="/m/assets/js/datatables/responsive.bootstrap.min.js"></script>-->

<script type="text/javascript">jQuery.noConflict();</script>
<script type="text/javascript" src="/m/assets/js/mootools/core-1.4.0.js"></script>
<script type="text/javascript" src="/m/assets/js/mootools/more-1.4.0.1.js"></script>
<!--<script type="text/javascript" src="/js-bin/control2.js"></script>
<script type="text/javascript" src="/js-bin/functions.js"></script>-->
<script type="text/javascript" src="/m/assets/js/ebindr.js.php?flex"></script>

<!-- Custom Scripts in Mergecode [e2m custom scripts] -->
<!--<script type="text/javascript" src="/m/assets/js/custom-scripts.js.php?flex"></script>-->

<script type="text/javascript">
    jQuery(document).on('click', '.dropdown-toggle', function(e) {
        jQuery('.dropdown').show();
    });

    //Attaching event handler to .dropdown selector.
    jQuery('.dropdown').on({

        //fires after dropdown is shown instance method is called.(if you click //anywhere else)
        "shown.bs.dropdown": function() { this.close= false; },

        //when dropdown is clicked
        "click": function() { this.close= true; },

        //when close event is triggered
        "hide.bs.dropdown":  function() {
            jQuery( 'li.dropdown' ).show();
            return this.close; }
    });

    jQuery(document).ready(function() {
        if (document.getElementById('search-type').textContent == "ABs Near Address")
            ebindr.findr2.what = "ab-address";

        ebindr.initializeMobile2Findr( function(){
            ebindr.findr2.start();
            ebindr.findr2.initIFrame( 'findr2-search-frame' );
        } );
    });
</script>

<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/m/assets/debug/ie10-viewport-bug-workaround.js"></script>