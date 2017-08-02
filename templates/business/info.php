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
            <a alt="Rating" href="/m/business.html?info=rating-accreditation">
                <? if ( $__business_info['member'] == 'y' ): ?>
                    <img src="http://<?= $_SERVER['SERVER_NAME'] ?>/m/assets/img/bbb-ab3.png" alt="Accredited Business" height="31" class="ab"/>
                <? endif; ?>

                <div class="rating"><h5><?=$__business_info['button_bz-btn']?></h5></div>
            </a>
        </li>
        <li class="main-address">
            <a alt="Address" href="/m/business.html?info=address"><i class="icon-map-marker"></i>
                <?=$__business_info['button_ba']?>
            </a>
        </li>

        <li class="main-person">
            <a alt="Contact" href="/m/business.html?info=people-contacts"><i class="icon-user"></i>
                <?=$business->firstname?> <?=$business->lastname?>, <?=$business->title?>
            </a>
        </li>

        <li class="main-phone-fax">
            <a alt="Phone Fax" href="/m/business.html?info=phone-fax">
                <i class="icon-book"></i>
                <abbr title="Phone">P:</abbr> <?=strip_tags($__business_info['button_bp'])?>,
            </a>
            <a alt="Phone Fax" href="/m/business.html?info=phone-fax">
                <i class="icon-book"></i>
                <abbr title="Fax">F:</abbr> <?=strip_tags($__business_info['button_bf'])?>
            </a>
        </li>

        <li class="main-contact-email">
            <a alt="Email Address" href="/m/business.html?info=email-website"><i class="icon-envelope"></i>
                <?=$__business_info['button_b@']?>
            </a>
        </li>

        <li class="main-contact-url">
            <a alt="Website" href="/m/business.html?info=email-website"><i class="icon-home"></i>
                <?=$__business_info['button_bu']?>
            </a>
        </li>

        <li class="main-tob">
            <a alt="TOB" href="/m/business.html?info=tobs"><i class="icon-briefcase"></i>
                <?=$__business_info['button_bt']?>
            </a>
        </li>
    </ul>
</div>