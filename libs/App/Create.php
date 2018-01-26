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

use \Octris\Readline as readline;

/**
 * Create a new project.
 *
 * @copyright   copyright (c) 2014-2016 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Create implements \Octris\Cli\App\CommandInterface
{
    /**
     * Settings used.
     *
     * @type    array
     */
    protected static $settings = array('company', 'author', 'email', 'homepage', 'repository');

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
    public static function configure(\Octris\Cli\App\Command $command)
    {
        $package = '';

        $command->setHelp('Create a new project.');
        $command->setDescription(<<<DESCRIPTION
This command creates a new project of the specified type in the specified destination-path. A valid basic directory layout will be created from a skeleton according to the specified project-type.

The supported types are:

DESCRIPTION
        );
        $command->setExample(<<<EXAMPLE
Create a test project:

    $ ./octris create -p example/test -t web \
        -d company="Foo Inc." \
        -d author="Bar Baz" \
        -d email="info@example.org" \
        ~/tmp/
EXAMPLE
        );
        $command->addOption('project', '-p | --project <project-name>', ['\Aaparser\Coercion', 'value'], [
            'help' => 'A valid name for the project in the form of <vendor>/<package>.',
            'required' => true
        ])->addValidator(function($value) use (&$package) {
            $package = $value;

            $validator = new \Octris\Core\Validate\Type\Project();

            if (($is_valid = $validator->validate($validator->preFilter($value)))) {
                list(, $package) = explode('/', $value);
            }

            return $is_valid;
        }, 'invalid project name specified');
        $command->addOption('type', '-t | --type <project-type>', ['\Aaparser\Coercion', 'value'], [
            'help' => 'The type of the project.',
            'required' => true
        ])->addValidator(function($value) {
            return in_array($value, ['web', 'w2ui', 'cli', 'lib']);
        }, 'invalid project type specified')
          ->addValidator(function($value) {
            return is_dir(__DIR__ . '/../../data/skel/' . $value . '/');
        }, 'unable to locate template directory "' . __DIR__ . '/../../data/skel/${value}/".');
        $command->addOption('define', '-d | --define <key-value>', ['\Aaparser\Coercion', 'kv'], [
            'help' => 'Overwrite default configuration settings for "' . implode('", "', self::$settings) . '".'
        ])->addValidator(function($value) {
            return in_array(key($value), self::$settings);
        }, 'Invalid configuration key specified "${value}"')
          ->addValidator(function($value) {
            $val = current($value);

            return (!is_null($val) && $val !== '');
        }, 'Configuration value must not be empty');
        $command->addOperand('project-path', 1, [
            'help' => 'Project path.'
        ])->addValidator(function($value) {
            return is_dir($value);
        }, 'specified path is not a directory or directory not found')
          ->addValidator(function($value) use (&$package) {
            $package_dir = rtrim($value, '/') . '/' . $package;

            return !is_dir($package_dir);
        }, 'project directory already exists');
    }

    /**
     * Helper method to test whether a file is binary or text file.
     *
     * @param   string          $file               File to test.
     * @param   string          $size               Optional block size to test.
     * @return  bool                                Returns true for binaries.
     */
    protected function isBinary($file, $size = 2048)
    {
        $return = false;

        if (is_file($file) && is_readable($file) && ($fp = fopen($file, 'r'))) {
            $blk = fread($fp, $size);
            fclose($fp);

            $return = (substr_count($blk, "\x00") > 0);
        }

        return $return;
    }

    /**
     * Run command.
     *
     * @param   array           $options                    Cli options.
     * @param   array           $operands                   Cli operands.
     */
    public function run(array $options, array $operands)
    {
        $project = $options['project'];
        $type = $options['type'];
        $dir = rtrim($operands['project-path'][0], '/');

        list($vendor, $package) = explode('/', $project);

        $year = date('Y');

        // handle project configuration
        $cfg = new \Octris\Cliconfig(['/etc']);
        $cfg->load($dir . '/.octris.ini');

        $data = [];
        $info = $cfg->addSection('info');

        if (isset($options['key-value'])) {
            foreach ($options['key-value'] as $k => $v) {
                $info[$k] = $v;
            }
        }

        foreach (self::$settings as $k) {
            $info[$k] = readline::getPrompt(
                sprintf(
                    '%s [%%s]: ',
                    $k
                ),
                (isset($info[$k]) ? $info[$k] : '')
            );

            $data[$k] = preg_replace('/<package>/', $package, $info[$k]);
        }

        print "\n";

        if ($cfg->hasChanged()) {
            do {
                $yn = readline::getPrompt('Save changed configuration? (Y/n) ', 'y');
            } while (!preg_match('/^[YyNn]$/', $yn));

            if ($yn == 'y') {
                $cfg->save();
            }

            print "\n";
        }

        // build data array
        $data = array_merge($data, array(
            'year'      => $year,
            'package'   => $package,
            'vendor'    => $vendor,
            'namespace' => ucfirst($vendor) . '\\' . ucfirst($package),
            'directory' => $vendor . '.' . $package
        ));

        // create project
        $dir .= '/' . $package;
        $src = tempnam(sys_get_temp_dir(), 'octris-') . '.phar.gz';

        // https://stackoverflow.com/questions/26148701/file-get-contents-ssl-operation-failed-with-code-1-and-more
        $context = [
            'ssl' => [
                'cafile' => __DIR__ . '/../../etc/cacert.pem',
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ];
        copy('https://github.com/octris/skel-legacy/tarball/master', $src, stream_context_create($context));

        // process skeleton and write project files
        $tpl = new \Octris\Tpl();
        $tpl->addSearchPath('phar://' . $src);
        $tpl->setValues($data);
        
        mkdir($dir, 0755);

        $directories = array();
        $iterator = new \RecursiveIteratorIterator(new \PharData($src));

        foreach ($iterator as $filename => $cur) {
            $rel   = preg_replace('/^.*?\/skeleton\//', '', $filename);
            $dst   = $dir . '/' . $rel;
            $path  = dirname($dst);
            $base  = basename($filename);
            $ext   = preg_replace('/^\.?[^\.]+?(\..+|)$/', '\1', $base);
            $base  = basename($filename, $ext);

            $sandbox = $tpl->getSandbox($rel);

            if (substr($base, 0, 1) == '$' && isset($data[$base = ltrim($base, '$')])) {
                // resolve variable in filename
                $dst = $path . '/' . $data[$base] . $ext;
            }

            if (!is_dir($path)) {
                // create destination directory
                mkdir($path, 0755, true);
            }

            if (!$this->isBinary($filename)) {
                $sandbox->save($dst, \Octris\Core\Tpl::ESC_NONE);
            } else {
                copy($filename, $dst);
            }

            chmod($dst, 0644);
        }

        // reminder
        print "Project created in '$dir'.\n\n";

        print "Next steps you should do:\n";
        print "- edit the 'composer.json' configuration located in the project directory.\n";
        print "- run 'composer update' in the project directory to load dependencies.\n\n";
    }
}
