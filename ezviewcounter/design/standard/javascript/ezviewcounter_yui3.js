/**
 * eZ View Counter : Rating extension for eZ Publish 4.x
 * This piece of code depends on YUI 3.0 and eZJSCore ( Y.io.ez() plugin ).
 * @since
 * @copyright Copyright (c) 2012
 * @license http://ez.no/licenses/gnu_gpl GNU General Public License v2.0
 * @package ezviewcounter
 *
 */
YUI( YUI3_config ).use('node', 'event', 'io-ez', function( Y )
{
    Y.on( "domready", function( e ) {
        Y.all('span.counter').each( function( node ){
        	var args = node.get('id').split('_');
        	Y.io.ez( 'ezviewcounter::count::' + args[1] + '::' + args[2], { on : { success: _callBack } } );
        } );
    });

    function _callBack( id, o ) {
    	if ( o.responseJSON && o.responseJSON.content !== '' ) {
            var data = o.responseJSON;
            var id = +data.content.id;
			var update = +data.content.update;
			var count = +data.content.count;
			Y.all('#ezviewcounter_' + id + '_' + update).setContent( count );
        } else {
            alert( o.responseJSON.error_text );
        }
    }
});