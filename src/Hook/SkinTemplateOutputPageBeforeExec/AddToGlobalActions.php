<?php

namespace BlueSpice\Expiry\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddToGlobalActions extends SkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialExpiry = \SpecialPageFactory::getPage( 'Expiry' );

		if ( !$oSpecialExpiry ) {
			return true;
		};

		if ( !$this->getContext()
			->getUser()
			->isAllowed( $oSpecialExpiry->getRestriction() )
		) {
			return true;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-expiry' => [
					'href' => $oSpecialExpiry->getPageTitle()->getFullURL(),
					'text' => $oSpecialExpiry->getDescription(),
					'title' => $oSpecialExpiry->getPageTitle(),
					'iconClass' => ' icon-hour-glass ',
					'position' => 800,
					'data-permissions' => 'read'
				]
			]
		);

		return true;
	}
}