<?php

class ApiExpiryTasks extends BSApiTasksBase {
	protected $aTasks = array( 'saveExpiry', 'deleteExpiry', 'getDetailsForExpiry' );

	public function task_getDetailsForExpiry( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		$iArticleId = isset( $oTaskData->articleId )
			? (int) $oTaskData->articleId
			: 0
		;

		$oTitle = Title::newFromID( $iArticleId );

		$aExpiry = \BlueSpice\Expiry\Extension::getExpiryForPage( $iArticleId );

		if ( !$aExpiry ) {
			$oResult->message = $oResult->errors['expiryId'] = wfMessage( 'bs-expiry-unknown-page-msg' )->plain();
			return $oResult;
		}

		$aReturnData = array();
		$aReturnData['date'] = $aExpiry['exp_date'];
		$aReturnData['id'] = $aExpiry['exp_id'];
		$aReturnData['comment'] = $aExpiry['exp_comment'];

		$oResult->success = true;
		$oResult->payload = $aReturnData;
		return $oResult;
	}

	public function task_saveExpiry( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$oUser = $this->getUser();
		$bIsUpdate = false;
		if ( $oUser->isAnon() ) {
			$oResult->message = $oResult->errors['permissionError'] = wfMessage( 'bs-permissionerror' )->plain();
			return $oResult;
		}

		$sComment = addslashes( $oTaskData->comment );

		$iDate = isset( $oTaskData->date )
			? $oTaskData->date
			: 0;
		$dbr = wfGetDB( DB_REPLICA );
		// this is normally the case when clicking the expiry on a normal page
		// (not the overview specialpage) or the edit button on the specialpage
		// and data needs to be prefilled
		if ( empty( $oTaskData->articleId ) && !empty( $oTaskData->id) ) {
			$res = $dbr->select( 'bs_expiry', 'exp_page_id', array ( 'exp_id' => (int) $oTaskData->id ) );
			if ( !$res ) {
				$oResult->message = $oResult->errors['noactions'] =
					wfMessage( 'bs-expiry-unknown-page-msg' )->text();
			}
			$row = $res->fetchRow();
			if ( empty( $row['exp_page_id'] ) ) {
				$oResult->message = $oResult->errors['noactions'] =
					wfMessage( 'bs-expiry-unknown-page-msg' )->text();
				return $oResult;
			}
			$oTaskData->articleId = (int) $row['exp_page_id'];
		}
		if( empty( $oTaskData->articleId ) && !empty( $oTaskData->pageName ) ) {
			$oTitle = Title::newFromText( $oTaskData->pageName );
		} else {
			$oTitle = Title::newFromID( $oTaskData->articleId );
		}

		if ( !$oTitle instanceof Title || !$oTitle->exists() ) {
			$oResult->message = $oResult->errors['unknown-page'] =
				wfMessage( 'bs-expiry-unknown-page-msg' )->text();
			return $oResult;
		}
		//TODO: is valid date?
		$sFormattedFieldValue = date( "Y-m-d", $iDate );

		$res = $dbr->select(
			'bs_expiry',
			'*',
			array (
				'exp_page_id' => (int)$oTitle->getArticleID()
			),
			__METHOD__
		);
		$iExpiryId = 0;
		if ( $res && $res->numRows() ) {
			$row = $res->fetchRow();
			$iExpiryId = (int) $row['exp_id'];
			$bIsUpdate = true;
		}

		$aData = array (
			'exp_page_id' => (int)$oTitle->getArticleID(),
			'exp_date' => $sFormattedFieldValue,
			'exp_comment' => $sComment
		);

		$dbw = wfGetDB( DB_MASTER );
		if ( !$iExpiryId ) {
			$res = $dbw->insert( 'bs_expiry', $aData, __METHOD__ );
			if ( !$res ) {
				$oResult->message = $oResult->errors['createerror'] =
					wfMessage( 'bs-expiry-create-error' )->text();
				return $oResult;
			}

			$iExpiryId = $dbw->insertId();

			try {
				Hooks::run( 'BsExpiryOnSave', [
					$oTaskData,
					$iExpiryId,
					$oTitle->getArticleID(),
					$oUser->getId()
				] );
			} catch ( Exception $e ) {
				$oResult->message = $oResult->errors['createerror'] =
					$e->getMessage();
				return $oResult;
			}
		} else {
			$res = $dbw->update( 'bs_expiry', $aData, array ( 'exp_id' => $iExpiryId ) );
			if ( !$res ) {
				$oResult->message = $oResult->errors['updateerror'] =
					wfMessage( 'bs-expiry-update-error' )->text();
				return $oResult;
			}

			try {
				Hooks::run( 'BsExpiryOnUpdate', array ( $oTaskData, $iExpiryId ) );
			} catch ( Exception $e ) {
				$oResult->message = $oResult->errors['createerror'] =
					$e->getMessage();
				return $oResult;
			}
		}

		$oResult->success = true;

		$oTitle->invalidateCache();

		if ( $bIsUpdate ) {
			$oResult->message = wfMessage( "bs-expiry-update-success" )->plain();
		} else {
			$oResult->message = wfMessage( "bs-expiry-save-success" )->plain();
		}

		return $oResult;
	}

	public function task_deleteExpiry( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		// Check if there is a expiryId
		$iExpiryId = (int)$oTaskData->expiryId;
		$iArticleId = (int)$oTaskData->articleId;
		if ( !$iExpiryId ) {
			$oResult->message = $oResult->errors['expiryId'] =
				wfMessage( 'bs-expiry-unexpire-unsuccess' )->text();
			return $oResult;
		}

		// All ok, do the actual deletion
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'bs_expiry',
			array(
				'exp_id' => $iExpiryId
			),
			__METHOD__
		);

		if( !empty( $iArticleId ) && $oTitle = Title::newFromID( $iArticleId ) ) {
			$oTitle->invalidateCache();
		}

		$oResult->success = true;
		$oResult->message = wfMessage( 'bs-expiry-unexpire-success' )->plain();
		return $oResult;
	}

	protected function getRequiredTaskPermissions() {
		return array(
			'getDetailsForExpiry' => array( 'read' ),
			'saveExpiry' => array( 'expirearticle' ),
			'deleteExpiry' => array( 'expirearticle' )
		);
	}
}