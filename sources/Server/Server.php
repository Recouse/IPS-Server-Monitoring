<?php
/**
 * @brief		Server Monitoring
 * @author		Recouse (http://recouse.me)
 * @copyright   (c) 2017 - Recouse
 * @package		Monitoring
 * @since		12 Feb 2017
 */

namespace IPS\monitoring;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/* Make sure application class is loaded */
class_exists( 'IPS\monitoring\Application' );

/**
 * Game
 */
class _Server extends \IPS\Patterns\ActiveRecord
{

	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;

	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'monitoring_servers';

	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'server_';

	public function data()
	{
		try
		{
			$data = \IPS\Db::i()->select( '*', 'monitoring_servers_data', array( 'server_id=?', $this->id ) )->first();
		}
		catch ( \UnderflowException $e )
		{
			return array();
		}

		return $data;
	}
}
