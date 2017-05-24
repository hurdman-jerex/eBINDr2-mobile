<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse" id="ebindr-mobile-navbar-collapse">
    <ul class="nav navbar-nav">
        <? foreach( $__main_menu['ul'] as $li => $value ): ?>
            <? list(,$class) = explode( ':', $value ); ?>
            <li class="<?=$class?><?=( ( $page == strtolower( $li ) ) ? ' active' : '' )?><?=(is_array( $value )?' dropdown': '')?>">

                <? if( is_array( $value ) ): ?>
                    <a class="dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" alt="<?=$li?>"><?=$li?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <? foreach( $value as $_li => $_value ): ?>
                            <? list($_v,$_class) = explode( ':', $_value ); ?>
                            <li class="<?=$_class?>"><a alt="<?=$_li?>" href="<?=$___ebindr2mobile_http[ 'url' ].$_v?>"><?=$_li?></a></li>
                        <? endforeach; ?>
                    </ul>
                <? else: ?>
                    <a alt="<?=$li?>" href="<?=$___ebindr2mobile_http[ 'url' ].$value?>"><?=$li?></a>
                <? endif; ?>

            </li>
        <? endforeach; ?>
    </ul>

    <div class="dropdown btn-group navbar-right" style="margin-right: 0 !important;">
        <button type="button" class="btn btn-default btn-sm navbar-btn dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-user"></i> <?=$_SESSION['username']?><b class="caret"></b></button>
        <ul class="dropdown-menu">
            <li><a href="/m/sign-out.html">Sign Out</a></li>
        </ul>
    </div>
</div><!-- /.navbar-collapse -->