#!/usr/bin/env php
<?php

define( 'REAL_PATH', realpath(dirname(__FILE__)) );

require_once REAL_PATH.'/vendor/autoload.php';
require_once REAL_PATH.'/wwwroot/inc/pre-init.php';
require_once REAL_PATH.'/wwwroot/inc/config.php';
require_once REAL_PATH.'/wwwroot/inc/dictionary.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RacktablesDatabaseCommand extends Command
{

	protected function ensureDatabase(OutputInterface $output)
	{
		global $dbxlink;
		try{
			if( !$dbxlink ) connectDB();
			return $dbxlink;
		}catch (RackTablesError $e)
		{
			$output->write("<error>Database connection failed:\n\n" . $e->getMessage().'</error>');
			exit();
		}
	}

	protected function getDatabaseVersion(OutputInterface $output)
	{
		$dbxlink = $this->ensureDatabase($output);
		$prepared = $dbxlink->prepare ('SELECT varvalue FROM Config WHERE varname = "DB_VERSION" and vartype = "string"');
		if (! $prepared->execute())
		{
			$errorInfo = $dbxlink->errorInfo();
			$output->write('<error>SQL query failed with error ' . $errorInfo[2].'</error>');
			exit();
		}
		$rows = $prepared->fetchAll (PDO::FETCH_NUM);
		unset ($result);
		if (count ($rows) != 1 || !strlen ($rows[0][0])){
			$output->write('<error>Cannot guess database version. Config table is present, but DB_VERSION is missing or invalid. Giving up.</error>');
			exit();
		}
		$ret = $rows[0][0];
		return $ret;
	} 

}

class RacktablesBackupCommand extends RacktablesDatabaseCommand
{

	protected function configure()
	{
		$this
			->setName('backup:db')
			->setDescription('Backups the db')
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'just say what would be done')
			->addArgument('outputfile', InputArgument::OPTIONAL, 'Where to write the backup file');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		global $pdo_dsn, $db_username, $db_password;

		$dsn = $this->parseDSN($pdo_dsn);
		$dry_run = $input->getOption('dry-run');
		$cmd = 'mysqldump --host='.escapeshellarg($dsn['host']).' --user='.escapeshellarg($db_username).' --password='.escapeshellarg($db_password).' '.escapeshellarg($dsn['dbname']).' ';

		if( $input->getArgument('outputfile') ){
			$file = $input->getArgument('outputfile');
		}else{
			if( !is_dir(REAL_PATH.'/../backups/') ) mkdir( REAL_PATH.'/../backups/' );
			$file = REAL_PATH.'/../backups/'.strftime('%F-%T.sql');
		}

		$output->writeln( "<info>Running: $cmd</info>" );
		$output->writeln( "<info>Output to: $file</info>" );
		if( !$dry_run ){

			$proc = proc_open($cmd, array( 0 =>array('pipe', 'r') , 1 => array('file', $file, 'w' ), 2 => array('pipe', 'w') ), $pipes, null,array());

			$status = proc_get_status($proc);

			while( $status['running'] ){
				sleep(1);
				$output->write('.');
				$status = proc_get_status($proc);
			}
			if( $status['exitcode'] ){
				$output->writeln('<error>'.stream_get_contents($pipes[2]).'</error>');
				return $status['exitcode'];
			}

			$output->writeln( "\n<info>Done</info>" );
		}
	}

	private function parseDSN($dsn)
	{
		if( preg_match( '#^mysql:#', $dsn) ){
			$args = explode(';',substr($dsn,6));
			$params = array();
			foreach( $args as $arg ){
				list($key,$value) = explode('=',$arg,2);
				$params[$key] = $value;
			}
			return $params;
		}else{
			throw "Expected a DSN, but got: $dsn";
		}
	}

}

class RacktablesUpgradeCheckCommand extends RacktablesDatabaseCommand
{
	protected function configure()
	{
		$this
			->setName('upgrade:check')
			->setDescription('Checks if upgrades are necessary. Exit code is zero if no upgrade is needed and non-zero otherwise.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		return ( $this->getDatabaseVersion($output) == CODE_VERSION ) ? 0 : 1;
	}

}

class RacktablesUpgradeDoCommand extends RacktablesDatabaseCommand
{
	protected function configure()
	{
		$this
			->setName('upgrade:do')
			->setDescription('Actually upgrades racktables WITHOUT BACKUP. If the take the backup yourself, this is fine. Otherwise use "upgrade".')
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'just say what would be done');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		require './wwwroot/inc/upgrade.php';
		$this->ensureDatabase($output);
		return $this->upgrade($this->getDatabaseVersion($output), CODE_VERSION, $input, $output);
	}

	protected function upgrade($from, $to, InputInterface $input, OutputInterface $output )
	{
		global $dbxlink;
		$dry = $input->getOption('dry-run');
		if( $from == $to ){
			$output->writeln('<info>Database is up-to-date. Nothing to do here.</info>');
			return 0;
		}
		$failures = 0;
		$path = getDBUpgradePath ($from, $to);
		$output->writeln('<comment>Upgrading database ['.$from.'] -> ' . join(' -> ',$path).'</comment>' );
		$path[]='dictionary';
		foreach( $path as $version ){
			$batch = getUpgradeBatch($version);
			if( $version == 'dictionary' ){
				$output->writeln( '<info>Updating Dictionary</info>');
			}else{
				$output->writeln( '<info>Upgrading to Version '.$version.'</info>');
			}
			$output->writeln('');
			foreach( $batch as $query )
			{
				try
				{
					if( $output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE ){
						$output->writeln("<comment>  $query</comment>");
					}else{
						$output->write(".");
					}
					if( !$dry ) $dbxlink->query ($query);
				}
				catch (PDOException $e)
				{
					$errorInfo = $dbxlink->errorInfo();
					if( $output->getVerbosity() != OutputInterface::VERBOSITY_VERBOSE ){
						$output->writeln('');
						$output->writeln("<error>QUERY FAILED:	$query</error>");
					}
					$output->writeln("<error>REASON: {$errorInfo[2]}</error>");
					$failures++;
				}
			}
			$output->writeln("");
		}
		return $failures;
	}
}

class RacktablesUpgradeCommand extends Command
{

	protected function configure()
	{
		$this
				->setName('upgrade')
				->setDescription('Upgrades racktables with backup.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$check_command = $this->getApplication()->find('upgrade:check');

		$returnCode = $check_command->run(new Symfony\Component\Console\Input\ArrayInput(array('upgrade:check')), $output);

		if( !$returnCode )
		{
			$output->writeln('<info>Database is up-to-date.</info>');
			return 0;
		}

		$backup_command = $this->getApplication()->find('backup:db');
		if( !$backup_command->run(new Symfony\Component\Console\Input\ArrayInput(array('backup:db')), $output) ){
			return 1;
		}

		$upgrade_command = $this->getApplication()->find('upgrade:do');
		return $upgrade_command->run(new Symfony\Component\Console\Input\ArrayInput(array('upgrade:do')), $output);

	}

}


use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new RacktablesBackupCommand);
$application->add(new RacktablesUpgradeCheckCommand);
$application->add(new RacktablesUpgradeDoCommand);
$application->add(new RacktablesUpgradeCommand);
$application->run();
