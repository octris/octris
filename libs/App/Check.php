<?php

/*
 * This file is part of the octris/octris.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\App;

use \Octris\Core\Provider as provider;
use \Octris\Core\Validate as validate;

/**
 * Check a project for various kind of coding-style related flaws.
 *
 * @copyright   copyright (c) 2012-2016 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Check implements \Octris\Cli\App\ICommand
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Configure the command.
     * 
     * @param   \Aaparser\Command       $command            Instance of an aaparser command to configure.
     */
    public static function configure(\Aaparser\Command $command)
    {
        $command->setHelp('Syntactical check of project files.');
        $command->addOperand('path', 1, [
            'help' => 'Project path.'
        ])->addValidator(function($value) {
            return (is_dir($value) && is_file($value . '/etc/global.php'));

        // if (!is_dir($value)) {
        //     throw new \Octris\Cliff\Exception\Argument('specified path is not a directory or directory not found');
        // }
        //
        // $base = rtrim($args[0], '/');
        //
        // if (!is_file($base . '/etc/global.php')) {
        //     throw new \Octris\Cliff\Exception\Argument(sprintf('global app configuration not found "%s"!', $base . '/etc/global.php'));
        // }

            return true;
        });
    }

    /**
     * Return command manual.
     */
    public static function getManual()
    {
            return <<<EOT
NAME
    octris check - syntactical check of project files.

SYNOPSIS
    octris check     <project-path>

DESCRIPTION
    This command is used to check the syntax of files in a project. Currently
    validation can be performed for php files and OCTRiS template files.

OPTIONS

EXAMPLES
    Check a project:

        $ ./octris check ~/tmp/octris/test
EOT;
    }

    /**
     * Get a file iterator for a specified directory and specified regular expression matching file names.
     *
     * @param   string                          $dir            Director to iterate recusrivly.
     * @param   string                          $regexp         Regular expression each file has to match to.
     * @param   string                          $exclude        Optional pattern for filtering files.
     * @return  \RegexIterator                                  The iterator.
     */
    protected function getIterator($dir, $regexp, $exclude = null)
    {
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)),
            $regexp,
            \RegexIterator::GET_MATCH
        );

        if (!is_null($exclude)) {
            $iterator = new \Octris\Core\Type\Filteriterator($iterator, function ($current, $filename) use ($exclude) {
                return !preg_match($exclude, $filename);
            });
        }

        return $iterator;
    }

    /**
     * Run command.
     *
     * @param   array           $options                    Cli options.
     * @param   array           $operands                   Cli operands.
     */
    public function run(array $options, array $operands)
    {
        $base = rtrim($operands['path'][0], '/');
        
        // check php files
        $iterator = $this->getIterator($base, '/\.php$/', '/(\/data\/cldr\/|\/vendor\/)/');

        foreach ($iterator as $filename => $cur) {
            system(PHP_BINARY . ' -l ' . escapeshellarg($filename));
        }

        // check templates
        passthru(__DIR__ . '/../../bin/lint.php ' . escapeshellarg($base));
    }
}
