<? //echo '<pre>'.print_r( $businesses, true ).'</pre>'; ?>
<table class="table table-bordered table-hover table-striped">
    <tbody>
    <? if ( sizeof($businesses) > 0 ):
        foreach ($businesses as $business):
            if( ! ( isset( $business->bid ) || isset( $business->BID ) ) )
                continue;

            if( isset( $business->Name ) )
                $business->name = $business->Name;
            ?>

            <!-- Results -->
            <tr>
                <td>
                    <address>
                        <? if( isset( $business->bid ) ): ?>
                        <h3>Business ID: <a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->bid ?></a></h3>
                        <? endif; ?>

                        <? if( isset( $business->CID ) ): ?>
                            <h3>Business ID: <a href="/m/searchlink.html?bid=<?= $business->BID ?>"><?= $business->BID ?></a></h3>
                            <h4>Complaint ID: <a href="/m/searchlink.html?bid=<?= $business->BID ?>&cid=<?=$business->CID?>"><?= $business->CID ?></a></h4>
                        <? endif; ?>

                        <h3><a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->name ?></a> <div class="rating"><h3><?= $business->Letter ?></h3></div></h3>

                        <? if ( isset( $business->member ) && $business->member == 'y'): ?>
                            <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                        <? endif; ?>

                        <? if( isset( $business->city ) ): ?>
                        <br />
                        <?= ($business->street1) . ', ' . ($business->county) ?><br>
                        <?= $business->city ?>, <?= $business->stateProv ?> <?= $business->postalCode ?><br>
                        <? endif; ?>

                        <? if( isset( $business->employee ) ): ?>
                            <br />
                            <p>
                                <abbr title="Employee Name">Employee Name: </abbr><b><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->employee ?> </a></b><br/>

                                <? if ($business->Title != '' && $business->Title != null): ?>
                                    <abbr title="Employee Title">Title: </abbr><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->Title ?> </a><br />
                                <? endif; ?>

                                <? if ($business->Label != '' && $business->Label != null): ?>
                                    <abbr title="Label">Label: </abbr><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->Label ?> </a><br />
                                <? endif; ?>
                            </p>
                        <? endif; ?>
                        <br />

                        <p>
                            <? if( isset( $business->CID ) ): ?>
                            Cosumer Name: <strong><?= ($business->firstname) . ' ' . ($business->lastname) ?></strong></p>
                            <? endif; ?>

                        <? if ( isset( $business->number ) && strlen(trim($business->number)) > 1): ?>
                            <abbr title="Phone Number">Phone: </abbr><a href="tel:<?= remove_phone_format($business->number) ?>"><?= $business->number ?></a><br/>
                        <? endif; ?>

                        <? if ( isset( $business->email ) && $business->email != '' && $business->email != null): ?>
                            <abbr title="Email Address">Email: </abbr><a href="mailto:<?= $business->email ?>"><?= $business->email ?></a>
                        <? endif; ?>

                        <? if ( isset( $business->url ) && $business->url != '' && $business->url != null): ?>
                            <abbr title="Web Address">URL: </abbr><a href="<?= $business->url ?>"><?= $business->url ?></a>
                        <? endif; ?>
                        </p>
                    </address>
                </td>
            </tr>
        <? endforeach; ?>
    <? else: ?>
        <tr>
            <td>
                <h4>No Results Found</h4>
            </td>
        </tr>
    <? endif; ?>
    </tbody>
</table>