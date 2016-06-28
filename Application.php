<?php
/**
 * @brief		Server Monitoring Application Class
 * @author		<a href='http://fm-web.studio'>Fm Web (Recouse)</a>
 * @copyright	(c) 2016 Fm Web (Recouse)
 * @package		IPS Community Suite
 * @subpackage	Server Monitoring
 * @since		21 Jun 2016
 * @version		
 */
 
namespace IPS\monitoring;

/**
 * Server Monitoring Application Class
 */
class _Application extends \IPS\Application
{
    /**
     * Application icon
     *
     * @return  string
     */
    public function get__icon()
    {
        return 'list-alt';
    }

	/**
     * Copyright
     *
     * @return mixed
     */
    public function copyright()
    {
        return \IPS\Theme::i()->getTemplate( 'global', 'monitoring', 'admin' )->copyright( $this->version, $this->author, $this->website );
    }
}