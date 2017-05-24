<div id="findr2-result-wrapper" class="container-fluid" ondblclick="DblClickHandle();">
    <input type="hidden" id="current_query_desc" value="<[current_query_desc]>&nbsp;">
    <div class="row-fluid">

        <a href="#" style="display:none;" id="cr-email" title="Email this report" lang="<[current_query]>"><img style="display:none;" src="/ebindr/images/icons16x/email.png" alt="Email this report" /><span style="display:none;"><[USED_PARAMETERS]></span></a>

        <form name="limit" action="" method="post">
            <[postinputs]>
            <input type="hidden"  name="which_table" id="which_table" value="" />
            <[content]>
            <[submit]>
        </form>
    </div>
</div>

<script type="text/javascript">
    if("<[current_query]>"!="lite button myalerts") {
        ebindr.current.editr="<[current_query]>";
        ebindr.current.editr_desc="<[current_query_desc]>";
    }
    var currentInsertEditr="<[current_query]>";
    var currentEditr="<[current_query]>";
    var currentEditrExt="<[current_location]>";
    var exportrString="<[exportr_string]>";
    var customextension="";
    var customeditrextension="";
    var issearchdrop=false;
    var ctrl_t=false;
    var _bid = <[bid]>;

    function DblClickHandle() {
        ebindr.current.editr="<[current_query]>";
        if(ebindr.current.editr.indexOf("editr")>0) return;
        switch("<[current_query]>") {
            case "e button m": case "lite button bg": return; break;
            case "e button c": ebindr.button.go("co"); return; break;
        }
        if( ebindr.current.editr.match( '^lite findr' ) ) {
            if ( ebindr.current.editr.match( 'Small Claims Case' ) ){
                ebindr.button.editr_edit("qvq Small Claims Cases.editr", _bid );
            }
            else ebindr.findr2.choose();
            }
            else if(typeof current_select=="undefined") ebindr.alert('Please select a record first', 'Error');
	    else ebindr.button.editr_edit(ebindr.current.editr+".editr", <[bid]>);

    }

    function fireClickEvent( control ) {
        if( document.all ) control.fireEvent( "onclick" );
        else {
            var clickEvent = window.document.createEvent("MouseEvent");
            clickEvent.initEvent("click", false, true);
            control.dispatchEvent(clickEvent);
        }
    }

    function findrOpenBID( bid, start, cid, minimize ){
        ebindr.openBID( bid, start, cid, minimize );
        window.dopage("records");
    }

    var editrtitle="<[current_query_desc]>";

    if("<[current_query]>"!="lite button myalerts" && "<[current_query]>"!="lite button info2") window.focus();
    if(document.complaintform) {
        for(var i=0;i<document.complaintform.elements.length;i++){
            if(document.complaintform.elements[i].type!='hidden' && (document.complaintform.elements[i].type!='select-one' || document.body.scrollHeight<=document.body.clientHeight)) {
                document.complaintform.elements[i].focus();
                if((ebindr.current.editr.indexOf('button m-.editr')>0 || ebindr.current.editr.indexOf('+.editr')>0 || ebindr.current.editr.indexOf('mp.editr')>0) && ebindr.current.editr.indexOf('Message')<1 && document.complaintform.elements[i].type!='select-one')
                    document.complaintform.elements[i].select();
                break;
            }
        }
    }
    if(document.login) {
        for(i=0;i<document.login.elements.length;i++){
            if(document.login.elements[i].type!='hidden') {
                document.login.elements[i].focus();
                break;
            }
        }
    }

    if(ebindr.current.editr) {
        if(ebindr.current.editr.indexOf( 'findr' )>0 && document.getElementById("1-1-3")) {
            fireClickEvent( document.getElementById("1-1-3") );
        }
        if(ebindr.current.editr.indexOf("lite button")>-1 && document.getElementById("1-1-2")) {
            fireClickEvent( document.getElementById( "1-1-2" ) );
        }
        if(ebindr.current.editr == "lite button c" && document.getElementById("2-1-4")) {
            document.getElementById("2-1-4").click();
            document.getElementById("2-1-4").children(0).click();
        }
        if(ebindr.current.editr.indexOf("editr")<0) window.scrollTo(window.editrLeft, window.editrTop);
    }

    if(issearchdrop) {
        document.complaintform.next.disabled=true;
        document.complaintform.next.value='Please wait...';
    }

    window.addEvent( 'domready', function(e) {
        if(window.customextension) {
            customextension=window.customextension;
            customeditrextension=window.customeditrextension;
        }
    });
</script>

<[restriction_link_noheader]><[iptracking]>