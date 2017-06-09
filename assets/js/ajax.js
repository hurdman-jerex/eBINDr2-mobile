function AjaxService() {}

AjaxService.prototype = {

    handleErrorResponse : function( $error )
    {
        var $message = [];
        jQuery.each(
            $error.responseJSON,
            function( $key, $value )
            {
                switch ( $key )
                {
                    case 'status'  :
                    case 'results' :
                    case 'success' :
                        break;
                    default :
                        $message.push( $value );
                }
            }
        );

        alert( $message.join( '<br />' ) );
    },

    post : function( $url, $data, type, callback, preload ) {
        var $self = this;

        jQuery.ajax(
            {
                url    : $url,
                type: 'POST',
                dataType: 'json',
                data: $data,
                beforeSend: function() {
                    if( preload ) {
                        preload();
                    }
                },
                success: function( $json_response ) {
                    if( callback ) {
                        callback( $json_response );
                    }

                    if( $json_response.redirect )
                    {
                        window.location = $json_response.redirect;
                    }

                },
                error: function( $error ) {
                    $self.handleErrorResponse( $error );
                }
            }
        );
    },

    postHtml : function( $url, callback, preload ) {
        var $self = this;

        jQuery.ajax(
            {
                url    : $url,
                dataType: 'html',
                beforeSend: function() {
                    if( preload ) {
                        preload();
                    }
                },
                success: function( $json_response ) {
                    if( callback ) {
                        callback( $json_response );
                    }

                    if( $json_response.redirect )
                    {
                        window.location = $json_response.redirect;
                    }

                },
                error: function( $error ) {
                    $self.handleErrorResponse( $error );
                }
            }
        );
    },

    /**
     * This method is for Uploading Files ...
     * @param  {[type]}   $url     [description]
     * @param  {[type]}   $data    [description]
     * @param  {Function} callback [description]
     * @return {[type]}            [description]
     */
    postUpload : function( $form, $url, $data, callback ) {
        var $self = this;

        jQuery.ajax(
            {
                url    : $url,
                type: 'POST',
                dataType: 'json',
                data: jQuery.extend( true, new FormData( $form ), $data),
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function( $json_response ) {
                    if( callback )
                    {
                        callback( $json_response );
                    }

                    if( $response.redirect )
                    {
                        window.location = $json_response.redirect;
                    }
                },
                error: function( $error ) {
                    $self.handleErrorResponse( $error );
                }
            }
        );
    },

    get : function( $url, $data, callback, preload, errorCallback ) {
        var $self = this;

        jQuery.ajax(
            {
                url    : $url,
                type: 'GET',
                dataType: 'json',
                data: $data,
                beforeSend: function() {
                    if( preload ) {
                        preload();
                    }
                },
                success: function( $json_response ) {
                    if( callback ) {
                        callback( $json_response );
                    }

                    if( $json_response.redirect )
                    {
                        window.location = $json_response.redirect;
                    }

                },
                error: function( $error ) {
                    if( errorCallback )
                        errorCallback( $error );
                    else
                        $self.handleErrorResponse( $error );
                }
            }
        );
    },

    getHtml : function( $url, $data, callback, preload, errorCallback ) {
        var $self = this;

        jQuery.ajax(
            {
                url    : $url,
                type: 'GET',
                dataType: 'html',
                data: $data,
                beforeSend: function() {
                    if( preload ) {
                        preload();
                    }
                },
                success: function( $json_response ) {
                    if( callback ) {
                        callback( $json_response );
                    }

                    if( $json_response.redirect )
                    {
                        window.location = $json_response.redirect;
                    }

                },
                error: function( $error ) {
                    if( errorCallback )
                        errorCallback( $error );
                    else
                        $self.handleErrorResponse( $error );
                }
            }
        );
    },

    formSubmit : function( formElement, $parameters, callback ) {
        var $self = this;

        jQuery( formElement ).submit(
            function( $event )
            {
                $event.preventDefault();
                //$event.defaultPrevented;

                console.log( 'Processing Request ....' );

                var $url     = jQuery( this ).attr( 'action' );
                var $data     = jQuery( this ).serialize();

                if( $parameters )
                {
                    var $andKey = '';
                    jQuery.each( $parameters,
                        function( $key, $val )
                        {
                            if( $key != '' )
                            {
                                $andKey = $data.length == 0 ? '' : '&';
                                $data += $andKey + $key + '=' + $val;
                            }
                        }
                    );
                }

                if( ! callback )
                {
                    callback = function( $json_response )
                    {
                        alert( $json_response )
                    }
                }

                if ( ! $parameters ) {}

                $self.post( $url, $data, callback );
            }
        );
    }

};

var $http;
$http = new AjaxService();