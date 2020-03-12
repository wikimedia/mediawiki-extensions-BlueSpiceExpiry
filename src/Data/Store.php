<?php

namespace BlueSpice\Expiry\Data;

use BlueSpice\Services;

class Store implements \BlueSpice\Data\IStore {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			Services::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @return Writer
	 */
	public function getWriter() {
		return new Writer(
			$this->getReader(),
			Services::getInstance()->getDBLoadBalancer()
		);
	}
}
