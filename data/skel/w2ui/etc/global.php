<?php

/*
 * This file is part of the '{{$vendor}}/{{$package}}' package.
 *
 * (c) {{$company}}
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Global application configuration.
 *
 * @copyright   copyright (c) {{$year}} by {{$company}}
 * @author      {{$author}} <{{$email}}>
 */

namespace {{$namespace}};

define('OCTRIS_APP_VENDOR', '{{$vendor}}');
define('OCTRIS_APP_NAME', '{{$package}}');
define('OCTRIS_APP_BASE', realpath(__DIR__ . '/../'));

\Octris\Core\Registry::getInstance()
    ->set('config', function () {
            return new \Octris\Core\Config('config');
        }, \Octris\Core\Registry::T_SHARED | \Octris\Core\Registry::T_READONLY)
    ->set('mode', function($registry) {
            return (function() use ($registry) {
                if (($mode = getenv('OCTRIS_APP_MODE')) === false) {
                    $mode = (isset($registry->config['mode']) ? $registry->config['mode'] : '');
                }

                return $mode;
            })();
        }, \Octris\Core\Registry::T_SHARED | \Octris\Core\Registry::T_READONLY)
    ->set('createTemplate', function($registry) {
            $tpl = new \Octris\Core\Tpl();

            $tpl->setL10n(\Octris\Core\L10n::getInstance());
            if ($registry->mode != 'development') {
                $tpl->setCache(
                    new \Octris\Core\Tpl\Cache\File(
                        OCTRIS_APP_BASE . '/cache/templates_c/'
                    )
                );
            }
            $tpl->addPostprocessor(
                new \Octris\Core\Tpl\Postprocess\CombineJs(
                    [
                        '/js/' => OCTRIS_APP_BASE . '/libsjs/',
                        '/vjs/' => OCTRIS_APP_BASE . '/vendor_assets/js/'
                    ],
                    OCTRIS_APP_BASE . '/host/libsjs/'
                )
            );
            $tpl->addPostprocessor(
                new \Octris\Core\Tpl\Postprocess\CombineCss(
                    [
                        '/css/' => OCTRIS_APP_BASE . '/styles/',
                        '/vcss/' => OCTRIS_APP_BASE . '/vendor_assets/css/'
                    ],
                    OCTRIS_APP_BASE . '/host/styles/'
                )
            );
            $tpl->addSearchPath(OCTRIS_APP_BASE . '/templates/');

            return $tpl;
        }, \Octris\Core\Registry::T_READONLY);
