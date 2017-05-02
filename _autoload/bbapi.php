<?php

if( !class_exists('bbapi') ) {
class bbapi {
	
	public $params = array(); // the parmaeters to be submitted with the request

	static public $autoinitialize = TRUE;
    
    protected $rootUrl;

	public function __construct() {
        // Set Root URL
        $this->rootUrl = $this->getProtocol() . $_SERVER['SERVER_NAME'];
    }
    
    public function getProtocol(){
        return (String) ( ( $_SERVER[HTTPS]=='on' ) ? 'https://' : 'http://' );
    }
    
    public function getRootUrl()
    {
        if ( strpos($_SERVER['SERVER_NAME'], 'mbc-web') > -1 || strpos($_SERVER['SERVER_NAME'], 'vancouver') > -1 ){
            $url = str_replace( $_SERVER['SERVER_NAME'], "mbc-web.app.bbb.org", $this->rootUrl );
            $url = str_replace( "http://", "https://", $url );
            return $url;
        }
            
        return $this->rootUrl;
    }
    
    public function serializeUrl( $url )
    {
        if ( strpos($_SERVER['SERVER_NAME'], 'mbc-web') > -1 || strpos($_SERVER['SERVER_NAME'], 'vancouver') > -1 ){
            $url = str_replace( $_SERVER['SERVER_NAME'], "mbc-web.app.bbb.org", $url );
            $url = str_replace( "http://", "https://", $url );
            return $url;
        }
        
        return $url;
    }
	
	public function returnAuto() { return $autoinitialize; }
	/*
	 * Set parameters for the request
	 */
	public function set( $key, $value = null ) {
		if( is_array($key) && is_null($value) ) {
			if( sizeof($key) > 0 ) foreach($key as $k => $v) $this->set( $k, $v );
		} else {
			$this->params[urlencode($key)] = urlencode($value);
		}
	}
	
	public function getParams( $url = '' ) {
		$vars = '';
		if( sizeof( $this->params ) > 0 ) {
			foreach( $this->params as $key => $value ) {
				$vars .= $key . "=" . $value . "&";
			}
		}
		
		return ( $url . ( ( strstr( $url, '?' ) && strlen($url) > 0 ) ? '' : '?' ) . rtrim( $vars, "& " ) );
	}
	
	public function get( $url, $params = array(), $file = __FILE__, $line = __LINE__ ) {
		$this->set($params);
        //echo $this->getParams( $this->serializeUrl( $url ) );
		//return file_get_contents(  );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getParams( $this->serializeUrl( $url ) ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "|" . _IP);
        curl_setopt($ch, CURLOPT_USERAGENT, 'hurdman-internal ' . str_replace("/home/bbb_local/cms_portal/", "", $file) . ' (' . $line . ') ' . session_id());
        //curl_setopt($ch, CURLOPT_COOKIE, session_name().'='.$_COOKIE[session_name()].'; path=/');

        $raw = curl_exec($ch);
        curl_close($ch);

        //echo '<pre>'.print_r( $_COOKIE, true ).'</pre>';

        return $raw;

	}
	
	public function post( $url, $params = array() ) {
		$this->set($params);
		$_parm = $this->getParams();
        
        $url = $this->serializeUrl( $url );
        
		$this->_url = $url . $_parm;
		$curl = curl_init($url);
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_POST, 1 );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, str_replace( '?', '', $_parm ) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        
		$temp = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);
        
		return $temp;
	}
    
    public function auth( $params = array() )
    {
        global $cookies;
        $this->set($params);
        $_parm = $this->getParams();

        $url = $this->getRootUrl() . '/report/auth.login?ebindr2=y';
        
        $this->_url = $url . $_parm;
        $curl = curl_init($url);
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $curl, CURLOPT_POST, 1 );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, str_replace( '?', '', $_parm ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_VERBOSE, 1);
        curl_setopt( $curl, CURLOPT_HEADER, 1);
        curl_setopt( $curl, CURLOPT_HEADERFUNCTION, array($this, 'curlResponseHeaderCallback'));
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Expect:'));
        
        $response = curl_exec( $curl );
        
        // Then, after your curl_exec call:
        /*$header_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);*/
        $error = curl_error($curl);
        
        /*list($header, $body) = explode("\r\n\r\n", $response, 2);
        echo '<pre>Header: '.print_r( $header, true ).'</pre>';
        echo '<pre>Body: '.print_r( $body, true ).'</pre>';*/
        curl_close($curl);
        
        return $cookies;
    }
    
    private function curlResponseHeaderCallback($ch, $headerLine) {
        global $cookies;
        if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1){
            list( $key, $value) = explode( '=', $cookie[1] );
            $cookies[$key] = $value;
        }
        return strlen($headerLine); // Needed by curl
    }

}
}

$bbapi = new bbapi();

?>
