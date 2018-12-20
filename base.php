<?php
/**
 * @package  Stats
 * @author   Alan Hardman <alan@phpizza.com>
 * @license  GPL
 * @version  1.0.0
 */

namespace Plugin\Stats;

class Base extends \Plugin {

	/**
	 * Check if plugin is installed
	 * @return bool
	 */
	function _installed() {
		return !!\Base::instance()->get("statsplugin.key");
	}

	/**
	 * Install plugin
	 */
	function _install() {
		$f3 = \Base::instance();
		$key = sha1(mt_rand() . mt_rand());
		$config = new \Model\Config;
		$config->setVal("statsplugin.key", $key);
		$config->setVal("statsplugin.last_sent", 0);
	}

	/**
	 * Initialize plugin
	 */
	function _load() {
		$f3 = \Base::instance();

		// Add menu item
		$this->_addNav("stats", "Stats", "/^\\/stats/i");

		// Add routes
		$f3->route("GET /stats", "Plugin\Stats\Controller->index");
		$f3->route("GET /stats/trends", "Plugin\Stats\Controller->trends");
		$f3->route("GET /stats/users", "Plugin\Stats\Controller->users");
		$f3->route("GET /stats/issues", "Plugin\Stats\Controller->issues");
	}

	/**
	 * Generate page for admin panel
	 */
	public function _admin() {
		echo \Helper\View::instance()->render("stats/admin.html");
	}

	/**
	 * Asynchronously post data via HTTP and sockets
	 * @link   http://stackoverflow.com/q/14587514/873843
	 * @param  string $url
	 * @param  array  $params
	 */
	protected function asyncPost($url, array $params = array()) {
		// create POST string
		$post_params = array();
		foreach ($params as $key => &$val) {
			$post_params[] = $key . '=' . urlencode($val);
		}
		$post_string = implode('&', $post_params);

		// get URL segments
		$parts = parse_url($url);

		// workout port and open socket
		$port = isset($parts['port']) ? $parts['port'] : 80;
		$fp = fsockopen($parts['host'], $port, $errno, $errstr, 30);

		if($fp) {
			// create output string
			$output  = "POST " . $parts['path'] . " HTTP/1.1\r\n";
			$output .= "Host: " . $parts['host'] . "\r\n";
			$output .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$output .= "Content-Length: " . strlen($post_string) . "\r\n";
			$output .= "Connection: Close\r\n\r\n";
			$output .= !empty($post_string) ? $post_string : '';

			// send output to $url handle
			fwrite($fp, $output);
			fclose($fp);

			return true;
		} else {
			return false;
		}

	}

}
