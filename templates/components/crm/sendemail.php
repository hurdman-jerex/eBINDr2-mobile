<?
$tmp_sendemail = $crm->toarray( $crm->query( "select setup(5701) as `mailcode`" ) );
$tmp_sendemail = $tmp_sendemail[0];
$tmp_attachment = $crm->toarray( $crm->query( "select setup(5707) as `attachment`" ) );
$tmp_attachment = $tmp_attachment[0];
$tmp_attachment = $tmp_attachment['attachment'];

$tmp_bbbid = $crm->toarray( $crm->query( "select setup(318) as `option`" ) );
$tmp_bbbid = $tmp_bbbid[0];
$tmp_bbbid = $tmp_bbbid['option'];

$mailcodes = explode(',', str_replace(' ', '', $tmp_sendemail['mailcode']));
$staff = $crm->toarray( $crm->query( "select username, title, email from staff where initials = '" . $_COOKIE['reportr_username'] . "'" ) );
$staff = $staff[0];

//dd( $staff );

$mail_array = array();
foreach ($mailcodes as $mailcode) {
    $tmp_mail = $crm->toarray( $crm->query( "select description, replyto, subject from memberaction where code = '" . $mailcode . "'") );
    $mail_array[$mailcode] = $tmp_mail[0];
}

$emails = $crm->toarray( $crm->query( "select email from emailaddress where returned = 'n' and bid = '" . $_SESSION['currentBID'] . "'" ) );
?>

<div style="float: left; width: 30%;">
    <h5>Call Results: Sent Email</h5>
    <h5>Email Template</h5>
    <select id="mail_code" name="code" style="width: 100%;">
        <option value="" selected>Please select an Email Template</option>
        <? foreach ($mailcodes as $mailcode) { ?>
            <option value="<?=$mailcode;?>" replyto="<?=$mail_array[$mailcode]['replyto'];?>" subject="<?=$mail_array[$mailcode]['subject'];?>">
                <?=$mail_array[$mailcode]['description'];?>
            </option>
        <? } ?>
    </select>
    <h5 style="margin-top: 25px; padding-top: 5px;">Send To Email Address</h5>
    <select id="mail_add" name="mail_list" style="width: 100%;">
        <option value="" selected>Please select or manually Add Email</option>
        <? foreach ($emails as $email) { ?>
            <option value="<?=$email['email'];?>"><?=$email['email'];?></option>
        <? } ?>
    </select>
    <input id="mail_recipient" type="text" name="recipient" style="width: 100%;">
    <? if (strlen($staff['email']) > 0) { ?>
        bcc: <?=$staff['email'];?> <input type="checkbox" id="enable_bcc"/>
    <? } ?>
    <h5 style="margin-top: 25px; padding-top: 5px;">Subject</h5>
    <input id="mail_subject" type="text" name="subject" style="width: 100%;">
    <h5 style="margin-top: 25px; padding-top: 5px;">Comment</h5>
    <textarea id="mail_body" name="Comment" rows="7" style="width: 100%;" placeholder="Enter your message here, then click on the preview to view it there"></textarea>
	<!-- Disabled -->
    <!--<span id="due_fields"><br><br>
		<input id="mail_dues" type="text" name="subject" style="margin-right: 10px;" placeholder="Dues">
		<input id="mail_paydate" class="pickerdate" type="text" name="subject" style="" placeholder="Payment Date">
	</span>-->

    <!-- Call Again Input -->
    <h5>Call Again</h5>
    <input id="mail_paydate" class="pickerdate" type="text" name="CallAgain" placeholder="Choose Date">
    <br />
    <br />

    <? if ( strtolower(substr($tmp_attachment, 0, 1)) == 'y' && false ) { ?>
        <form id="fileform" enctype="multipart/form-data" method="post" action="">
            <h5 style="margin-top: 25px; padding-top: 5px;">Include Attachment:</h5>
            <input type='file' id="attachment1" name='attachment1'><br>
            <input type='file' id="attachment2" name='attachment2'><br>
            <input type='file' id="attachment3" name='attachment3'><br>
            <span>Uploaded files cannot exceed 512 MB in size</span><br>
        </form>
    <? } ?>
    <!--<button id="send_email">Send Email</button>-->
    <div style="display: block;">
        <input name="back" value=" Back" onclick="var myloc=window.location;history.go(-1);if(myloc==window.location) history.go(-1);" class="btn" disabled="" type="button">
        <button id="send_email" class="btn btn-primary">Send Email</button>
    </div>
