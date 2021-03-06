<?php

/*
 * This file is part of the octris/octris.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Util;

/**
 * Helper methods for various validations.
 *
 * @copyright   copyright (c) 2016 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Validator
{
    /**
     * Add check for valid project path.
     *
     * @param   object              $object                 Object to add checks to.
     */
    public static function addProjectPathCheck($object)
    {
        $object->addValidator(function($value) {
            return is_dir($value);
        }, 'Specified path is not a directory or directory not found');
        $object->addValidator(function($value) {
            return is_file($value . '/etc/global.php');
        }, 'global app configuration not found "${value}/etc/global.php"');
    }

    /**
     * Add check for valid web application project path.
     *
     * @param   object              $object                 Object to add checks to.
     */
    public static function addWebProjectPathCheck($object)
    {
        self::addProjectPathCheck($object);

        $object->addValidator(function($value) {
            return (is_dir($value . '/libs/app') && is_file($value . '/libs/app/entry.php'));
        }, 'Specified path does not seem to belong to a web application created with the OCTRiS framework');
    }
}
