(function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.expiry.info' );

	bs.expiry.info.ExpiryInformationPage = function ExpiryInformationPage( name, config ) {
		this.expiryGrid = null;
		bs.expiry.info.ExpiryInformationPage.super.call( this, name, config );
	};

	OO.inheritClass( bs.expiry.info.ExpiryInformationPage, StandardDialogs.ui.BasePage );

	bs.expiry.info.ExpiryInformationPage.prototype.setupOutlineItem = function () {
		bs.expiry.info.ExpiryInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

		if ( this.outlineItem ) {
			this.outlineItem.setLabel( mw.message( 'bs-expiry-info-dialog' ).plain() );
		}
	};

	bs.expiry.info.ExpiryInformationPage.prototype.setup = function () {
		return;
	};

	bs.expiry.info.ExpiryInformationPage.prototype.onInfoPanelSelect = function () {
		var me = this;
		if ( me.expiryGrid === null ) {
			bs.api.store.getData( 'expiry' ).done( function ( data ) {
				var message = me.getExpiryMessage( data.results[0] );
				me.expiryLabel = new OO.ui.LabelWidget( {
					label: message
				} );
				me.$element.append( me.expiryLabel.$element );
				if ( data.results[0] && data.results[0].exp_comment.length > 0 ) {
					me.expiryCommentLabel = new OO.ui.LabelWidget( {
						label: mw.message(
							'bs-expiry-info-dialog-comment',
							data.results[0].exp_comment
						).plain()
					} );
					me.$element.append( me.expiryCommentLabel.$element );
				}
				me.specialPageButton = new OO.ui.ButtonWidget( {
					label: mw.message( 'bs-expiry-info-dialog-button-label' ).plain(),
					href: mw.util.getUrl( "Special:Expiry" )
				} );
				var buttonFieldLayout = new OO.ui.FieldLayout( me.specialPageButton );
				me.$element.append( buttonFieldLayout.$element );
			} );
		}
	}

	bs.expiry.info.ExpiryInformationPage.prototype.getExpiryMessage = function ( expiryData ) {
		if ( !expiryData ) {
			return mw.message( 'bs-expiry-info-dialog-expiry-not-set' ).plain();
		}
		var daysRemaining = this.getRemainingDays( expiryData );
		if ( daysRemaining <= 0 ) {
			return mw.message( 'bs-expiry-info-dialog-expired' ).plain();
		}
		return mw.message(
			'bs-expiry-info-dialog-current',
			daysRemaining,
			expiryData.expiry_date
		).parse();
	};

	bs.expiry.info.ExpiryInformationPage.prototype.getRemainingDays = function ( expiryData ) {
		var today = new Date();
		var remDate = new Date( expiryData.expiry_date );
		var msTime = remDate.getTime() - today.getTime();
		var daysRemaining = Math.ceil( msTime / ( 1000 * 60 * 60 * 24 ) );
		return daysRemaining;
	};

	registryPageInformation.register( 'expiry_infos', bs.expiry.info.ExpiryInformationPage );

})( mediaWiki, jQuery, blueSpice );
