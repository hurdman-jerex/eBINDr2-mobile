<li <? if( $page == 'Search' ) { ?> class="active" <? } ?>><a alt="Search" href="/m/search.html">Search</a></li>

<li <? if( $page == 'Info' ) { ?> class="active" <? } ?> class="dropdown">
    <a class="dropdown-toggle" href="/m/business.html" data-toggle="dropdown">Business <b class="caret"></b></a>
    <ul class="dropdown-menu">

        <? if ( e2mobile::securityCheck('/^b/', true)) { ?>
            <li><a alt="Record Information" href="/m/business.html">Record Information</a></li>
        <? } ?>

        <? if ( e2mobile::securityCheck('/^c/', true)) { ?>
            <li><a alt="Complaints" href="/m/business/complaints.html">Complaints</a></li>
        <? } ?>

        <li><a alt="Rating" href="/m/business/ratings.html">Rating</a></li>

        <? if ( e2mobile::securityCheck('bj') || e2mobile::securityCheck('b*')) { ?>
            <li><a alt="Journal" href="/m/business/journal.html">Journal</a></li>
        <? } ?>

        <? if ( e2mobile::securityCheck('bs') || e2mobile::securityCheck('b*')) { ?>
            <li><a alt="Sales" href="/m/business/sales.html">Sales</a></li>
        <? } ?>

        <? if ( e2mobile::securityCheck('bg') || e2mobile::securityCheck('b*')) { ?>
            <li><a alt="General" href="/m/business/general.html">General</a></li>
        <? } ?>

        <? if ( e2mobile::securityCheck('is') || e2mobile::securityCheck('i*')) { ?>
            <li><a alt="Snapshot" href="/m/business/snapshot.html">Snapshot</a></li>
        <? } ?>

        <li class="divider"></li>
        <li class="force_update"><a alt="Force Update" href="#">Force Update</a></li>
    </ul>
</li>

<? if ( e2mobile::securityCheck('is') || e2mobile::securityCheck('i*')) { ?>
    <li <? if( $page == 'Reports' ) { ?> class="active" <? } ?>><a alt="Reports" href="/m/reports.html">Reports</a></li>

<? } ?>

<li <? if( $page == 'abnearme' ) { ?> class="active" <? } ?>><a href="/m/abnearme.html">AB Near Me &trade;</a></li>