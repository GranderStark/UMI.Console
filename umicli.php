<?php
/**
 * UMI.CMS command line script for Unix/Linux.
 *
 * This is the bootstrap script for running UMI.CMS on Unix/Linux.
 *
 * @author Ilia Rogov <ilyar.software@gmail.com>
 */
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
#stream_set_blocking(STDIN, 0);
require 'standalone.php';
require  CURRENT_WORKING_DIR . '/console/consoleCommand.php';

@ob_clean();

if(isset($_SERVER['HTTP_HOST'])) {

    $buffer = outputBuffer::current('HTTPOutputBuffer');
    $buffer->contentType('text/plain');

    $comment = "This file should be executed by command line only. ";
    $buffer->push($comment);

} else {

    $buffer = outputBuffer::current('CLIOutputBuffer');
    $console = new consoleCommandRunner();
    $console->loadCommands(CURRENT_WORKING_DIR . '/console/commands');
    $console->run($argv);
}

$buffer->end();