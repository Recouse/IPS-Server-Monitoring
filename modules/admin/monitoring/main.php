<?php


namespace IPS\monitoring\modules\admin\monitoring;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * main
 */
class _main extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'main_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Don't show the ACP header bar */
		\IPS\Output::i()->hiddenElements[] = 'acpHeader';

		$app = \IPS\Application::load( 'monitoring' );

		if( empty( \IPS\Data\Store::i()->server_info ) )
			\IPS\Task::constructFromData(\IPS\Db::i()->select('*', 'core_tasks', array('app=? AND `key`=?', 'monitoring', 'monitoringUpdate'))->first())->run();
		$serverInfo = \IPS\Data\Store::i()->server_info;

		/* Init Chart */
		$chart = new \IPS\Helpers\Chart;

		/* Specify headers */
		$chart->addHeader( \IPS\Member::loggedIn()->language()->addToStack('server_name'), 'string' );
		$chart->addHeader( \IPS\Member::loggedIn()->language()->addToStack('server_players'), 'number' );

		$info['app']['name'] = \IPS\Member::loggedIn()->language()->addToStack('__app_monitoring');
		$info['app']['version'] = $app->version;
		$info['app']['longVersion'] = $app->long_version;
		$info['app']['author'] = $app->author;
		$info['app']['website'] = $app->website;
		$info['app']['added'] = \IPS\DateTime::ts( $app->added )->html();

		$info['server']['count'] = (int) \IPS\Db::i()->select( 'count(*)', 'monitoring_server' )->first();
		$info['server']['countEnabled'] = (int) \IPS\Db::i()->select( 'count(*)', 'monitoring_server', 'server_enabled=1' )->first();
		$info['server']['info'] = $serverInfo['data'];
		$info['server']['lastUpdate'] = \IPS\DateTime::ts( $serverInfo['fetched'] )->html();

		if ( count( $serverInfo['data'] ) > 0 )
		{
			foreach ( $serverInfo['data'] as $n => $server )
			{
				$chart->addRow( array(
					(string) $server['Info']['HostName'],
					$server['Info']['Players']
				) );

				$maxPlayers[] = $server['Info']['MaxPlayers'];
			}

			$increment = ceil( max( $maxPlayers ) / 5 );

			for ($i = 1; $i <= 5; $i++)
			{
				$v = $increment * $i;
				$ticks[] = array( 'v' => $v, 'f' => (string) $v );
			}
		}

		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('r__overview');
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'overview', 'monitoring' )->main( $info, \IPS\Output::i()->output = $chart->render( 'ColumnChart', array(
			'legend' => array( 'position' => 'right' ),
			'vAxis'  => array( 'ticks' => $ticks ),
		) ) );
	}
	
	public function updateCache()
	{
		/* Make sure the user confirmed the refreshing */
		\IPS\Request::i()->confirmedDelete();

		\IPS\Task::constructFromData(\IPS\Db::i()->select('*', 'core_tasks', array('app=? AND `key`=?', 'monitoring', 'monitoringUpdate'))->first())->run();


		/* Back */
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=monitoring&module=monitoring&controller=main" ), 'server_cache_refreshed' );
	}
}