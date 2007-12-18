<?php
/**
 * Installation operations
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.5
 * @package install
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */

// Call InstallTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
	define("PHPUnit_MAIN_METHOD", "InstallDbTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'www/install/Install.php';
require_once 'www/USVN/autoload.php';

/**
 * Test class for Install.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-20 at 09:07:00.
 */
class InstallDbTest extends USVN_Test_Test {
	private $db;
	private $_driver;

	/**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
	public static function main() {
		require_once "PHPUnit/TextUI/TestRunner.php";

		$suite  = new PHPUnit_Framework_TestSuite("InstallDbTest");
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	private function clean()
	{
		USVN_Db_Utils::deleteAllTables($this->db);
	}

	public function setUp()
	{
		parent::setUp();

		if (getenv('DB') == '' || getenv('DB') == 'PDO_SQLITE') {
			$this->markTestSkipped("Bad database");
		}
		$this->_driver = getenv('DB');
		$params = array ('host' => 'localhost',
		'username' => 'usvn-test',
		'password' => 'usvn-test',
		'dbname'   => 'usvn-test');
		$this->db = Zend_Db::factory($this->_driver, $params);

		Zend_Db_Table::setDefaultAdapter($this->db);
		USVN_Db_Utils::deleteAllTables($this->db);
		$_SERVER['SERVER_NAME'] = "localhost";
		$_SERVER['REQUEST_URI'] = "/test/install/index.php?step=7";
	}

	public function tearDown()
	{
		$this->clean();
        $this->db->closeConnection();
		parent::tearDown();
	}

	public function testInstallDbHostIncorrect()
	{
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "fake.usvn.info", "usvn-test", "usvn-test", "usvn-test", "usvn_", $this->_driver, false);
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}


	public function testInstallDbUserIncorrect() {
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-fake", "usvn-test", "usvn-test", "usvn_", $this->_driver, false);
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallDbPasswordIncorrect() {
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-fake", "usvn-test", "usvn_", $this->_driver, false);
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallDbDatabaseIncorrect() {
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-fake", "usvn_", $this->_driver, false);
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallDbConfigFileNotWritable() {
		try {
			Install::installDb("tests/fake/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_", $this->_driver, false);
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallDbTestLoadDb() {
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_", $this->_driver, false);
		$list_tables =  $this->db->listTables();
		$this->assertTrue(in_array('usvn_users', $list_tables), "usvn_users does not exist");
		$this->assertTrue(in_array('usvn_groups', $list_tables), "usvn_groups does not exist");
		$userTable = new USVN_Db_Table_Users();
		$this->assertEquals(null, $userTable->fetchRow(array('users_login = ?' => 'anonymous')));
		$this->assertEquals(0, count($userTable->fetchAll()));
	}

	public function testInstallDbTestLoadDbOtherPrefixe() {
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "fake_", $this->_driver, false);
		$list_tables =  $this->db->listTables();
		$this->assertFalse(in_array('usvn_users', $list_tables), "usvn_users exists");
		$this->assertFalse(in_array('usvn_groups', $list_tables), "usvn_groups exists");
		$this->assertTrue(in_array('fake_users', $list_tables), "usvn_users does not exist");
		$this->assertTrue(in_array('fake_groups', $list_tables), "fake_groups does not exist");
	}

	public function testInstallDbTestConfigFile() {
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_", $this->_driver, false);
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("localhost", $config->database->options->host);
		$this->assertEquals("usvn-test", $config->database->options->dbname);
		$this->assertEquals("usvn-test", $config->database->options->username);
		$this->assertEquals("usvn-test", $config->database->options->password);
		$this->assertEquals($this->_driver, $config->database->adapterName);
		$this->assertEquals("usvn_", $config->database->prefix);
	}

	public function testInstallDbTestConfigFileWithNewDb() {
		$params = array ('host' => 'localhost',
		'username' => 'usvn-root',
		'password' => 'usvn-root',
		'dbname'   => 'usvn-test');


		$this->db = Zend_Db::factory($this->_driver, $params);
		try {
			if ($this->_driver == 'PDO_PGSQL' || $this->_driver == 'ORACLE') {
				$this->db->getConnection()->query("DROP DATABASE \"usvn-root\"");
			}
			else if ($this->_driver == 'PDO_MYSQL' || $this->_driver == 'MYSQLI') {
				$this->db->getConnection()->query("DROP DATABASE `usvn-root`");
			}
		}
		catch (Exception $e) {
		}

		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-root", "usvn-root", "usvn-root", "usvn_", $this->_driver, true);
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("localhost", $config->database->options->host);
		$this->assertEquals("usvn-root", $config->database->options->dbname);
		$this->assertEquals("usvn-root", $config->database->options->username);
		$this->assertEquals("usvn-root", $config->database->options->password);
		$this->assertEquals($this->_driver, $config->database->adapterName);
		$this->assertEquals("usvn_", $config->database->prefix);
	}

/*
	public function testInstallDbTestConfigFileWithRandUser() {
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-root", "usvn-root", "usvn-root", "usvn_", false, true);
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("localhost", $config->database->options->host);
		$this->assertEquals("usvn-test", $config->database->options->dbname);
		$this->assertEquals("usvn-test", $config->database->options->username);
		$this->assertEquals("usvn-test", $config->database->options->password);
		$this->assertEquals($this->_driver, $config->database->adapterName);
		$this->assertEquals("usvn_", $config->database->prefix);
	}
*/
	public function testInstallAdmin()
	{
		file_put_contents("tests/tmp/config.ini", "[general]\nsubversion.path=tests/tmp/");
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_", $this->_driver, false);
		Install::installAdmin("tests/tmp/config.ini", "root", "secretpassword", "James", "Bond", "");
		$userTable = new USVN_Db_Table_Users();
		$user = $userTable->fetchRow(array('users_login = ?' => 'root'));
		$this->assertNotEquals(False, $user);
		$this->assertTrue(USVN_Crypt::checkPassword("secretpassword", $user->password));
		$this->assertEquals("James", $user->firstname);
		$this->assertEquals("Bond", $user->lastname);
		$this->assertEquals(true, (bool)$user->is_admin);
		$groupTable = new USVN_Db_Table_Groups();
		$this->assertEquals(0, count($groupTable->fetchAll()));
	}
}

// Call InstallTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "InstallDbTest::main") {
	InstallDbTest::main();
}
?>
