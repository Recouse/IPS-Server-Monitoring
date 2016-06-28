<?php


namespace IPS\monitoring\modules\front\monitoring;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * server
 */
class _server extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{

		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		if ( \IPS\Member::loggedIn()->inGroup( explode( ",", \IPS\Settings::i()->monitoring_groups ) ) )
		{
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( '__app_monitoring' );

			/* Update session location */
			\IPS\Session::i()->setLocation( \IPS\Http\Url::internal( 'app=monitoring', 'front', 'monitoring' ), array(), 'loc_monitoring_viewing_monitoring' );


			//if( empty( \IPS\Data\Store::i()->server_info ) )
				//\IPS\Task::constructFromData(\IPS\Db::i()->select('*', 'core_tasks', array('app=? AND `key`=?', 'monitoring', 'monitoringUpdate'))->first())->run();

			$serverInfo = \IPS\Data\Store::i()->server_info['data'];
			$serverInfoOther = \IPS\Data\Store::i()->server_info['other'];
			$lastUpdate = \IPS\DateTime::ts( \IPS\Data\Store::i()->server_info['fetched'] )->html();
			$allPlayers = array(
				'AllPlayers' => $serverInfo['AllPlayers'],
				'MaxAllPlayers' => $serverInfo['MaxAllPlayers'],
				'AllFill' => $serverInfo['AllFill'],
			);
			unset( $serverInfo['AllPlayers'], $serverInfo['MaxAllPlayers'], $serverInfo['AllFill'] );

			/* Display */
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'server', 'monitoring', 'front' )->serverInfo( $serverInfo, $serverInfoOther, $lastUpdate, $allPlayers ) . \IPS\Application::load( 'monitoring' )->copyright();
		}
		else
		{
			\IPS\Output::i()->error( 'no_module_permission', '2G188/9', 403, '' );
		}
	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}