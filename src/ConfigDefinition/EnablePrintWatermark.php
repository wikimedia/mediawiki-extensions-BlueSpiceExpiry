<?php

namespace BlueSpice\Expiry\ConfigDefinition;

class EnablePrintWatermark extends \BlueSpice\ConfigDefinition\BooleanSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EXPORT . '/BlueSpiceExpiry',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceExpiry/' . static::FEATURE_EXPORT,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceExpiry',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-expiry-pref-enableprintwatermark';
	}

}
