<?php

namespace BlueSpice\Expiry\PageInfoElement;

use BlueSpice\Expiry\Extension as Expiry;
use BlueSpice\IPageInfoElement;
use BlueSpice\PageInfoElement;
use Message;

class Expired extends PageInfoElement {
	public $expire = 'undefined';
	public $expId = '';

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return $this->msg( 'bs-expiry-pageinfoelement-expired-label' );
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return "expiry-expired";
	}

	/**
	 *
	 * @return Message
	 */
	public function getTooltipMessage() {
		return $this->msg( 'bs-expiry-pageinfoelement-expired-tooltip' );
	}

	/**
	 *
	 * @param \ContextSource $context
	 * @return bool
	 */
	public function shouldShow( $context ) {
		$id = $context->getTitle()->getArticleID();
		$expiry = Expiry::getExpiryForPage( $id );

		if ( !$expiry || empty( $expiry['exp_date'] ) ) {
			return false;
		}

		$this->expId = $expiry['exp_id'];

		$this->expire = 'expired';

		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getItemClass() {
		return IPageInfoElement::ITEMCLASS_CONTRA;
	}

	/**
	 *
	 * @return string
	 */
	public function getUrl() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlClass() {
		return 'bs-expiry-pageinfo-page-' . $this->expire;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'pageinfo-expiry-expired';
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return IPageInfoElement::TYPE_MENU;
	}

	/**
	 *
	 * @return string
	 */
	public function getMenu() {
		return $this->makeMenu();
	}

	public function makeMenu() {
		$html = '';
		$label = $this->msg( 'bs-expiry-pageinfoelement-unexpire-label' );
		$tooltip = $this->msg( 'bs-expiry-pageinfoelement-unexpire-tooltip' );

		$html .= \Html::openElement( 'ul' );
		$html .= \Html::openElement( 'li' );

		$html .= \Html::element(
				'a',
				[
					'href' => "#",
					'class' => 'bs-expiry-unexpire',
					'data-expid' => $this->expId,
					'title' => $tooltip->plain()
				],
				$label->plain()
			);

		$html .= \Html::closeElement( 'li' );
		$html .= \Html::closeElement( 'ul' );

		return $html;
	}
}
