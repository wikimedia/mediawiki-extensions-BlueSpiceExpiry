<?php
namespace BlueSpice\Expiry\Hook;

use BlueSpice\Hook;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

abstract class BsExpiryOnUpdate extends Hook {
	/**
	 *
	 * @var \stdClass
	 */
	protected $taskData = null;

	/**
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @param \stdClass $taskData
	 * @param int $id
	 * @param User $user
	 * @param Title $title
	 * @return bool
	 */
	public static function callback( $taskData, $id, $user, $title ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$taskData,
			$id,
			$user,
			$title
		);
		return $hookHandler->process();
	}

	/**
	 * @param IContextSource $context
	 * @param Config $config
	 * @param \stdClass $taskData
	 * @param int $id
	 * @param User $user
	 * @param Title $title
	 */
	public function __construct( $context, $config, $taskData, $id, $user, $title ) {
		parent::__construct( $context, $config );

		$this->taskData = $taskData;
		$this->id = $id;
		$this->user = $user;
		$this->title = $title;
	}
}
