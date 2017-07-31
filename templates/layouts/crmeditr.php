<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <[MYSQL_ERRORS]>
    <title><[current_query]></title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link href="/ebindr/views/crm/css/bootstrap-tabs-x.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.1/css/fixedHeader.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/ebindr/scripts/plugins/chosen.jquery.min.js"></script>
    <link href="/ebindr/styles/plugins/chosen.1.5.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>

    <!-- Report CSS -->
    <link href="/m/assets/css/report.css" rel="stylesheet">
    <link href="/m/assets/css/crmeditr.css" rel="stylesheet">

</head>
<body bgcolor="#ffffff">
<[custom_header]>

<div class="cr-top">
    <a href="javascript:Print('<[query_run]>');"><img src="/ebindr/images/icons16x/print.png" alt="Print" /></a>
    <a href="#" id="cr-pdf"><img src="/ebindr/images/icons16x/pdf.gif" alt="Export to PDF" /></a>
    <a href="#" id="cr-xml"><img src="/ebindr/images/icons16x/xml.png" alt="XML" /></a>
    <a href="#" id="cr-xls"><img src="/ebindr/images/icons16x/excel.png" alt="XLS" /></a>
    <a href="#" id="cr-xlsnew"><img src="/ebindr/images/icons16x/excelnew.png" alt="XLSX" /></a>
    <a href="#" id="cr-ggl"><img src="/ebindr/images/icons16x/google-apps.png" alt="Google Apps" /></a>
    <a href="#" id="cr-rtf"><img src="/ebindr/images/icons16x/word.png" alt="RTF" /></a>
    <a href="#" id="cr-txt"><img src="/ebindr/images/icons16x/text.png" alt="TXT" /></a>
    <a href="#" id="cr-refresh"><img src="/ebindr/images/icons16x/refresh.png" alt="Refresh" /></a>
    <a href="#" id="cr-help" title="<[current_query]>"><img src="/ebindr/images/icons16x/help.png" alt="Help" /></a>
    <a href="#" id="cr-email" title="Email this report" lang="<[current_query]>"><img src="/ebindr/images/icons16x/email.png" alt="Email this report" /><span style="display:none;"><[USED_PARAMETERS]></span></a>
    <a href="#" id="cr-favorites-add" title="<[current_query]>"><img src="/ebindr/images/icons16x/favorites_add.png" alt="Add to Favorites" /></a>
    <a href="#" id="cr-resize-left" title="Resize: Fit Left Page"><img src="/ebindr/images/icons16x/resize-left.png" alt="Resize: Fit Left Page" /></a>
    <a href="#" id="cr-resize-center" title="Resize: Center"><img src="/ebindr/images/icons16x/resize-middle.png" alt="Resize: Center" /></a>
    <a href="#" id="cr-resize-right" title="Resize: Fit Right Page"><img src="/ebindr/images/icons16x/resize-right.png" alt="Resize: Fit Right Page" /></a>
    <a href="#" style="color:red;line-height:normal" title="This report is a common report but has been customized in some way for your BBB"><[customcommon_message]></a>
    <!-- input type=button id=backrptbtn value="<< Back" style="margin-top:5px;" onclick="$$('div.breadcrumbs div')[$$('div.breadcrumbs div').length-2].getElement('a').click();" -->
    <div style="clear: both;"></div>
</div>
<div id="cr-email-list" style="display:none;">
    <div class="top"></div>
    <div class="list">
        <ul>
            <li>One-time</li>
            <li>Daily</li>
            <li>Weekly</li>
            <li>Monthly</li>
            <li>Yearly</li>
        </ul>
        <div style="clear: both;"></div>
    </div>
    <div class="bottom"></div>
</div>
<[heirarchy]>

<div class="container-fluid">
    <div class="row-fluid">
        <div>
            <[content]>
            <? //include MOBILE_TEMPLATE_URI . 'components/crm/sendemail.php'; ?>
        </div>
    </div>
</div>


<div id="href_dialog" title="Warning">
    <p>You are about to claim a batch of leads.</p>
    <p>Are you sure you want to continue?</p>
</div>

<script src="/ebindr/views/crm/js/js.cookie.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="/ebindr/views/crm/js/bootstrap-tabs-x.min.js" type="text/javascript"></script>
<script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js" type="text/javascript"></script>

<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.1/js/dataTables.fixedHeader.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(e){
        var data_table = $('#mergecode').DataTable({
            "paging": false,
            fixedHeader: true,
            "order": []
        });

        var dialog_href = "";

        $("#href_dialog").dialog({
            autoOpen: false,
            resizable: false,
            height:250,
            modal: true,
            buttons: {
                "Yes": function() {
                    $( this ).dialog("close");
                    window.location.href = dialog_href;
                },
                "No": function() {
                    $( this ).dialog("close");
                }
            }
        });
    });
</script>
</body>
</html>