function Modal( element )
{
    this.construct( element );
}

Modal.prototype = {

    modalElement : false,

    construct : function( element )
    {
        this.init_modal( element );
    },

    init_modal : function( element )
    {
        this.modalElement = jQuery( element );
    },

    header : function( $headerContent )
    {
        this.modalElement.find( '.modal-header' ).html( $content );
        return this;
    },

    footer : function( $footerContent )
    {
        this.modalElement.find( '.modal-footer' ).html( $content );
        return this;
    },

    wholeContent : function( $content )
    {
        this.modalElement.html( $content );
        //$ThemeJs.init_form_fields( this.modalElement );
        return this;
    },

    content : function( $content )
    {
        this.modalElement.find( '.modal-content' ).html( $content );
        //$ThemeJs.init_form_fields( this.modalElement );
        return this;
    },

    open : function( callback )
    {
        this.modalElement.modal(
            {
                dismissible: false,
                show: true
            }
        );

        if( callback )
            callback();
    },

    close : function()
    {
        this.modalElement.modal( 'hide' );
    },

    enableFixedFooter: function() {
        this.modalElement.addClass('modal-fixed-footer');
    },

    disableFixedFooter: function() {
        this.modalElement.removeClass('modal-fixed-footer');
    }
};

var $Modal;

jQuery( document ).ready(
    function()
    {
        $Modal = new Modal( '#modal-element' );
        $ModalAlert = new Modal( '#modal-alert-element' );
    }
);