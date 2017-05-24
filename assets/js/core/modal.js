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
        this.modalElement = $( element );
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
        return this;
    },

    content : function( $content )
    {
        this.modalElement.find( '.modal-content' ).html( $content );
        return this;
    },

    open : function( callback )
    {
        this.modalElement.openModal(
            {
                dismissible: false
            }
        );

        if( callback )
            callback();
    },

    close : function()
    {
        this.modalElement.closeModal();
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