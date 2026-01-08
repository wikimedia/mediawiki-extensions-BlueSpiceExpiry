<?php

namespace BlueSpice\Expiry\Process;

use BlueSpice\Expiry\Data\Record;
use BlueSpice\Expiry\Factory;
use MediaWiki\Config\Config;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\Events\Notifier;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;

abstract class SendNotification implements IProcessStep {

	/** @var string */
	protected string $localTimeZone;

	/** @var Title[] */
	private array $expiredTitles;

	/**
	 * @param Notifier $notifier
	 * @param Factory $factory
	 * @param Config $config
	 */
	public function __construct(
		private readonly Notifier $notifier,
		protected readonly Factory $factory,
		Config $config
	) {
		$this->localTimeZone = $config->get( 'Localtimezone' ) ?? 'UTC';
		$this->expiredTitles = [];
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $data = [] ): array {
		$this->expiredTitles = $this->getExpiredTitles();

		if ( empty( $this->expiredTitles ) ) {
			return [];
		}

		foreach ( $this->expiredTitles as $title ) {
			$expiry = $this->factory->newFromTitle( $title );
			$this->sendNotifications( $title, $expiry, $this->notifier );
		}

		return [];
	}

	/**
	 * @return Title[]
	 */
	protected function getExpiredTitles(): array {
		return $this->factory->getExpiredTitles();
	}

	/**
	 * @param Title $title
	 * @param Record $record
	 * @param Notifier $notifier
	 */
	abstract protected function sendNotifications( Title $title, Record $record, Notifier $notifier );

}
