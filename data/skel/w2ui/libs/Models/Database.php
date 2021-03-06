<?php

/*
 * This file is part of the '{{$vendor}}/{{$package}}' package.
 *
 * (c) {{$company}}
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {{$namespace}}\Models;

/**
 * Proxy for accessing different database backends.
 *
 * @copyright   copyright (c) {{$year}} by {{$company}}
 * @author      {{$author}} <{{$email}}>
 */
class Database
{
    /**
     * Instance of database backend class.
     *
     * @type    \{{$namespace}}\Models\Database
     */
    protected $backend;
    /**/

    /**
     * Constructor.
     *
     * @param   array           $settings                   Database connection settings.
     */
    public function __construct(array $settings)
    {
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\'));
        $class = '{{$namespace}}\Models\\' . $settings['device'] . $class;

        $this->backend = new $class($settings);
    }
}