</div>
<div style="float: left; width: 65%; margin-left: 20px;">
    <h5>Preview</h5>
    <iframe id="sales_container" style="border-radius: 10px; border-color: #E6E1DD; border-style: solid; border-size: 1px; padding-top: 20px; padding-bottom: 20px; width: 100%;">
    </iframe>
</div>
<? if ( strtolower(substr($tmp_attachment, 0, 1)) == 'y' && false ) { ?>
    <div id="attachment_dialog" title="Notification">
        <p>Uploading Files</p>
    </div>
<? } ?>
<div id="email_dialog" title="Notification">
    <p>Email Sent</p>
    <button id="close_remail">Close</button>
</div>
<div id="error_dialog" title="Warning">
    <p>Required field(s):</p>
    <p id="error_container"></p>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        var sender = null;
        var default_subject = null;
        var iframe = document.getElementById('sales_container'), iframedoc = iframe.contentDocument || iframe.contentWindow.document;

        /*$('#mail_paydate').datepicker({ beforeShowDay: function(td) {
            if (td.getDate() == 5 || td.getDate() == 20) {
                console.log('test ' + td.getMonth() + ' ' + td.getDate());
                return true;
            } else {
                console.log('test2 ' + td.getMonth() + ' ' + td.getDate());
                return false;
            }
        }});*/

        $('#mail_paydate').datepicker();

        $('#sales_container').on('load', function() {
            var current_content = $("#sales_container").contents().find("p").eq(0).html();
            if (current_content != undefined) {
                var tmp_text = $('#mail_body').val().replace(/\r?\n/g, '<br/>');
                var current_content = current_content.replace("Personal Message:", "<span id='personal_message'>" + tmp_text + "</span>");
                $("#sales_container").contents().find("p").eq(0).html(current_content);
            }

            <? if ( strtolower(substr($tmp_attachment, 0, 1)) == 'y' ) { ?>
            var tmp_content = $('#sales_container').contents().find("#attachment_links").eq(0).html();
            if (tmp_content != undefined) {
                $('#fileform').show();
                var attachment_links = "";
                $.each($(':file'), function(fi, fv) {
                    if ($(fv)[0].files[0] != undefined) {
                        attachment_links = attachment_links + '<a id="attachment' + (fi + 1) + '" href="http://s3.amazonaws.com/hurdman-files/<?=$tmp_bbbid;?>/<?=$_SESSION['currentBID'];?>/' + encodeURIComponent($(fv)[0].files[0].name) + '">' + $(fv)[0].files[0].name + '</a><br>';
                    }
                });
                $('#sales_container').contents().find("#attachment_links").eq(0).html(attachment_links);
            } else {
                // $('#fileform')[0].reset();
                $('#fileform').hide();
            }
            <? } ?>
            var due_content = $('#sales_container').contents().find("#dues_section").eq(0).html();
            if (due_content != undefined) {
                $('#due_fields').show();
                $('#sales_container').contents().find("#dues_section").eq(0).html($('#mail_dues').val());

                var payday_array = $('#mail_paydate').val().split("/");
                var due_day = 5;

                if (payday_array.length > 1) {
                    due_day = parseInt(payday_array[1]);
                }
                var due_ord = "th";
                // switch (due_day % 10) {
                //      case 1:  due_ord = "st"; break;
                //      case 2:  due_ord = "nd"; break;
                //      case 3:  due_ord = "rd"; break;
                //      default: due_ord = "th"; break;
                //   }

                $('#sales_container').contents().find("#date_section").eq(0).html(due_day + due_ord);
            } else {
                $('#due_fields').hide();
            }
        });

        /*$('#mail_dues').change( function() {
            var due_content = $('#sales_container').contents().find("#dues_section").eq(0).html();
            if (due_content != undefined) {
                $('#sales_container').contents().find("#dues_section").eq(0).html($('#mail_dues').val());
            }
        });

        $('#mail_paydate').change( function() {
            var paydate_content = $('#sales_container').contents().find("#date_section").eq(0).html();
            if (paydate_content != undefined) {
                var payday_array = $('#mail_paydate').val().split("/");
                var due_day = 1;

                if (payday_array.length > 1) {
                    due_day = parseInt(payday_array[1]);
                }
                switch (due_day % 10) {
                    case 1:  due_ord = "st"; break;
                    case 2:  due_ord = "nd"; break;
                    case 3:  due_ord = "rd"; break;
                    default: due_ord = "th"; break;
                }

                $('#sales_container').contents().find("#date_section").eq(0).html(due_day + due_ord);
            }
        });*/

        $('#mail_code').change( function() {
            var tmp_sub = $('option:selected', this).attr('subject');
            sender = $('option:selected', this).attr('replyto');
            $('#mail_subject').val(tmp_sub);
            default_subject = tmp_sub;
            var tmp_action = $(this).val();
            // iframedoc.body.innerHTML = data;
            var url = '/report/merge/BLANK.htm/?ebindr2=y&type=m&nojava&action=' + tmp_action + '&bid=<?=$_SESSION['currentBID'];?>';
            $('#sales_container').attr('src',url);
            console.log( iframedoc.body.scrollHeight );
            $("#sales_container").height( $( window ).height() - 150 );
        });

        $('#mail_add').change( function() {
            if ($(this).val().trim() != '') {
                var temp_email = $(this).val();
                $('#mail_recipient').val(temp_email);
            }
        });

        $('#mail_body').change( function() {
            if ($("#sales_container").contents().find("#personal_message").length > 0) {
                var tmp_text = $('#mail_body').val().replace(/\r?\n/g, '<br/>');
                $("#sales_container").contents().find("#personal_message").html(tmp_text);
            } else {
                var current_content = $("#sales_container").contents().find("p").eq(0).html();
                var tmp_text = $('#mail_body').val().replace(/\r?\n/g, '<br/>');
                var current_content = current_content.replace("Personal Message:", "<span id='personal_message'>" + tmp_text + "</span>");
                $("#sales_container").contents().find("p").eq(0).html(current_content);
            }
        });

        /*$(':file').change(function(){
            <? if ( strtolower(substr($tmp_attachment, 0, 1)) == 'y' ) { ?>
            var tmp_content = $('#sales_container').contents().find("#attachment_links").eq(0).html();
            if (tmp_content != undefined) {
                var attachment_links = "";
                $.each($(':file'), function(fi, fv) {
                    if ($(fv)[0].files[0] != undefined) {
                        attachment_links = attachment_links + '<a id="attachment' + (fi + 1) + '" href="http://s3.amazonaws.com/hurdman-files/<?=$tmp_bbbid;?>/<?=$_SESSION['currentBID'];?>/' + encodeURIComponent($(fv)[0].files[0].name) + '">' + $(fv)[0].files[0].name + '</a><br>';
                    }
                });
                $('#sales_container').contents().find("#attachment_links").eq(0).html(attachment_links);
            }
            <? } ?>
        });*/

        $('#send_email').click(function() {
            var error_message = "";
            if ($('#mail_code').val().length < 1) error_message += " Email<br>";
            if ($('#mail_recipient').val().length < 1) error_message += " Send to<br>";
            if ($('#mail_subject').val().length < 1) error_message += " Subject<br>";

            <? if ( strtolower(substr($tmp_attachment, 0, 1)) == 'y' && false ) { ?>
            var tmp_content = $('#sales_container').contents().find("#attachment_links").eq(0).html();
            if (tmp_content == undefined) {
                $('#fileform')[0].reset();
                $('#fileform').hide();
            }
            var totalSize = 0;
            if ($('#attachment1')[0].files[0] != undefined) {
                totalSize += $('#attachment1')[0].files[0].size;
            }
            if ($('#attachment2')[0].files[0] != undefined) {
                totalSize += $('#attachment2')[0].files[0].size;
            }
            if ($('#attachment3')[0].files[0] != undefined) {
                totalSize += $('#attachment3')[0].files[0].size;
            }
            var totalSize = (totalSize / (1024*1024));
            if (totalSize > 512) error_message += "File Size exceeds limit of 512 MB";
            <? } ?>

            if (error_message.length > 0) {
                $('#error_container').html(error_message);
                $("#error_dialog").dialog("open");
                return;
            } else {
                $('#error_container').html(error_message);
            }

            if ($("#sales_container").contents().find("#personal_message").length < 1) {
                var current_content = $("#sales_container").contents().find("p").eq(0).html();
                var current_content = current_content.replace("Personal Message:", "<span id='personal_message'></span>");
                $("#sales_container").contents().find("p").eq(0).html(current_content);
            }

            var recipient = $('#mail_recipient').val();
            var subject = $('#mail_subject').val();
            var message = '<html>' + $('#sales_container').contents().find("html").html() + '</html>';
            var bcc = '';

            <? if (strlen($staff['email']) > 0) : ?>
            if ($('#enable_bcc').is(':checked')) bcc = '<?=$staff['email'];?>';
            <? endif; ?>

            $.post('/ebindr/views/crm/index.php/function/email', {
                bid: '<?=$_SESSION['currentBID'];?>',
                staff: '<?=$_COOKIE['reportr_username'];?>',
                sendto: recipient,
                subject: subject,
                message: message,
                replyto: <?=(strlen($staff['email']) > 0 ? "'" . $staff['email'] . "'" : "sender");?>,
                sender: <?=(strlen($staff['email']) > 0 ? "'" . $staff['email'] . "'" : "sender");?>,
                bcc: bcc
            }, function(data) {
                var tmp_subject = default_subject + '\r\n\r\n' + $('#mail_body').val();
                $.post('/ebindr/views/crm/index.php/api', {
                    mergecode: 'salescrm.lead email',
                    staff: '<?=$_COOKIE['reportr_username'];?>',
                    bid: '<?=$_SESSION['currentBID'];?>',
                    subject: tmp_subject,
                    code: $('#mail_code').val()
                }, function(data) {
                    <? if ( strtolower(substr($tmp_attachment, 0, 1)) == 'y' ) { ?>
                    $("#attachment_dialog").dialog("open");
                    var formData = new FormData($('#fileform')[0]);

                    $.ajax({
                        url: '/ebindr/views/crm/index.php/function/upload/<?=$tmp_bbbid;?>',
                        type: 'POST',
                        xhr: function() {
                            var myXhr = $.ajaxSettings.xhr();
                            if(myXhr.upload){
                                myXhr.upload.addEventListener('progress',function() {}, false);
                            }
                            return myXhr;
                        },
                        success: function(data) {
                            console.log(data);
                            $("#attachment_dialog").dialog("close");
                            $("#email_dialog").dialog("open");
                        },
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    <? } else { ?>
                    $("#email_dialog").dialog("open");
                    <? } ?>
                });
            });
        });

        $("#attachment_dialog").dialog({
            autoOpen: false,
            resizable: false,
            height: 150,
            modal: true
        });

        $("#email_dialog").dialog({
            autoOpen: false,
            resizable: false,
            height: 150,
            modal: true
        });

        $("#error_dialog").dialog({
            autoOpen: false,
            resizable: false,
            height:250,
            modal: true
        });

        $('#email_dialog').on('dialogclose', function(event) {
            window.parent.ebindr.window.library.Windows.instances.get("salescrm_email").close();
        });

        $('#close_remail').click( function() {
            window.parent.ebindr.window.library.Windows.instances.get("salescrm_email").close();
        });

        <? if ( strtolower(substr($tmp_attachment, 0, 1)) == 'y' ) { ?>
        $('#fileform').show();
        <? } ?>
    });
</script>