<div class="nav-collapse">
    <ul class="nav">
        <? foreach( $__main_menu['ul'] as $li => $value ): ?>
        <? list(,$class) = explode( ':', $value ); ?>
            <li class="<?=$class?><?=( ( $page == strtolower( $li ) ) ? ' active' : '' )?>">

                <? if( is_array( $value ) ): ?>
                    <a alt="<?=$li?>" class="dropdown-toggle" href="javascript:void(0);" data-toggle="dropdown"><?=$li?> <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <? foreach( $value as $_li => $_value ): ?>
                                <? list($_v,$_class) = explode( ':', $_value ); ?>
                                    <li class="<?=$_class?>"><a alt="<?=$_li?>" href="<?=$_v?>"><?=$_li?></a></li>
                            <? endforeach; ?>
                        </ul>
                <? else: ?>
                    <a alt="<?=$li?>" href="<?=$value?>"><?=$li?></a>
                <? endif; ?>

            </li>
        <? endforeach; ?>
    </ul>
</div><!--/.nav-collapse -->

