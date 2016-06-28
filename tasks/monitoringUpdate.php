<?php
/**
 * @brief        monitoringUpdate Task
 * @author        <a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) 2001 - 2016 Invision Power Services, Inc.
 * @license        http://www.invisionpower.com/legal/standards/
 * @package        IPS Community Suite
 * @subpackage    monitoring
 * @since        22 Jun 2016
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\monitoring\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

require_once str_replace( 'tasks/monitoringUpdate.php', 'sources/Server/GetInfo.php', str_replace( '\\', '/', __FILE__ ) );

use IPS\monitoring\GetInfo;

/**
 * monitoringUpdate Task
 */
class _monitoringUpdate extends \IPS\Task
{
    /**
     * Execute
     *
     * If ran successfully, should return anything worth logging. Only log something
     * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which will be most cases).
     * If an error occurs which means the task could not finish running, throw an \IPS\Task\Exception - do not log an error as a normal log.
     * Tasks should execute within the time of a normal HTTP request.
     *
     * @return    mixed    Message to log or NULL
     * @throws    \IPS\Task\Exception
     */
    public function execute()
    {
        $getServer = \IPS\Db::i()->select( array(
            'server_id',
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
        ), 'monitoring_server', array( 'server_enabled=1' ) );

        if( count( $getServer ) > 0) {
            $n = 0;
            foreach ($getServer as $row) {
                $server[$n] = array(
                    'ip' => $row['server_ip'],
                    'port' => $row['server_port'],
                    'game' => $row['server_game'],
                );
                $serverInfoOther[$n] = array(
                    'id' => $row['server_id'],
                    'name' => $row['server_name'],
                    'game' => $row['server_game'],
                    'bans' => $row['server_bans'],
                    'stats' => $row['server_stats'],
                    'shop' => $row['server_shop'],
                    'mod' => $row['server_mod'],
                    'mod_desc' => $row['server_mod_desc'],
                    'moderator' => \IPS\Member::load( $row['server_moderator'] )->link( NULL, TRUE ),
                );
                $n++;
            }

            $monitoring = new GetInfo;
            $monitoring->server( $server );

            $serverInfo = $monitoring->serverInfo;

            unset( \IPS\Data\Store::i()->server_info );
            \IPS\Data\Store::i()->server_info = array(
                'fetched' => time(),
                'data' => $serverInfo,
                'other' => $serverInfoOther
            );
        }

        return NULL;
    }
}