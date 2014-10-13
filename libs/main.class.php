<?php

/*
 * This file is part of the 'octris' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace octris {
    use \org\octris\core\provider as provider;
    use \org\octris\cliff\options as options;

    /**
     * Main application class.
     *
     * @octdoc      c:libs/main
     * @copyright   copyright (c) 2014 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class main extends \org\octris\cliff\app
    /**/
    {
        /**
         * Octris tool version and version-date.
         *
         * @octdoc  d:main/T_VERSION
         * @type    string
         */
        const T_VERSION = '0.0.3';
        const T_VERSION_DATE = '2014-10-13';
        /**/

        /**
         * Available commands.
         *
         * @octdoc  p:main/$commands
         * @type    array
         */
        protected $commands = array();
        /**/

        /**
         * Constructor.
         *
         * @octdoc  m:main/__construct
         * @param   
         */
        public function __construct()
        /**/
        {
        }

        /**
         * Determine available "commands".
         *
         * @octdoc  m:main/getCommands
         */
        protected function getCommands()
        /**/
        {
            $this->commands = array();
            
            foreach (new \DirectoryIterator(__DIR__ . '/command/') as $file) {
                if (!$file->isDot() && substr(($name = $file->getFilename()), -4) == '.php') {
                    $command = basename($name, '.class.php');
                    
                    $this->commands[$command] = '\\octris\\command\\' . $command;
                }
            }
            
            ksort($this->commands);
        }
    
        /**
         * Run main application.
         *
         * @octdoc  m:main/main
         */
        public function main()
        /**/
        {
            global $argv;
            
            $this->getCommands();
            
            $opts = new \org\octris\cliff\options();
            $opts->addOption(['h', 'help'])->setAction(function() {
                $this->showUsage();
                exit(1);
            });
            $opts->addOption(['version'])->setAction(function() {
                printf("octris %s (%s)\n", self::T_VERSION, self::T_VERSION_DATE);
                exit(1);
            });
            
            // help command
            $opts->addCommand('help')->setAction(function(\org\octris\cliff\options\collection $collection) {
                if (count($operands = $collection->getOperands()) != 1) {
                    $this->showUsage();
                    exit(1);
                }
                
                if (!isset($this->commands[$operands[0]])) {
                    printf("octris: '%s' is not a command\n", $operands[0]);
                    exit(1);
                }
                
                $class = '\\octris\\command\\' . $operands[0];
                
                print trim($class::getManual(), "\n") . "\n";
                exit(1);
            });
            
            // create
            $cmd = $opts->addCommand('create')->setAction(function(\org\octris\cliff\options\collection $collection) {
                // (new \octris\command\create($args))->run();
                print "subcommand create\n";
                var_dump($collection);
            });
            $cmd->addOption(['p'], options::T_VALUE);
            $cmd->addOption(['t'], options::T_VALUE);
            $cmd->addOption(['d'], options::T_KEYVALUE);

            if (!$opts->process()) {
                $this->showUsage();
                exit(1);
            }

            exit(0);
        }
    
        /**
         * Show usage information.
         *
         * @octdoc  m:main/showUsage
         */
        public function showUsage()
        /**/
        {
            debug_print_backtrace();
            
            printf("               __         .__        
  ____   _____/  |________|__| ______
 /  _ \_/ ___\   __\_  __ \  |/  ___/    OCTRiS framework tool
(  <_> )  \___|  |  |  | \/  |\___ \     copyright (c) 2014 by Harald Lapp
 \____/ \___  >__|  |__|  |__/____  >    http://github.com/octris/octris/
            \/%20s\/

usage: octris --help
usage: octris --version
usage: octris help <command>
usage: octris <command> [ARGUMENTS]

Commands:
", 'v' . self::T_VERSION);

            $size = array_reduce(array_keys($this->commands), function($size, $item) {
                return max($size, strlen($item));
            }, 0);

            foreach ($this->commands as $command => $class) {
                if (($desc = $class::getDescription()) == '') {
                    continue;
                }
                
                printf("    %-" . $size . "s    %s\n", $command, $class::getDescription());
            }
        }
    }

    provider::set('env', $_ENV);
}
