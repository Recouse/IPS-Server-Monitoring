<?php


namespace IPS\monitoring\modules\admin\monitoring;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'settings_manage' );
		parent::execute();
	}

	/**
	 * Manage
	 *
	 * @return	void
	 */
	public function manage()
	{
		$form = new \IPS\Helpers\Form;

		$form->addTab( 'monitoring_general' );
		$form->add( new \IPS\Helpers\Form\Select( 'monitoring_groups', array_filter( explode( ',', \IPS\Settings::i()->monitoring_groups ) ), TRUE, array( 'options' => \IPS\Member\Group::groups(), 'parse' => 'normal', 'multiple' => TRUE ) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showMapsFromGt', \IPS\Settings::i()->monitoring_showMapsFromGt, FALSE ) );

		$form->addTab( 'monitoring_customization' );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showGameIcon', \IPS\Settings::i()->monitoring_showGameIcon, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showMod', \IPS\Settings::i()->monitoring_showMod, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showModerator', \IPS\Settings::i()->monitoring_showModerator, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showGameTrackerIcon', \IPS\Settings::i()->monitoring_showGameTrackerIcon, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showSteamConnectIcon', \IPS\Settings::i()->monitoring_showSteamConnectIcon, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showBansIcon', \IPS\Settings::i()->monitoring_showBansIcon, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showStatsIcon', \IPS\Settings::i()->monitoring_showStatsIcon, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'monitoring_showShopIcon', \IPS\Settings::i()->monitoring_showShopIcon, FALSE ) );

		if ( $values = $form->values( TRUE ) )
		{
			$form->saveAsSettings( $values );
		}

		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('r__settings');
		\IPS\Output::i()->output	= $form;
	}
}