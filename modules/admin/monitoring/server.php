<?php


namespace IPS\monitoring\modules\admin\monitoring;

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
	 * Games list
	 *
	 * @var array
	 */
	protected $games = array(
		'arkse' => "ARK: Survival Evolved",
		'arma2' => "ARMA 2",
		'arma3' => "ARMA 3",
		'cs' => "Counter Strike 1.6",
		'csgo' => "Counter Strike Global Offensive",
		'css' => "Counter Strike Source",
		'cszero' => "Counter Strike Condition Zero",
		'garrysmod' => "Garry's Mod",
		'hl' => "Half Life 1",
		'hl2dm' => "Half Life 2 Deathmatch",
		'l4d' => "Left 4 Dead",
		'left4dead2' => "Left 4 Dead 2",
		'mw3' => "Call of Duty: Modern Warfare 3",
		'ql' => "Quake Live",
		'rust' => "Rust",
		'samp' => "SA-MP",
		'starbound' => "Starbound",
		'tf2' => "Team Fortress 2",
		'tfc' => "Team Fortress Classic 1.6",
		'other' => "Other",
	);

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'server_manage' );
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{		
		/* Create the table */
		$table = new \IPS\Helpers\Table\Db( 'monitoring_server', \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=server' ) );
		$table->langPrefix = 'server_';

        /* Columns we need */
		$table->include = array( 'server_game', 'server_enabled', 'server_name', 'server_address', 'server_mod', 'm.member_id' );
		$table->mainColumn = 'server_name';
		$table->noSort	= array( 'server_game', 'server_enabled', 'server_address', 'm.member_id' );

		/* Joins */
		$table->joins = array(
			array(
				'select' => 'm.member_id',
				'from' => array( 'core_members', 'm' ),
				'where' => 'm.member_id=monitoring_server.server_moderator' )
		);

		/* Custom parsers */
		$table->parsers = array(
			'server_game'    => function( $val, $row )
			{
				return '<img src="' . \IPS\Theme::i()->resource('icons/' . $val . '.png', 'monitoring', 'front') . '" />';
			},
			'm.member_id'    => function( $val, $row )
			{
				//return \IPS\Theme::i()->getTemplate( 'global', 'core' )->userPhoto( \IPS\Member::constructFromData( $row ), 'mini' );
				if ( $row['server_moderator'] == null )
					return '<span class="ipsType_medium ipsType_light">' . \IPS\Member::loggedIn()->language()->addToStack('server_no_moderator') . '</span>';
				return \IPS\Member::constructFromData( $row )->link();
			},
			'server_address' => function( $val, $row )
			{
				return $row['server_ip'] . ':' . $row['server_port'];
			},
			'server_enabled' => function( $val, $row )
			{
				if( $row['server_enabled'] == 1 ) {
					$labelText = \IPS\Member::loggedIn()->language()->addToStack('server_enabled');
					$labelClass = 'ipsBadge ipsBadge_positive';
				}
				else
				{
					$labelText = \IPS\Member::loggedIn()->language()->addToStack('server_disabled');
					$labelClass = 'ipsBadge ipsBadge_neutral';
				}

				return '<span class="' . $labelClass . '">' . $labelText . '</span>';
			},
		);

		/* Specify the buttons */
		if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'monitoring', 'servers', 'server_add' ) )
		{
			$table->rootButtons = array(
				'add'	=> array(
					'icon'		=> 'plus',
					'title'		=> 'server_add',
					'link'		=> \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=server&do=add' ),
					'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('server_add') )
				)
			);
		}
		$table->rowButtons = function( $row )
		{
			$return = array();

			$return['view'] = array(
				'title'		=> 'view',
				'icon'		=> 'search',
				'link'		=> \IPS\Http\Url::external( \IPS\Settings::i()->base_url . '?app=monitoring&module=monitoring&controller=view&id=' ) . $row['server_id'],
				'class'		=> '',
				'target'    => '_blank'
			);

			if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'monitoring', 'servers', 'server_edit' ) )
			{
				$return['edit'] = array(
					'icon'		=> 'pencil',
					'title'		=> 'edit',
					'link'		=> \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=server&do=edit&id=' ) . $row['server_id'],
					'hotkey'	=> 'e'
				);
			}

			if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'monitoring', 'servers', 'server_delete' ) )
			{
				$return['delete'] = array(
					'icon'		=> 'times-circle',
					'title'		=> 'delete',
					'link'		=> \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=server&do=delete&id=' ) . $row['server_id'],
					'data'      => array(
						'delete' => '',
						'delete-warning' => \IPS\Member::loggedIn()->language()->addToStack('server_delete_confirm_desc')
					)
				);
			}

			return $return;
		};

		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('r__server');
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table ) . \IPS\Application::load( 'monitoring' )->copyright();
	}

	/**
	 * Add Server
	 *
	 * @return	void
	 */
	public function add()
	{
		/* Check permissions */
		\IPS\Dispatcher::i()->checkAcpPermission( 'server_add' );

		/* Build form */
		$form = new \IPS\Helpers\Form;
		$form->addHeader( 'server_general' );
		$form->add( new \IPS\Helpers\Form\YesNo( 'server_enabled', 1, FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_name', NULL, TRUE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_ip', NULL, TRUE, array( 'regex' => '/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/' ) ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_port', NULL, TRUE ) );
		$form->add( new \IPS\Helpers\Form\Select( 'server_game', array(), TRUE, array( 'options' => $this->games, NULL, 'parse' => 'normal' ) ) );
		$form->addHeader( 'server_other' );
		$form->add( new \IPS\Helpers\Form\Member( 'server_moderator', NULL, FALSE, array( 'multiple' => 1 ), NULL, NULL, NULL, 'server_moderator' ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_mod', NULL, FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_mod_description', NULL, FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_bans', NULL, FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_stats', NULL, FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_shop', NULL, FALSE ) );

		/* Handle submissions */
		if ( $values = $form->values() )
		{
			\IPS\Db::i()->insert( 'monitoring_server', array(
				'server_enabled' => $values['server_enabled'],
				'server_name' => $values['server_name'],
				'server_ip' => $values['server_ip'],
				'server_port' => $values['server_port'],
				'server_game' => $values['server_game'],
				'server_moderator' => $values['server_moderator']->member_id,
				'server_mod' => $values['server_mod'],
				'server_mod_desc' => $values['server_mod_description'],
				'server_bans' => $values['server_bans'],
				'server_stats' => $values['server_stats'],
				'server_shop' => $values['server_shop']
			) );

			unset( \IPS\Data\Store::i()->server_info );
			\IPS\Task::constructFromData( \IPS\Db::i()->select( '*', 'core_tasks', array( 'app=? AND `key`=?', 'monitoring', 'monitoringUpdate' ) )->first() )->run();

			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=server' ), 'saved' );
		}

		/* Display */
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->outputTemplate = array( \IPS\Theme::i()->getTemplate( 'global', 'core' ), 'blankTemplate' );
		}
		\IPS\Output::i()->title	= \IPS\Member::loggedIn()->language()->addToStack( 'server_add' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'server_add', $form, FALSE );
	}

	/**
	 * Edit Server
	 *
	 * @return	void
	 */
	public function edit()
	{
		/* Check permission */
		\IPS\Dispatcher::i()->checkAcpPermission( 'server_edit' );

		/* Load Server */
		$server = \IPS\Db::i()->select( array(
			'server_name',
			'server_ip',
			'server_port',
			'server_game',
			'server_bans',
			'server_stats',
			'server_shop',
			'server_mod',
			'server_mod_desc',
			'server_moderator'
		), 'monitoring_server', array( 'server_id=' . \IPS\Request::i()->id ) )->first();

		if ( empty( $server ) )
		{
			\IPS\Output::i()->error( 'node_error', '2C114/1', 404, '' );
		}

		/* View button */
		\IPS\Output::i()->sidebar['actions']['view'] = array(
			'title'		=> 'view',
			'icon'		=> 'search',
			'link'		=> \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=view&id=' . $server['server_id'] ),
			'class'		=> '',
			'target'    => '_blank'
		);
		/* Delete button */
		if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'monitoring', 'monitoring', 'server_delete' ) )
		{
			\IPS\Output::i()->sidebar['actions']['delete'] = array(
				'title'		=> 'delete',
				'icon'		=> 'times-circle',
				'link'		=> \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=server&do=delete&id=' . $server['freelancer_id'] ),
				'data'		=> array( 'delete' => '', 'delete-warning' => \IPS\Member::loggedIn()->language()->addToStack( 'server_delete_confirm_desc' ) )
			);
		}

		/* Build form */
		$form = new \IPS\Helpers\Form;
		$form->addTab( 'server_general' );
		$form->add( new \IPS\Helpers\Form\YesNo( 'server_enabled', $server['server_enabled'], FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_name', $server['server_name'], TRUE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_ip', $server['server_ip'], TRUE, array( 'regex' => '/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/' ) ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_port', $server['server_port'], TRUE ) );
		$form->add( new \IPS\Helpers\Form\Select( 'server_game', $server['server_game'], TRUE, array( 'options' => $this->games, NULL, 'parse' => 'normal' ) ) );
		$form->addTab( 'server_other' );
		if ( $server['server_moderator'] != 0 )
			$moderator = \IPS\Member::load( $server['server_moderator'] );
		$form->add( new \IPS\Helpers\Form\Member( 'server_moderator', $moderator, FALSE, array( 'multiple' => 1 ), NULL, NULL, NULL, 'server_moderator' ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_mod', $server['server_mod'], FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_mod_description', $server['server_mod_desc'], FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_bans', $server['server_bans'], FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_stats', $server['server_stats'], FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'server_shop', $server['server_shop'], FALSE ) );

		/* Handle submissions */
		if ( $values = $form->values() )
		{
			\IPS\Db::i()->update( 'monitoring_server', array(
				'server_enabled' => $values['server_enabled'],
				'server_name' => $values['server_name'],
				'server_ip' => $values['server_ip'],
				'server_port' => $values['server_port'],
				'server_game' => $values['server_game'],
				'server_moderator' => $values['server_moderator']->member_id,
				'server_mod' => $values['server_mod'],
				'server_mod_desc' => $values['server_mod_description'],
				'server_bans' => $values['server_bans'],
				'server_stats' => $values['server_stats'],
				'server_shop' => $values['server_shop']
			), array( 'server_id=' . \IPS\Request::i()->id ) );

			unset( \IPS\Data\Store::i()->server_info );
			\IPS\Task::constructFromData( \IPS\Db::i()->select( '*', 'core_tasks', array( 'app=? AND `key`=?', 'monitoring', 'monitoringUpdate' ) )->first() )->run();

			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=monitoring&module=monitoring&controller=server&do=edit&id=' . \IPS\Request::i()->id ), 'saved' );
		}

		\IPS\Output::i()->title		= $server['server_name'];
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'server_edit', $form, FALSE );
	}

	/**
	 * Delete
	 *
	 * @return	void
	 */
	public function delete()
	{
		/* Check permission */
		\IPS\Dispatcher::i()->checkAcpPermission( 'server_delete' );

		/* Load server */
		try
		{
			$freelancer = \IPS\Db::i()->select( array( 'server_id' ), 'monitoring_server', array( 'server_id=' . \IPS\Request::i()->id ) )->first();
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2C114/7', 404, '' );
		}

		/* Make sure the user confirmed the deletion */
		\IPS\Request::i()->confirmedDelete();

		/* Delete */
		\IPS\Db::i()->delete( 'monitoring_server', array( 'server_id=' . \IPS\Request::i()->id ) );

		/* Boink */
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=monitoring&module=monitoring&controller=server" ), 'deleted' );
	}
}