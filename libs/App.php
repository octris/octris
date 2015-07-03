<?php

/*
 * This file is part of the 'octris' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris;

use \Octris\Core\Provider as provider;

/**
 * Application class.
 *
 * @copyright   copyright (c) 2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class App extends \Octris\Cliff\App
{
    /**
     * Application name.
     *
     * @type    string
     */
    protected static $app_name = 'octris';
    /**/

    /**
     * Application version.
     *
     * @type    string
     */
    protected static $app_version = '0.0.9';
    /**/

    /**
     * Application version date.
     *
     * @type    string
     */
    protected static $app_version_date = '2015-07-04';
    /**/

    /**
     * Constructor.
     *
     * @param
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Configure application arguments.
     */
    protected function configure()
    {
        parent::configure();

        $this->addCommand(new \Octris\App\Config('config'));
        $this->addCommand(new \Octris\App\Create('create'));
        $this->addCommand(new \Octris\App\Graph('graph'));
        $this->addCommand(new \Octris\App\Check('check'));
        $this->addCommand(new \Octris\App\Test('test'));
        $this->addCommand(new \Octris\App\Doc('doc'));
        $this->addCommand(new \Octris\App\Httpd('httpd'));
    }

    /**
     * Run main application.
     *
     * @param   \Octris\Cliff\Args\Collection        $args           Parsed arguments.
     */
    protected function main(\Octris\Cliff\Args\Collection $args)
    {
        if (count($GLOBALS['argv']) == 1) {
            $this->showHelp();

            exit(1);
        } else {
            exit(0);
        }
    }

    /**
     * Show help.
     */
    protected function showHelp()
    {
        printf("               __         .__
  ____   _____/  |________|__| ______
 /  _ \_/ ___\   __\_  __ \  |/  ___/    OCTRiS framework tool
(  <_> )  \___|  |  |  | \/  |\___ \     copyright (c) %s by Harald Lapp
 \____/ \___  >__|  |__|  |__/____  >    http://github.com/octris/octris/
            \/%20s\/\n\n",
            explode('-', static::$app_version_date)[0],
            'v' . static::$app_version
        );

        parent::showHelp();
    }
}
