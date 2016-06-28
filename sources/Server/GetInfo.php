<?php
namespace IPS\monitoring;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

require_once str_replace( 'Server/GetInfo.php', 'Monitoring/Autoloader.php', str_replace( '\\', '/', __FILE__ ) );
\Monitoring\Autoloader::register();

use Monitoring\SourceQuery;
use Monitoring\SampQuery;

class GetInfo
{
    public $serverInfo = [];

    /**
     * Get server/s information
     *
     * @param $server
     * @param bool $rules
     * @return string
     * @throws \Monitoring\Exception\InvalidArgumentException
     * @throws \Monitoring\Exception\InvalidPacketException
     * @throws \Monitoring\Exception\QueryServerException
     * @throws \Monitoring\Exception\SocketException
     */
    public function server($server, $rules = FALSE )
    {
        $query = new SourceQuery;
        $sampQuery = new SampQuery;
        $serverInfo = array();

        try
        {
            foreach ( $server as $n => $serverGet )
            {
                if ( $serverGet['game'] == 'cs' || $serverGet['game'] == 'hl' )
                    $engine = SourceQuery :: GOLDSOURCE;
                else
                    $engine = SourceQuery :: SOURCE;

                try {
                    if ($serverGet['game'] != 'samp') {
                        $query->Connect( $serverGet['ip'], $serverGet['port'], 1, $engine );
                        $serverInfo[$n]['Info'] = $query->GetInfo();
                        $serverInfo[$n]['Players'] = $query->GetPlayers();
                        if ( $rules == TRUE ) $serverInfo[$n]['Rules'] = $query->GetRules();
                    }
                    else
                    {
                        $sampQuery->Connect( $serverGet['ip'], $serverGet['port'] );

                        $serverInfo[$n]['Info'] = $sampQuery->GetInfo();
                        $serverInfo[$n]['Players'] = $sampQuery->GetDetailedPlayers();
                        if ( $rules == TRUE ) $serverInfo[$n]['Rules'] = $sampQuery->GetRules();
                    }

                    /* Players sorted by frags */
                    if (!empty($serverInfo[$n]['Players'])) {
                        foreach ($serverInfo[$n]['Players'] as $key => $row) {
                            $frags[$key] = $row['Frags'];
                        }
                        array_multisort($frags, SORT_DESC, SORT_NUMERIC, $serverInfo[$n]['Players']);
                        unset($frags);
                    }

                    $serverInfo[$n]['Info']['HostAddress'] = $serverGet['ip'] . ':' . $serverGet['port'];
                    $serverInfo[$n]['Info']['Fill'] = round($serverInfo[$n]['Info']['Players'] / $serverInfo[$n]['Info']['MaxPlayers'] * 100);

                    /* Map image */
                    if ( \IPS\Settings::i()->monitoring_showMapsFromGt == 1 ) {
                        $serverInfo[$n]['Info']['MapImage'] = 'http://image.www.gametracker.com/images/maps/160x120/' . $serverGet['game'] . '/' . $serverInfo[$n]['Info']['Map'] . '.jpg';
                        $mapImageHeaders = get_headers($serverInfo[$n]['Info']['MapImage']);
                        if (!mb_strpos($mapImageHeaders[0], '200'))
                            $serverInfo[$n]['Info']['MapImage'] = 'http://image.www.gametracker.com/images/maps/160x120/nomap.jpg';
                    }
                    else
                    {
                        $serverInfo[$n]['Info']['MapImage'] = \IPS\Theme::i()->resource('maps/' . $serverGet['game'] . '/' . $serverInfo[$n]['Info']['Map'] . '.jpg', 'monitoring', 'front');
                        $mapImageHeaders = get_headers($serverInfo[$n]['Info']['MapImage']);
                        if (!mb_strpos($mapImageHeaders[0], '200'))
                            $serverInfo[$n]['Info']['MapImage'] = \IPS\Theme::i()->resource('maps/map_no_image.jpg', 'monitoring', 'front');
                    }
                    unset($mapImageHeaders);

                    $serverInfo[$n]['Info']['Online'] = isset($serverInfo[$n]['Info']['MaxPlayers']) ? 1 : 0;
                    $serverInfo['AllPlayers'] += $serverInfo[$n]['Info']['Players'];
                    $serverInfo['MaxAllPlayers'] += $serverInfo[$n]['Info']['MaxPlayers'];

                }
                catch( Exception $e )
                {
                    $serverInfo[$n]['Info']['HostName'] = \IPS\Member::loggedIn()->language()->addToStack('monitoring_map_changing');
                    $serverInfo[$n]['Info']['Players'] = '-';
                    $serverInfo[$n]['Info']['MaxPlayers'] = '-';
                    $serverInfo[$n]['Info']['HostAddress'] = $serverGet["ip"] . ':' . $serverGet["port"];
                }
                finally
                {
                    if( $serverGet['game'] != 'samp' )
                        $query->Disconnect();
                    else
                        $sampQuery->Close();
                }
            }
        }
        catch ( Exception $e )
        {
            return $e->getMessage();
        }

        $serverInfo['AllFill'] = round( $serverInfo['AllPlayers'] / $serverInfo['MaxAllPlayers'] * 100 );

        $this->serverInfo = $serverInfo;
    }
}