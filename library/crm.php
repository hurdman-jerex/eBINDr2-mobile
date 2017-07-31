<?php

if( !class_exists( 'mybindr' ) )
    include _MYBINDR;

if( !class_exists( 'mobileCrm' ) ){
    class mobileCrm {

        protected $ebindr;

        public function __construct()
        {
            $this->setMyBindr();
        }

        public function setMyBindr(){
            // now bring in mybindr and connect to the database
            $this->ebindr=new mybindr;
            $this->ebindr->database = LOCAL_DB;
            mysql_select_db( $this->ebindr->database, $this->ebindr->db );
        }

        public function bind( $key, $value, $noescape = false ) {
            return $this->ebindr->addparm( $key, ( $noescape ? $value : mysql_real_escape_string($value) ) );
        }

        public function getMergeCode( $sql ) {
            // log that we are running this query
            $__q = mysql_real_escape_string( str_replace( array( "[", "]" ), "", $sql ) );
            // get the query
            list( $mergequeries ) = $this->ebindr->getquery( $__q );

            return $mergequeries;
        }

        public function query( $sql ) {

            $this->bind( 'staff', $_COOKIE['reportr_username'] );

            // get for a mergequery
            if( substr( $sql, -1 ) == ']' ) {

                // log that we are running this query
                $__q = mysql_real_escape_string( str_replace( array( "[", "]" ), "", $sql ) );

                $__staff = mysql_real_escape_string($_COOKIE['reportr_username']);

                mysql_query( "insert into reportlog (mergecode, day, staff, count) values ('".$__q."', now(), '".$__staff."', 1) on duplicate key update count=count+1" );
                // get the query
                list( $mergequeries ) = $this->ebindr->getquery( $__q );

                $mergequeries = explode( "||", str_replace( "\r\n", "", $mergequeries ) );
                // return $mergequeries;
                //echo "<pre>"; print_r($mergequeries); echo "</pre>";
                foreach( $mergequeries as $i => $q ) {
                    $result = mysql_query( $this->ebindr->resolvepipes($q) );

                    //$res_q = mysql_fetch_object($result );
                    //echo "<pre>"; print_r($res_q ); echo "</pre>";

                    if( ($i+1) == sizeof($mergequeries) ) return $result;
                    else unset($result);
                }
            } else {

                return mysql_query( $this->ebindr->resolvepipes($sql) );
            }
        }

        public function toarraynumeric( $result ) {
            if( isset($result) && $result && mysql_num_rows($result) < 1 ) {
                return false;
            } else {
                $dataset = array();
                while( $row = mysql_fetch_row( $result ) ) {
                    $dataset[] = $row;
                }
                return $dataset;
            }
        }

        public function toarray( $result ) {
            if( isset($result) && $result && mysql_num_rows($result) < 1 ) {
                return false;
            } else {
                $dataset = array();
                while( $row = mysql_fetch_assoc( $result ) ) {
                    $dataset[] = $row;
                }
                return $dataset;
            }
        }

        public function mergecodeToVariables( $name, $params ) {
            $get = '';
            if( sizeof($params) > 0 ) {
                foreach( $params as $k => $v ) {
                    $get .= '&' . urlencode($k) . '=' . rawurlencode($v);
                }
            }

            //echo "http://".$this->host."/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get;

            //echo "http://hurdmantest.hurdman.org/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get;
            $data = json_decode(file_get_contents("http://".$this->host."/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get));

            if( sizeof($data->resultset) == 1 ) return $data->resultset[0];
            else return $data->resultset;
        }

        public function mergecode( $name, $params = array() ) {

            $get = '';
            if( sizeof($params) > 0 ) {
                foreach( $params as $k => $v ) {
                    $get .= '&' . urlencode($k) . '=' . rawurlencode($v);
                }
            }

            foreach( $this->segments as $k => $v ) {
                $get .= '&segment'.$k.'=' . $v;
            }

            //echo "http://hurdmantest.hurdman.org/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get;
            $tmp = file_get_contents("http://".$this->host."/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get);
            $data = json_decode($tmp);
            $html = '';

            if (count($data->resultset) > 0) {
                $html = '
      <table id="mergecode" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
	        <tr>';
                foreach( $data->resultset[0] as $key => $value ) {
                    $html .= '<th>'.$key.'</th>';
                }
                $html .= '</tr>
        </thead>
        <tbody>';
                foreach( $data->resultset as $i => $row ) {
                    $html .= '<tr>';
                    foreach( $row as $k => $v ) {
                        $html .= '<td>'.$v.'</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody>
      </table>';
            }

            return $html;
        }

        public function enabled( $page ) {
            $this->bind( 'staff', $_COOKIE['reportr_username'] );
            if( !isset($page->isenabled) ) return true;
            $results = $this->toarraynumeric( $this->query( $page->isenabled ) );
            return ( $results[0][0] == 1 ? true : false );
        }

        public function email($tmparray) {

            $new_message = str_replace("\\\"", "\"", $tmparray['message']);

            $this->query( "insert into emailqueue values ( null, '" . $tmparray['bid'] . "', '" . $tmparray['staff'] . "', now(), '" . $tmparray['sendto'] . "', '" . $tmparray['subject'] . "', '" . $new_message . "', null, null, '" . $tmparray['bcc'] . "', '" . $tmparray['replyto'] . "', '" . $tmparray['sender'] . "', 'HTML' )" );

            $tmp_email = $this->toarray( $this->query( "select setup(5705) as `option`" ) );
            if ( strtolower(substr($tmp_email[0]['option'], 0, 1)) == 'y' ) {
                $emails = $this->toarray($this->query( "select * from emailaddress where bid = '" . $tmparray['bid'] . "'" ));
                $email_present = false;
                if (!isset($emails[0])) {
                    $this->query( "insert into emailaddress (BID, Email, Main, Report, eQuote, OptOut, MassEmail, Created, returned) values ('" . $tmparray['bid'] . "', '" . $tmparray['sendto'] . "', 'y', 'n', 'n', 'n', 'n', curdate(), 'n')" );
                    $this->query( "insert into changeaudit (fid, type, bid, key2, day, staff, history) values ('20', 'Insert', '" . $tmparray['bid'] . "', curdate(), now(), '" . $tmparray['staff'] . "', concat('Inserted to emailaddress from CRM with email ', '" . $tmparray['sendto'] . "', ' to bid ', '" . $tmparray['bid'] . "', ' by staff ', '" . $tmparray['staff'] . "'))" );
                } else {
                    foreach ($emails as $email) {
                        if ($email['Email'] == $tmparray['sendto']) $email_present = true;
                    }
                    if (!$email_present) {
                        $this->query( "insert into emailaddress (BID, Email, Main, Report, eQuote, OptOut, MassEmail, Created, returned) values ('" . $tmparray['bid'] . "', '" . $tmparray['sendto'] . "', 'n', 'n', 'n', 'n', 'n', curdate(), 'n')" );
                        $this->query( "insert into changeaudit (fid, type, bid, key2, day, staff, history) values ('20', 'Insert', '" . $tmparray['bid'] . "', curdate(), now(), '" . $tmparray['staff'] . "', concat('Inserted to emailaddress from CRM with email ', '" . $tmparray['sendto'] . "', ' to bid ', '" . $tmparray['bid'] . "', ' by staff ', '" . $tmparray['staff'] . "'))" );
                    }
                }
            }
            return;
        }

        public function upload($tmparray, $bbbid) {
            for($i=1;$i<4;$i++) {
                if($_FILES["attachment".$i][name]) {
                    $filecontent=implode('',file($_FILES["attachment".$i][tmp_name]));
                    if(!eregi("pdf$", $_FILES["attachment".$i][name])) {
                        $filecontent=str_replace("\r\n","***LINEBREAK***",$filecontent);
                        $filecontent=ereg_replace('[{][\]shprslt[^{]+[{][^{]+[{][^{}]+[}][^{}]+[}][^{}]*[}]', '', $filecontent);
                        $filecontent=ereg_replace('[{][\]nonshppic[^{]+[{][^{]+[{][^{}]+[}][^{}]+[}][^{}]*[}]', '', $filecontent);
                        $filecontent=ereg_replace('[{][\][*][\]themedata[^{}]+[}]', '', $filecontent);
                        $filecontent=ereg_replace('[{][\][*][\]panose[^{}]+[}]', '', $filecontent);
                        $filecontent=ereg_replace('[{][\]flomajor.+[}]([}][^{}]+[{][\]colortbl)', "\\1", $filecontent);
                        $filecontent=ereg_replace('[{][\][*][\]colorschememapping[^{}]+[}]', '', $filecontent);
                        $filecontent=ereg_replace('[{][\][*][\]latentstyles.+[{][\][*][\]datastore[^{}]+[}]', '', $filecontent);
                        $filecontent=str_replace("***LINEBREAK***","\r\n",$filecontent);
                    }

                    $filename = $bbbid . '/' . $_SESSION['currentBID'] . '/' . $_FILES["attachment".$i][name];
                    $accesskey = "AKIAJQWGS3GZG36P4E2A";
                    $bucket = "hurdman-files";
                    $secret = "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";
                    $policy = '{
          "expiration": "' . date("Y-m-d",strtotime("+2 day",time())) . 'T00:00:00Z' . '",
          "conditions": [
            {
              "bucket": "' . $bucket . '"
            },
            {
              "acl": "public-read"
            },
            [
              "starts-with",
              "$key",
              ""
            ],
            [
            	"starts-with",
            	"$Content-Type",
            	""
            ],
            {
              "success_action_status": "201"
            }
          ]
        }';

                    $tmpfile = '@' . $_FILES["attachment".$i][tmp_name];
                    $base64Policy = base64_encode($policy);
                    $signature = base64_encode(hash_hmac("sha1", $base64Policy, $secret, $raw_output = true));
                    $filetype = $_FILES["attachment".$i][type];

                    $post = array('key' => $filename, 'AWSAccessKeyId' => $accesskey, 'acl' => 'public-read', 'success_action_status' => '201', 'policy' => $base64Policy, 'signature' => $signature, 'Content-Type' => $filetype, 'file'=> $tmpfile);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,'https://hurdman-files.s3.amazonaws.com/');
                    curl_setopt($ch, CURLOPT_POST,1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    $result=curl_exec ($ch);
                    curl_close ($ch);
                }
            }
        }

        public function getform( $crm ){
            ob_start();
            include MOBILE_LIBRARY_URI . 'crm/sendemail';
            $contents = ob_get_contents();
            ob_end_clean();

            return $contents;
        }
    }
}
