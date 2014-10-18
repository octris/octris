<?php

/*
 * This file is part of the '{{$vendor}}/{{$module}}' package.
 *
 * (c) {{$company}}
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {{$namespace}}\app {
    /**
     * index page.
     *
     * @octdoc      c:app/index
     * @copyright   copyright (c) {{$year}} by {{$company}}
     * @author      {{$author}} <{{$email}}>
     */
    class index extends \octris\core\app\web\page
    /**/
    {
        /**
         * The index points to which the current page should allow requests to have to be defined with this
         * property.
         *
         * @octdoc  p:index/$next_page
         * @type    array
         */
         protected $next_pages = array(
             '' => '{{$namespace}}\app\index',
         );
        /**/

        /**
         * The constructor is used to setup common settings for example validation rulesets must be defined 
         * through the page object constructor.
         *
         * @octdoc  m:index/__construct
         */
        public function __construct() 
        /**/
        {
            parent::__construct();
        }

        /**
         * Prepare rendering of a page. this method is called _BEFORE_ rendering a page.
         *
         * @octdoc  m:index/prepare
         * @param   \octris\core\app\page       $last_page  Instance of the page that was active before this one
         * @return  null|\octris\core\app\page              A page can be returned.
         */
        public function prepare(\octris\core\app\page $last_page, $action)
        /**/
        {
        }

        /**
         * This method is used to populate a template with data and render it.
         *
         * @octdoc  m:index/render
         */
        public function render()
        /**/
        {
            $tpl = \octris\core\app::getInstance()->getTemplate();
            $tpl->render('index.html');
        }
    }
}