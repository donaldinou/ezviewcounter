/**
 * 
 */
(function( $ ) {
	
	/**
	 * 
	 */
	jQuery(document).ready(function($) {
		$('span.counter').each( function(){
			var args = $(this).attr('id').split('_');
			jQuery.ez( 'ezviewcounter::count::' + args[1] + '::' + args[2], {}, _callBack );
	        return false;
		});
	});
	
	/**
	 * 
	 */
	function _callBack( data ) {
		if ( data && data.content !== '' ) {
			var id = +data.content.id;
			var update = +data.content.update;
			var count = +data.content.count;
			$('#ezviewcounter_' + id + '_' + update).text( count );
		} else {
			alert( data.content.error_text );
		}
	}
})(jQuery);