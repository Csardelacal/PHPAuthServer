<?php 

$test = $_GET['testname'];

file_put_contents(
	'/tmp/current.txt',
	sprintf('/var/www/coverage/' . $test . '/')
);

mkdir('/var/www/coverage/' . $test);

/**
 * In case there is any stray coverage files, we remove them, since thay would
 * interfere. Usually all tests will dispose of their coverage in the teardown
 * method, but it's entirely feasible that if a test fails to finish it will 
 * generate stray data.
 */
$coverage = (glob('/var/www/coverage/' . $test . '/*.cov'));

foreach ($coverage as $file) {
	unlink($file);
}
