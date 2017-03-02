<?php
  
  class crypt {
      
      public static function encrypt($plain_text, $iv_len = 16) {
           $password = 'beac5447a38f456fda840b3749444bb4';
           $plain_text .= "\x13";
           $n = strlen($plain_text);
           if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
           $i = 0;
           $enc_text = $this->get_rnd_iv($iv_len);
           $iv = substr($password ^ $enc_text, 0, 512);
           while ($i < $n) {
               $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
               $enc_text .= $block;
               $iv = substr($block . $iv, 0, 512) ^ $password;
               $i += 16;
           }
           return $this->base64UrlEncode($enc_text);
      }
      
      public static function decrypt($enc_text, $iv_len = 16) {
        $password = 'beac5447a38f456fda840b3749444bb4';
       $enc_text = $this->base64UrlDecode($enc_text);
       $n = strlen($enc_text);
       $i = $iv_len;
       $plain_text = '';
       $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
       while ($i < $n) {
           $block = substr($enc_text, $i, 16);
           $plain_text .= $block ^ pack('H*', md5($iv));
           $iv = substr($block . $iv, 0, 512) ^ $password;
           $i += 16;
       }
       return preg_replace('/\\x13\\x00*$/', '', $plain_text);
    }
      
      public function get_rnd_iv($iv_len)
            {
               $iv = '';
               while ($iv_len-- > 0) {
                   $iv .= chr(mt_rand() & 0xff);
               }
               return $iv;
            }

      public function base64UrlEncode($data)
            {
              return strtr(rtrim(base64_encode($data), '='), '+/', '-_');
            }

      public function base64UrlDecode($base64)
            {
              return base64_decode(strtr($base64, '-_', '+/'));
            }
  }
?>
