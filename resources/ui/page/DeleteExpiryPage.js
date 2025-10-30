bs.expiry.ui.DeleteExpiryPage = function ( cfg ) {
	bs.expiry.ui.DeleteExpiryPage.parent.call( this, 'delete-expiry', cfg );
	this.reminderEnabled = false;
};

OO.inheritClass( bs.expiry.ui.DeleteExpiryPage, OOJSPlus.ui.booklet.DialogBookletPage );

bs.expiry.ui.DeleteExpiryPage.prototype.getItems = function () {
	return [
		new OO.ui.LabelWidget( {
			label: mw.message( 'bs-expiry-dialog-delete-prompt' ).text()
		} )
	];
};

bs.expiry.ui.DeleteExpiryPage.prototype.getTitle = function () {
	return mw.message( 'bs-expiry-dialog-title' ).text();
};

bs.expiry.ui.DeleteExpiryPage.prototype.getSize = function () {
	return 'medium';
};

bs.expiry.ui.DeleteExpiryPage.prototype.setData = function ( data ) {
	this.id = data.id;
	this.page = data.page;
	this.reminderEnabled = data.reminderEnabled;
};

bs.expiry.ui.DeleteExpiryPage.prototype.getActionKeys = function () {
	return [ 'cancel', 'delete', 'back' ];
};

bs.expiry.ui.DeleteExpiryPage.prototype.getAbilities = function () {
	return { cancel: true, delete: true, back: true };
};

bs.expiry.ui.DeleteExpiryPage.prototype.onAction = function ( action ) {
	const dfd = $.Deferred();

	if ( action === 'delete' ) {
		this.deleteExpiry().done( () => {
			dfd.resolve( { action: 'close', data: { success: true } } );
		} ).fail( ( error ) => {
			dfd.reject( error );
		} );
	} else if ( action === 'back' ) {
		dfd.resolve( {
			action: 'switchPanel', page: 'set-expiry',
			data: { id: this.id, page: this.page, reminderEnabled: this.reminderEnabled }
		} );
	} else {
		return bs.expiry.ui.DeleteExpiryPage.parent.prototype.onAction.call( this, action );
	}

	return dfd.promise();
};

bs.expiry.ui.DeleteExpiryPage.prototype.deleteExpiry = function () {
	const dfd = $.Deferred();

	blueSpice.api.tasks.exec(
		'expiry',
		'deleteExpiry',
		{
			expiryId: this.id || 0,
			pageName: this.page
		},
		{
			success: function () {
				if ( this.reminderEnabled ) {
					this.deleteReminder().done( () => {
						dfd.resolve();
					} ).fail( ( e ) => {
						dfd.reject( e );
					} );
				} else {
					dfd.resolve();
				}
			}.bind( this ),
			failure: function ( response ) {
				dfd.reject( response.message );
			}
		}
	);

	return dfd.promise();
};

/**
 * This is horrible, not a good way to integrate other extensions -.-
 *
 * @return {Promise}
 */
bs.expiry.ui.DeleteExpiryPage.prototype.deleteReminder = function () {
	const dfd = $.Deferred();

	blueSpice.api.tasks.exec(
		'reminder',
		'deleteReminder',
		{
			type: 'expiry',
			page: this.page
		},
		{
			success: function () {
				dfd.resolve();
			},
			failure: function () {
				dfd.resolve();
			}
		}
	);

	return dfd.promise();
};
