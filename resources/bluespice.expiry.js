(function( mw, $, d, bs, undefined ){
	$( d ).on( 'click', 'a.bs-expiry-unexpire', function() {
		var exp_id = $( this ).data( 'expid' );
		bs.api.tasks.exec(
			'expiry',
			'deleteExpiry',
			{
				expiryId: exp_id || 0,
				articleId: mw.config.get( 'wgArticleId' ) || 0
			}
		)
		.done( function(){
			window.location.reload();
		} );
	});

	$( d ).on( 'click', "#ca-expiryCreate, .ca-expiryCreate", function ( e ) {
		e.preventDefault();
		var me = this;
		mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
			Ext.onReady( function() {
				if ( !me.dlgExpiry ) {
					me.dlgExpiry = Ext.create( 'BS.Expiry.Dialog', {
						id: 'bs-expiry-dlg-page'
					} );
					me.dlgExpiry.on( 'ok', function() {
						var obj = me.dlgExpiry.getData();
						bs.api.tasks.exec(
							'expiry',
							'saveExpiry',
							obj
						);
						$( d ).trigger( "BSExpiryAddOk", [ me, obj ] );
					}, me );
				}
				var obj = {
					articleId: mw.config.get( 'wgArticleId' )
				};
				me.dlgExpiry.setData( obj );
				me.dlgExpiry.show( me );
			});
		});
	} );
})( mediaWiki, jQuery, document, blueSpice );