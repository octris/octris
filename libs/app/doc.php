<?php

/*
 * This file is part of the octris/core.
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
 * Documentation tools.
 *
 * @octdoc      c:app/doc
 * @copyright   copyright (c) 2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Doc extends \Octris\Cliff\Args\Command
{
    /**
     * Constructor.
     *
     * @octdoc  m:doc/__construct
     * @param   string                              $name               Name of command.
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }
    
    /**
     * Return command description.
     *
     * @octdoc  m:doc/getDescription
     */
    public static function getDescription()
    {
        return '';
    }

    /**
     * Run command.
     *
     * @octdoc  m:lint/run
     * @param   \octris\cliff\args\collection        $args           Parsed arguments for command.
     */
    public function run(\octris\cliff\args\collection $args)
    {
        // $this->setError('not implemented, yet');
        //
        // return 1;

        // export EBNF
        $grammar = new \Octris\Core\Tpl\Compiler\Grammar();
        print $grammar->getEBNF();
    }
}
