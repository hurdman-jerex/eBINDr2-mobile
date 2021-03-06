<?
$url = "http://".$_SERVER['SERVER_NAME']."/m/api/business/info/" . $_SESSION['bid'];
$business= json_decode( e2mobile::apiGet( $url ) );
$business=$business->results;
$business=$business[0];
?>

<style type="text/css">
    .rating {
        border: 2px solid #0099cc;
        color: #0099cc;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        padding: 0 5px;
        display: inline-block;
        text-align: center;
        min-width: 20px;
        background-color: #e6e6e6;
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), to(#e6e6e6));
        background-image: -webkit-linear-gradient(top, #ffffff, #e6e6e6);
        background-image: -o-linear-gradient(top, #ffffff, #e6e6e6);
        background-image: linear-gradient(to bottom, #ffffff, #e6e6e6);
        background-image: -moz-linear-gradient(top, #ffffff, #e6e6e6);
        background-repeat: repeat-x;
    }

    .ab {
        margin: 10px 0;
    }
</style>

<? //echo '<pre>'.print_r( $business, true ).'</pre>'; ?>
<div class="nav">
    <ul class="nav nav-tabs nav-stacked">
        <li class="ratings-and-accreditation">
            <a href="/m/business.html?info=rating-accreditation">
                <? if ($business->member == 'y'): ?>
                    <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                <? endif; ?>

                <div class="rating"><h5><?=$business->Letter?></h5></div>
            </a>
        </li>
        <li class="main-address">
            <a href="/m/business.html?info=address"><i class="icon-map-marker"></i>
                <?=$business->street1?> <?=$business->street2?> <?=$business->city?>, <?=$business->stateprov?> <?=$business->postalcode?>
            </a>
        </li>

        <li class="main-person">
            <a href="/m/business.html?info=people-contacts"><i class="icon-user"></i>
                <?=$business->firstname?> <?=$business->lastname?>, <?=$business->title?>
            </a>
        </li>

        <li class="main-phone-fax">
            <a href="/m/business.html?info=phone-fax">
                <i class="icon-book"></i>
                <abbr title="Phone">P:</abbr> <?=$business->number?>,
                <abbr title="Fax">F:</abbr> <?=$business->faxnumber?>
            </a>
        </li>

        <li class="main-contact-email">
            <a href="/m/business.html?info=email-website"><i class="icon-envelope"></i>
                <?=$business->email?>
            </a>
        </li>

        <li class="main-contact-url">
            <a href="/m/business.html?info=email-website"><i class="icon-home"></i>
                <?=$business->url?>
            </a>
        </li>

        <li class="main-tob">
            <a href="/m/business.html?info=tobs"><i class="icon-briefcase"></i>
                <?=$business->TOB?>
            </a>
        </li>
    </ul>
</div>