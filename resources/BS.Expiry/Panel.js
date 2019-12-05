/**
 * Expiry Panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Expiry
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.Expiry.Panel', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi', 'BS.Expiry.PanelDialog' ],
	initComponent: function() {

		this.strMain = new BS.store.BSApi({
			apiAction: 'bs-expiry-store',
			autoLoad: true,
			remoteSort: true,
			fields: [
				'id',
				'page_title',
				'page_link',
				'expiry_date',
				'article_id',
				'exp_comment',
				'user_can_expire'
			],
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		} );

		this.colPageTitle = Ext.create( 'Ext.grid.column.Template', {
			id: 'page_title',
			header: mw.message('bs-expiry-header-pagename').plain(),
			sortable: true,
			dataIndex: 'page_title',
			tpl: '<a href="{page_link}">{page_title}</a>',
			filter: {
				type: 'string'
			}
		} );
		this.colExpiryDate = Ext.create( 'Ext.grid.column.Column', {
			id: 'expiry_date',
			header: mw.message('bs-expiry-header-date').plain(),
			sortable: true,
			dataIndex: 'expiry_date',
			renderer: this.renderDate,
			filter: {
				type: 'date'
			},
			filterable: true
		} );
		this.colComment = Ext.create( 'Ext.grid.column.Column', {
			header: mw.message('bs-expiry-header-comment').plain(),
			sortable: false,
			dataIndex: 'exp_comment',
			filter: {
				type: 'string'
			}
		});

		this.colMainConf.columns.push( this.colPageTitle );
		this.colMainConf.columns.push( this.colExpiryDate );
		this.colMainConf.columns.push( this.colComment );

		this.callParent( arguments );
	},
	renderDate: function( value ) {
		var expires = new Date( value );
		var today = new Date();
		if ( today > expires ) {
			return "<span style='color:red'>" + value + "</span>";
		};
		return value;
	},
	makeSelModel: function(){
		this.smModel = Ext.create( 'Ext.selection.CheckboxModel', {
			mode: "MULTI",
			selType: 'checkboxmodel'
		});
		return this.smModel;
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'REremove',
			{
				text: mw.message(
					'bs-expiry-text-delete',
					this.grdMain.getSelectionModel().getSelection().length
				).text(),
				title: mw.message( 'bs-expiry-title-delete' ).plain()
			},
			{
				ok: this.onRemoveExpiryOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onBtnEditClick: function () {
		this.selectedRow = this.grdMain.getSelectionModel().getSelection();
		var rowData = this.selectedRow[0].getData();
		var obj = {
			id: rowData.id,
			date: rowData.expiry_date,
			page_title: rowData.page_title,
			comment: rowData.exp_comment
		};
		var me = this;
		if ( !this.dlgEdit ) {
			this.dlgEdit = new BS.Expiry.PanelDialog({
				id: 'bs-expiry-dlg-edit'
			});
			this.dlgEdit.on( 'ok', me.onEditExpiryOk, me );
		}
		this.dlgEdit.setData( obj );
		this.dlgEdit.show();
	},
	onBtnAddClick: function () {
		var obj = {};
		var me = this;
		if ( !this.dlgAdd ) {
			this.dlgAdd = new BS.Expiry.PanelDialog({
				id: 'bs-expiry-dlg-add'
			});
			this.dlgAdd.on( 'ok', me.onAddExpiryOk, me );
		}
		this.dlgAdd.setData( obj );
		this.dlgAdd.show();
	},
	onAddExpiryOk: function( data ) {
		var obj = this.dlgAdd.getData();
		var me = this;
		bs.api.tasks.exec(
			'expiry',
			'saveExpiry',
			obj
		).done(function(){
			me.reloadStore();
		});
		$( document ).trigger( "BSExpiryAddOk", [ this, obj ] );
	},
	onEditExpiryOk: function( data ) {
		var obj = this.dlgEdit.getData();
		var me = this;
		bs.api.tasks.exec(
			'expiry',
			'saveExpiry',
			obj
		)
		.done(function(){
			me.reloadStore();
		});
		$( document ).trigger( "BSExpiryEditOk", [ this, obj ] );
	},
	onRemoveExpiryOk: function() {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		for (var i = 0; i < selectedRow.length; i++){
			var Id = selectedRow[i].get( 'id' );
			var me = this;
			bs.api.tasks.exec(
				'expiry',
				'deleteExpiry',
				{
					'expiryId': Id
				}
			)
			.done(function( response, xhr ){
				me.reloadStore();
			});
		}
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	makeRowActions: function() {
		var actions = this.callParent( arguments );
		for( var i = 0; i < actions.length; i++ ) {
			actions[i].isDisabled = function( view, rowIndex, colIndex, item, record  ) {
				return !record.get( 'user_can_expire' );
			};
		}

		return actions;
	}
});