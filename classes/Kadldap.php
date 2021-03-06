<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * AD/LDAP Module for Kohana.
 *
 * @package    Kadldap
 * @author     Beau Dacious <dacious.beau@gmail.com>
 * @copyright  (c) 2009 Beau Dacious
 * @author     Sam Wilson <sam@samwilson.id.au>
 * @copyright  (c) 2011 Sam Wilson
 * @author     Github user 'sfroeth'
 * @copyright  (c) 2011 sfroeth
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Kadldap
{
	/** @var \Adldap\Adldap Instance of third-party Adldap library. */
	protected $_adldap;

	/**
	 * Return a singleton instance of Kadldap.
	 *
	 * @return Kadldap
	 */
	public static function instance()
	{
		static $instance;

		// Load the Kadldap instance
		empty($instance) AND $instance = new Kadldap();

		return $instance;
	}

	/**
	 * Reads config file and loads third-party adLDAP library.
	 *
	 * @return void
	 */
	public function __construct()
	{
		/*
		 * Get and check config.
		*/
		$config = Kohana::$config->load('kadldap')->kadldap;
		if (count($config['domain_controllers'])==0)
		{
			$message = "No domain controllers provided in Kadldap configuration.";
			throw new Kohana_Exception($message);
		}

		/*
		 * Include third-party adLDAP library from vendor directory, if it's not
		 * already autoloaded (via Composer).
		 */
		if ( ! class_exists('\Adldap\Adldap'))
		{
			$adldap_file = Kohana::find_file('vendor/Adldap/src', 'Adldap');
			if (!$adldap_file)
			{
				throw new Kohana_Exception('Unable to find Adldap library.');
			}
			require_once $adldap_file;
		}

		/*
		 * Store instantiation of Adldap library.
		*/
		$this->_adldap = new \Adldap\Adldap($config);
	}

	/**
	 * Wrapper for all functions in the adLDAP class that have not already been
	 * wrapped in this class.
	 *
	 * @param <type> $name
	 * @param <type> $arguments
	 * @return <type>
	 */
	public function __call($name, $arguments)
	{
		if ( method_exists($this->_adldap, $name) )
		{
			return call_user_func_array(array($this->_adldap, $name), $arguments);
		}
		else
		{
			throw new Exception("Method $name does not exist in \Adldap\Adldap.");
		}
	}

}
