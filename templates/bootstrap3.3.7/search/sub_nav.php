<?
$mybindr->addparm( 'type', 'f' );
$mybindr->addparm( 'staff', $_COOKIE["reportr_username"] );
list($q)=$mybindr->getquery("e button sort" );
$q=$mybindr->ResolvePipes($q);
$result = mysql_db_query( LOCAL_DB, $q, $mybindr->db );
?>
<div id="findr2_contentWrapper" class="subnav subnav-fixed">
    <div id="findr2-body">
        <ul class="nav nav-pills findr-btns grad-lite-blue pull-left" style="padding-left: 15px;">
            <? $i=0; while( $row = mysql_fetch_assoc($result) ) : ?>
                <li class="<?=$row['class']?><?=($i==0? ' left' : '' )?>"><a aria-label="<?=strip_tags( $row['name'] )?>" alt="<?=strip_tags( $row['name'] )?>" href="javascript:void(0)"><?=$row['name']?></a></li>
                <? $i++; endwhile; ?>
            <li class="editorderbtn"><a aria-label="Edit Order" alt="Edit Order" href="javascript:void(0)">Edit Order</a></li>

            <?php
            if (eregi("atlanta|hurdmantest", $_SERVER['SERVER_NAME'])) //change server name to atlanta.ebindr.com
            {
                echo '<li class="arb"><a aria-label="ARM Num" alt="ARB Num" href="javascript:void(0)">ARB Num</a></li>';
            }
            ?></ul>
        <ul class="nav pull-right more-nav"><li class="more" id="more-search"><a aria-label="More Search List" alt="More Search" href="javascript:void(0)"><i class="glyphicon glyphicon-triangle-bottom"></i></a></li></ul>
        <div class="clearfix"></div>
        <div class="search-bar">
            <div id="search-by-type">Begins With *</div>
            <div class="search-type" id="search-type">
                <label id="search-type-label" for="search-q">Business Name</label>
            </div>
            <div class="form-group" style="float: left; margin-top: 4px;">
                    <input aria-label="Business Name" id="search-q" type="text" class="form-control" placeholder="Search">
            </div>
        </div>
        <div id="more-list" class="hide-more">
            <div class="top"></div>
            <div class="list">
                <ul></ul>
                <div class="clearfix"></div>
            </div>
            <div class="bottom"></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>