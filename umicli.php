<?php
/**
 * UMI.CMS command line script for Unix/Linux.
 *
 * This is the bootstrap script for running UMI.CMS on Unix/Linux.
 *
 * @author Ilia Rogov <ilyar.software@gmail.com>
 */
define('CRON', 'CLI');
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
#stream_set_blocking(STDIN, 0);
require 'standalone.php';
require  __DIR__ . '/console/consoleCommand.php';

@ob_clean();

if(!isset($_SERVER['HTTP_HOST'])) {
    $buffer = outputBuffer::current('CLIOutputBuffer');
    $console = new consoleCommandRunner();
    $console->loadCommands(__DIR__ . '/console/commands');
    $console->run($argv);
    $buffer->end();
}
