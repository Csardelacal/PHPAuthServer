<?php

use Psr\Log\LoggerInterface;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;

include __DIR__ . '/vendor/autoload.php';

/**
 * Merging the code coverage can become extraordinarily memory intensive.
 * Raisin the limit prevents oom errors which would prevent the process from
 * finishing.
 */
ini_set('memory_limit', '8G');

/**
 * The logger can be used to process the events ocurring on the system and generate
 * a clean output.
 * 
 * @var LoggerInterface
 */
$logger = container()->get(LoggerInterface::class);

# Wait for the database to come up and make a proper connection
$attempts = 30;
sleep(5);

$conn = false;

do {
	try {
		$conn = mysqli_connect('mysql', 'root', 'root');
		if ($conn === false) { throw new Exception('Database not ready yet'); }
	}
	catch (\Exception $e) {
		/**
		 * The database was not available, did not become available and
		 * we died of boredom trying to reach it.
		 */
		if ($attempts < 1) { exit(1); }
		
		$logger->info($e->getMessage());
		$logger->debug(sprintf('%d attempts left. Retrying...', $attempts));
		
		sleep(10);
		$attempts--;
	}
}
while ($conn === false);

$apps = ['web.coverage'];

foreach ($apps as $app) {
	file_get_contents(sprintf('http://%s/init.php', $app));
}

$cmd = sprintf(
	'XDEBUG_MODE=off %1$s/vendor/bin/phpunit --testdox %1$s/tests/%2$s',
	getcwd(),
	$argv[1]?? ''
);

$logger->debug(sprintf('Executing: %s', $cmd));
passthru($cmd);

/**
 * Generate the code coverage report
 */
$filter = new Filter;
$filter->includeDirectory('/var/www/');

defined('XDEBUG_FILTER_CODE_COVERAGE') || define('XDEBUG_FILTER_CODE_COVERAGE', 1);
defined('XDEBUG_PATH_INCLUDE') || define('XDEBUG_PATH_INCLUDE', 1);
if (!function_exists('xdebug_set_filter')) {
	function xdebug_set_filter() {};
}

putenv('XDEBUG_MODE=coverage');

$coverage = new CodeCoverage(
	(new Selector)->forLineCoverage($filter),
	$filter
);

putenv('XDEBUG_MODE=');

foreach ($apps as $app) {
	/**
	 * Loop over the resulting file system and extract the coverage data. This needs
	 * to get mangled together into a single coverage file per test (otherwise PHPCoverage
	 * will generate a bunch of entries for each test when generating coverage)
	 */
	$resp = file_get_contents(sprintf('http://%s/list.php', $app));
	$list = json_decode($resp);

	$logger->debug($resp);

	foreach($list as /** @var string */$test)
	{
		/**
		 * Output the name of the directory we're processing to debugging.
		 */
		$logger->debug(
			sprintf('Processing coverage for test %s', $test)
		);
		
		$ctx = stream_context_create([
			'http' => [
				'timeout' => 600
			]
		]);
		
		$resp = file_get_contents(sprintf('http://%s/get.php?testname=%s', $app, $test), true, $ctx);
		$lcov = json_decode($resp, true);
		
		/**
		 * @todo Upon failure of the request, this step should be skipped so the
		 * other coverage data is not lost.
		 */
		$coverage->append(
			RawCodeCoverageData::fromXdebugWithMixedCoverage($lcov),
			$test
		);
	}
}

$logger->info('Generating coverage report...');
(new HtmlReport)->process($coverage, '/coverage-report');
