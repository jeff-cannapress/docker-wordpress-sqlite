<?php
namespace docker {
	function adminer_object() {
		require_once('plugins/plugin.php');

		class Adminer extends \AdminerPlugin {

            function get_password_hash() {
                return password_hash(isset($_ENV['ADMINER_SQLITE_PASSWORD']) ? $_ENV['ADMINER_SQLITE_PASSWORD'] : random_bytes(32), PASSWORD_DEFAULT);		
            }
        	function credentials() {
                $password = get_password();
                $login = $_GET["username"];
                $result = $this->login($login, $password) ? '' : $password;
                return array(SERVER, $login,  $result );
            }
            
            function login($login, $password) {
                $hash = $this->get_password_hash();
                return password_verify($password, $hash);
            }
            function loginForm() {
                global $drivers;
                echo "<table cellspacing='0' class='layout'>\n";
                echo '<input type="hidden" value="sqlite" name="auth[driver]" />';
                echo '<input type="hidden" name="auth[server]" value="" />';
                echo '<input type="hidden" name="auth[username]" value="sqlite" />';
                $db = isset($_GET["db"]) ? $_GET["db"] : '/var/database/.ht.sqlite';
                echo $this->loginFormField('password', '<tr><th>' . lang('Password') . '<td>', '<input type="password" name="auth[password]" autocomplete="current-password">' . "\n");
                echo $this->loginFormField('db', '<tr><th>' . lang('Database') . '<td>', '<input name="auth[db]" value="' . h($db) . '" autocapitalize="off">' . "\n");
                echo "</table>\n";
                echo "<p><input type='submit' value='" . lang('Login') . "'>\n";
                echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang('Permanent login')) . "\n";
            }        
		}

		$plugins = [];
		foreach (glob('plugins-enabled/*.php') as $plugin) {
			$plugins[] = require($plugin);
		}

		return new Adminer($plugins);
	}
}

namespace {
	if (basename($_SERVER['DOCUMENT_URI'] ?? $_SERVER['REQUEST_URI']) === 'adminer.css' && is_readable('adminer.css')) {
		header('Content-Type: text/css');
		readfile('adminer.css');
		exit;
	}

	function adminer_object() {
		return \docker\adminer_object();
	}

	require('adminer.php');
}