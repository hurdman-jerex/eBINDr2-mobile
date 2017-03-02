<?php
if( ! class_exists( 'simple_html_dom' ) )
    include "/home/serv/public_html/ebindr/includes/simple_html_dom.php";
    
if( ! class_exists( 'bbapi' ) )
    include "/home/serv/public_html/m/_autoload/bbapi.php"; 

if( ! class_exists( 'mobileSnapShot' ) ){
class mobileSnapShot {

    public $dom; // the dom object to read the html
    public $raw; // the raw html

    public function __construct( simple_html_dom $dom ) {
        $this->dom = $dom;
    }
    
    public function retrieve( $bid, $staff, $url, $cleanup = null ) {
        $bbapi = new bbapi();
        //$url = $bbapi->getRootUrl() . "/report/menu.Stats.Inquiry%20by%20Bid?ebindr2=y&BYPASS=gure8wh3&bid=$bid&noheader&BYPASS2=9vfjesu3hgi&staff=$staff";
        $url = $url;
        $_tmp = file_get_contents( $url );
        
        $tidy = new Tidy();
        $tidy->parseString($_tmp, array('indent' => true, 'output-xhtml' => true));
        $tidy->cleanRepair();
         
         $_tmp = (string) $tidy;
                
         if( is_null($cleanup) || !$cleanup ) {
            return $_tmp;
         }

        return call_user_func_array( array( $this, '_' . $cleanup . '_cleanup' ), array( $_tmp ) );
    }

    private function _default_cleanup( $html ) {
        $this->dom->load( $html );
        
        // remove any input's
        $inputs = $this->dom->find( 'input' );
        if( isset($inputs[0]) && sizeof($inputs) > 0 ) {
            foreach( $inputs as $s ) {
                $s->outertext = '';
            }
        }
        
        // remove any content of prev/next tds
        $tds = $this->dom->find( 'td[id=prev], td[id=next]' );
        if( isset($tds[0]) && sizeof($tds) > 0 ) {
            foreach( $tds as $td ) {
                $td->innertext = '';
            }
        }
        
        // remove links and just leave their text
        /*$links = $this->dom->find( 'a' );
        if( isset($links[0]) && sizeof($links) > 0 ) {
            foreach( $links as $link ) {
                $link->outertext = $link->innertext;
            }
        }*/
        
        // remove the first table (basically its just a print button)
        // TODO: this doesn't work
        /*foreach( $this->dom->find( 'table#1' ) as $tbl ) {
            $tbl->outertext = '';
        }*/
        
        
        
        $this->dom->load( $this->dom->save() );
        
        // extract tables out that have an id but live inside another table
        foreach( $this->dom->find( 'table[!id]' ) as $table ) {
            if( $table->id == '' ) {
                $test = $table->first_child()->first_child()->first_child();
                if( $test->tag == 'script' ) $table->outertext = '';
                if( $test->tag == 'table' ) $table->outertext = $test->outertext;
            }
        }
        
        $this->dom->load( $this->dom->save() );
        
        // remove the blank table in the first tbody that holds the prev/next
        foreach( $this->dom->find( 'td#prev' ) as $i => $prev ) {
            $prev->parent()->parent()->parent()->outertext = '';
        }
        
        $this->dom->load( $this->dom->save() );
        
        foreach( $this->dom->find( 'table' ) as $table ) {
            $table->class = $table->class . " table table-bordered table-striped";
            $output .= $table->outertext;
        }
        
        return $output;
    }
}
}
?>
