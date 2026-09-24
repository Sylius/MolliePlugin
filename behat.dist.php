<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Profile;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use DMore\ChromeExtension\Behat\ServiceContainer\ChromeExtension;
use FriendsOfBehat\MinkDebugExtension\ServiceContainer\MinkDebugExtension;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;
use FriendsOfBehat\VariadicExtension\ServiceContainer\VariadicExtension;

$syliusSuitesPath = 'vendor/sylius/sylius/src/Sylius/Behat/Resources/config/suites';

return (new Config())
    ->import([
        // Sylius 2.3 ships its suites as a PHP config, earlier versions ship YAML.
        is_file(__DIR__ . '/' . $syliusSuitesPath . '.php') ? $syliusSuitesPath . '.php' : $syliusSuitesPath . '.yml',
        'tests/Behat/Resources/suites.php',
    ])
    ->withProfile(
        (new Profile('default'))
        ->withFormatter(new PrettyFormatter(paths: false, verbose: true, snippets: false))
        // CLI is excluded as it registers an error handler that mutes fatal errors
        ->withFilter(new TagFilter('~@todo&&~@cli'))
        ->withExtension(new Extension(ChromeExtension::class))
        ->withExtension(new Extension(MinkDebugExtension::class, [
            'directory' => 'etc/build',
            'clean_start' => false,
            'screenshot' => true,
        ]))
        ->withExtension(new Extension(MinkExtension::class, [
            'files_path' => '%paths.base%/vendor/sylius/sylius/src/Sylius/Behat/Resources/fixtures/',
            'base_url' => 'http://127.0.0.1:8080/',
            'default_session' => 'symfony',
            'javascript_session' => 'chromedriver',
            'sessions' => [
                'symfony' => [
                    'symfony' => null,
                ],
                'chromedriver' => [
                    'chrome' => [
                        'api_url' => 'http://127.0.0.1:9222',
                        'validate_certificate' => false,
                    ],
                ],
                'chrome_headless_second_session' => [
                    'chrome' => [
                        'api_url' => 'http://127.0.0.1:9222',
                        'validate_certificate' => false,
                    ],
                ],
            ],
            'show_auto' => false,
        ]))
        ->withExtension(new Extension(SymfonyExtension::class, [
            'bootstrap' => 'vendor/sylius/test-application/config/bootstrap.php',
            'kernel' => [
                'class' => 'Sylius\TestApplication\Kernel',
            ],
        ]))
        ->withExtension(new Extension(VariadicExtension::class)),
    )
;
