<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\analysis\logger\adapter;

use \lithium\util\String;

/**
 * The `Cache` logger allows log messages to be written to cache configurations set up in
 * `lithium\storage\Cache`. In order to use this adapter, you must first configure a cache adapter
 * for it to write to, as follows:
 *
 * {{{ lithium\storage\Cache::config(array(
 * 	'storage' => array('adapter' => 'Redis', 'server' => '127.0.0.1:6379')
 * ));}}}
 *
 * Then, you can configure the `Cache` logger with the `'storage'` config:
 * {{{ lithium\analysis\Logger::config(array(
 * 	'debug' => array('adapter' => 'Cache', 'config' => 'storage')
 * ));
 * }}}
 *
 * You can then send messages to the logger which will be written to the cache store:
 * {{{
 * lithium\analysis\Logger::write('debug', 'This message will be written to a Redis data store.');
 * }}}
 *
 * @see lithium\storage\Cache
 */
class Cache extends \lithium\core\Object {

	/**
	 * Classes used by `Cache`.
	 *
	 * @var array
	 */
	protected $_classes = array(
		'cache' => '\lithium\storage\Cache'
	);

	/**
	 * Class constructor
	 *
	 * @param array $config
	 * @return void
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'config' => null,
			'expiry' => '+999 days',
			'key' => 'log_{:type}_{:timestamp}'
		);
		parent::__construct($config + $defaults);
	}

	/**
	 * Appends `$data` to file `$type`.
	 *
	 * @param string $type
	 * @param string $message
	 * @return boolean True on successful write, false otherwise.
	 */
	public function write($type, $message) {
		$config = $this->_config;

		return function($self, $params, $chain) use ($config) {
			$params += array('timestamp' => strtotime('now'));
			$key = $config['key'];
			$key = is_callable($key) ? $key($params) : String::insert($key, $params);

			$cache = $this->_classes['cache'];
			$cache::write($config['config'], $key, $params['message'], $config['expiry']);
		};
	}
}

?>