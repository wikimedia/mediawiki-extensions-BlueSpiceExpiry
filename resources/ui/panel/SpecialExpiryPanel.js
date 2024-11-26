ext.bluespice = ext.bluespice || {};
ext.bluespice.expiry = ext.bluespice.expiry || {};
ext.bluespice.expiry.ui = ext.bluespice.expiry.ui || {};
ext.bluespice.expiry.ui.panel = ext.bluespice.expiry.ui.panel || {};

ext.bluespice.expiry.ui.panel.SpecialExpiryPanel = function ( cfg ) {
	ext.bluespice.expiry.ui.panel.SpecialExpiryPanel.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-expiry-store',
		pageSize: 25
	} );

	this.setup();
};

OO.inheritClass( ext.bluespice.expiry.ui.panel.SpecialExpiryPanel, OO.ui.PanelLayout );

ext.bluespice.expiry.ui.panel.SpecialExpiryPanel.prototype.setup = function () {
	const addExpiryButton = new OO.ui.ButtonWidget( {
		icon: 'add',
		title: mw.message( 'bs-expiry-title-add' ).plain(),
		framed: false
	} );
	addExpiryButton.connect( this, {
		click: () => this.showExpiryDialog()
	} );

	this.tools = [ addExpiryButton ];

	const gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( gridCfg );
	this.grid.connect( this, {
		action: 'doActionOnRow'
	} );

	this.$element.append( this.grid.$element );
};

ext.bluespice.expiry.ui.panel.SpecialExpiryPanel.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			page_title: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-expiry-header-pagename' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => {
					return new OO.ui.HtmlSnippet( mw.html.element(
						'a',
						{
							href: mw.util.getUrl( value )
						},
						value
					) );
				}
			},
			expiry_date: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-expiry-header-date' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'date' }
			},
			exp_comment: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-expiry-header-comment' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' }
			},
			edit: {
				headerText: mw.message( 'bs-expiry-header-action-edit' ).text(),
				title: mw.message( 'bs-expiry-title-edit' ).text(),
				type: 'action',
				actionId: 'edit',
				icon: 'settings',
				invisibleHeader: true,
				width: 30
			},
			delete: {
				headerText: mw.message( 'bs-expiry-header-action-delete' ).text(),
				title: mw.message( 'bs-expiry-title-delete' ).text(),
				type: 'action',
				actionId: 'delete',
				icon: 'trash',
				invisibleHeader: true,
				width: 30
			}
		},
		store: this.store,
		tools: this.tools,
		provideExportData: () => {
			const deferred = $.Deferred();

			( async () => {
				try {
					this.store.setPageSize( 99999 );
					const response = await this.store.reload();

					const $table = $( '<table>' );
					let $row = $( '<tr>' );

					$row.append( $( '<td>' ).text( mw.message( 'bs-expiry-header-pagename' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-expiry-header-date' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-expiry-header-comment' ).text() ) );

					$table.append( $row );

					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];
							$row = $( '<tr>' );

							$row.append( $( '<td>' ).text( record.page_title ) );
							$row.append( $( '<td>' ).text( record.expiry_date ) );
							$row.append( $( '<td>' ).text( record.exp_comment ) );

							$table.append( $row );
						}
					}

					deferred.resolve( `<table>${$table.html()}</table>` );
				} catch ( error ) {
					deferred.reject( 'Failed to load data' );
				}
			} )();

			return deferred.promise();
		}
	};

	return gridCfg;
};

ext.bluespice.expiry.ui.panel.SpecialExpiryPanel.prototype.doActionOnRow = function ( action, row ) {
	if ( action === 'edit' ) {
		const dialogData = {
			id: row.id,
			page: row.page_title,
			date: row.expiry_date,
			comment: row.exp_comment
		};

		this.showExpiryDialog( dialogData );
	}
	if ( action === 'delete' ) {
		bs.util.confirm(
			'REremove',
			{
				title: mw.message( 'bs-expiry-title-delete' ).plain(),
				text: mw.message( 'bs-expiry-text-delete', 1 ).text()
			},
			{
				ok: () => { this.onRemoveExpiryOk( row.id ); }
			}
		);
	}
};

ext.bluespice.expiry.ui.panel.SpecialExpiryPanel.prototype.onRemoveExpiryOk = async function ( id ) {
	await bs.api.tasks.exec(
		'expiry',
		'deleteExpiry',
		{
			expiryId: id
		}
	);

	this.store.reload();
};

ext.bluespice.expiry.ui.panel.SpecialExpiryPanel.prototype.showExpiryDialog = async function ( data = {} ) {
	const expiryPages = await bs.expiry.getDialogPages( {}, data );

	const dialogAdd = new OOJSPlus.ui.dialog.BookletDialog( {
		id: 'bs-expiry-dlg-add',
		pages: expiryPages
	} );

	const result = await dialogAdd.show().closed;
	if ( result.success ) {
		this.store.reload();
	}
};
