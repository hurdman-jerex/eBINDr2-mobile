<?php
  function hurdman_query_escape($value) {

    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // Escape if not integer
    if (!is_numeric($value)) {
        $value = mysql_escape_string($value);
    }
    return $value;

    //return mysql_escape_string($value);
    $return = '';
    for($i = 0; $i < strlen($value); ++$i) {
        $char = $value[$i];
        $ord = ord($char);
        if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
            $return .= $char;
        else
            $return .= '\\x' . dechex($ord);
    }
    return $return;
}
?>
