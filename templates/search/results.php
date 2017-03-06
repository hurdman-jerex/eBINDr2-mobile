<table class="table table-bordered table-hover table-striped">
    <tbody>
    <?
    if (sizeof($businesses) > 0 && ( $_GET['type'] != 'complaint-id' || $_GET['type'] != 'consumer-name' ) ) {
        foreach ($businesses as $business) {
            if( ! isset( $business->bid ) )
                continue;
            ?>
            <tr>
                <td>
                    <address>
                        <? if ($_GET['type'] == 'business-bid') { ?>
                            <h3>Business ID: <a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->bid ?></a></h3>
                            <h3><a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->name ?></a> <div class="rating"><h3><?= $business->Letter ?></h3></div></h3>
                            <? if ($business->member == 'y'): ?>
                                <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                            <? endif; ?>
                            <br/>
                            <?= ($business->street1) . ', ' . ($business->county) ?><br>
                            <?= $business->city ?>, <?= $business->stateProv ?> <?= $business->postalCode ?><br>
                            <br>
                            <p>
                                <? if (strlen(trim($business->number)) > 1): ?>
                                    <abbr title="Phone Number">Phone: </abbr><a href="tel:<?= $business->number ?>"><?= $business->number ?></a><br/>
                                <? endif; ?>

                                <? if ($business->email != '' && $business->email != null): ?>
                                    <abbr title="Email Address">Email: </abbr><a href="mailto:<?= $business->email ?>"><?= $business->email ?></a>
                                <? endif; ?>
                            </p>
                        <? } else if ($_GET['type'] == 'business-phone') { ?>
                            <h3>
                                <a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->name ?></a>
                                <div class="rating">
                                    <h3><?= $business->Letter ?></h3>
                                </div>

                            </h3>
                            <? if ($business->member == 'y'): ?>
                                <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                            <? endif; ?>
                            <br>
                            <?= ($business->street1) . ', ' . ($business->county) ?><br>
                            <?= $business->city ?>, <?= $business->stateProv ?> <?= $business->postalCode ?><br>
                            <br>
                            <p>
                                <? if (strlen(trim($business->number)) > 1): ?>
                                    <abbr title="Phone Number">Phone: </abbr><b><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->number ?> </a></b><br/>
                                <? endif; ?>

                                <? if ($business->email != '' && $business->email != null): ?>
                                    <abbr title="Email Address">Email: </abbr><a href="mailto:<?= $business->email ?>"><?= $business->email ?></a>
                                <? endif; ?>
                            </p>
                        <? } else if ($_GET['type'] == 'web-address') { ?>
                            <h3>
                                <a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->name ?></a>
                                <div class="rating">
                                    <h3><?= $business->Letter ?></h3>
                                </div>

                            </h3>
                            <? if ($business->member == 'y'): ?>
                                <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                            <? endif; ?>
                            <br>
                            <?= ($business->street1) . ', ' . ($business->county) ?><br>
                            <?= $business->city ?>, <?= $business->stateProv ?> <?= $business->postalCode ?><br>
                            <br>
                            <p>
                                <? if (strlen(trim($business->number)) > 1): ?>
                                    <abbr title="Phone Number">Phone: </abbr><b><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->number ?> </a></b><br/>
                                <? endif; ?>

                                <? if ($business->email != '' && $business->email != null): ?>
                                    <abbr title="Email Address">Email: </abbr><a href="mailto:<?= $business->email ?>"><?= $business->email ?></a><br />
                                <? endif; ?>

                                <? if ($business->url != '' && $business->url != null): ?>
                                    <abbr title="Web Address">URL: </abbr><a href="<?= $business->url ?>"><?= $business->url ?></a>
                                <? endif; ?>
                            </p>
                        <? }else if ($_GET['type'] == 'employee' && isset( $business->bid ) ) { ?>
                            <h3>
                                <a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->name ?></a>
                                <div class="rating">
                                    <h3><?= $business->Letter ?></h3>
                                </div>

                            </h3>
                            <? if ($business->member == 'y'): ?>
                                <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                            <? endif; ?>
                            <br>
                            <p>
                                <abbr title="Employee Name">Employee Name: </abbr><b><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->employee ?> </a></b><br/>

                                <? if ($business->Title != '' && $business->Title != null): ?>
                                    <abbr title="Employee Title">Title: </abbr><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->Title ?> </a><br />
                                <? endif; ?>

                                <? if ($business->Label != '' && $business->Label != null): ?>
                                    <abbr title="Label">Label: </abbr><a href="http:///m/searchlink.html?bid=<?= $business->bid ?>"> <?= $business->Label ?> </a><br />
                                <? endif; ?>
                            </p>
                        <? } else { ?>
                            <h3>
                                <a href="/m/searchlink.html?bid=<?= $business->bid ?>"><?= $business->name ?> </a>
                                <div class="rating"><?= $business->Letter ?></div>
                            </h3>
                            <? if ($business->member == 'y'): ?>
                                <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                            <? endif; ?>
                            <br/>
                            <?= ($business->street1) . ', ' . ($business->county) ?><br>
                            <?= $business->city ?>, <?= $business->stateProv ?> <?= $business->postalCode ?><br>
                            <br>
                            <p>
                                <? if (strlen(trim($business->number)) > 1): ?>
                                    <abbr title="Phone Number">Phone: </abbr><a href="tel:<?= remove_phone_format($business->number) ?>"><?= $business->number ?></a><br/>
                                <? endif; ?>

                                <? if ($business->email != '' && $business->email != null): ?>
                                    <abbr title="Email Address">Email: </abbr><a href="mailto:<?= $business->email ?>"><?= $business->email ?></a>
                                <? endif; ?>
                            </p>
                        <? } ?>
                    </address>
                </td>
            </tr>
            <?
        }
    }

    else if (sizeof($businesses) > 0 && ( $_GET['type'] == 'complaint-id' || $_GET['type'] == 'consumer-name' ) ) {

        foreach ($businesses as $complaint) {
            if( ! isset( $complaint->BID ) )
                continue;
            ?>
            <tr>
                <td>
                    <address>
                        <h3>Complaint ID: <a href="/m/searchlink.html?bid=<?= $complaint->BID ?>&cid=<?=$complaint->CID?>"><?= $complaint->CID ?></a></h3>
                        <br/>
                        <h3><a href="/m/searchlink.html?bid=<?= $complaint->BID ?>"><?= $complaint->Name ?></a>
                            <div class="rating">
                                <h3><?= $complaint->Letter ?></h3>
                            </div>
                        </h3>
                        <? if ($complaint->member == 'y'): ?>
                            <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                        <? endif; ?>
                        <br/>
                        <p> Business ID: <strong><?= $complaint->BID ?></strong><br/>
                            Cosumer Name: <strong><?= ($complaint->firstname) . ' ' . ($complaint->lastname) ?></strong></p>
                        <? if (strlen(trim($complaint->number)) > 1): ?>
                            <p>Phone: <a href="tel:<?= $complaint->number ?>"><?= $complaint->number ?></a></p>
                        <? endif; ?>
                    </address>
                </td>
            </tr>
        <? } ?>
    <? } else { ?>
        <tr>
            <td>
                <h4>No Results Found</h4>
            </td>
        </tr>
    <? } ?>
    </tbody>
</table>