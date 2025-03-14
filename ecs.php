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

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    $config->parallel();
    $config->paths([
        'src',
        'tests/Behat',
        'tests/PHPUnit',
        'tests/Application/src',
    ]);
    $config->ruleWithConfiguration(
        HeaderCommentFixer::class,
        [
            'location' => 'after_open',
            'comment_type' => HeaderCommentFixer::HEADER_COMMENT,
            'header' => <<<TEXT
This file is part of the Sylius Mollie Plugin package.
 
(c) Sylius Sp. z o.o.
 
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
TEXT
        ]
    );
};
