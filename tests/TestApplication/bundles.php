<?php

declare(strict_types=1);

$bundles = [
    Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class => ['all' => true],
    Knp\Bundle\GaufretteBundle\KnpGaufretteBundle::class => ['all' => true],
    Sylius\MolliePlugin\SyliusMolliePlugin::class => ['all' => true],
];

if (class_exists(Sylius\RefundPlugin\SyliusRefundPlugin::class)) {
    $bundles[Sylius\RefundPlugin\SyliusRefundPlugin::class] = ['all' => true];
}

if (class_exists(Knp\Bundle\SnappyBundle\KnpSnappyBundle::class)) {
    $bundles[Knp\Bundle\SnappyBundle\KnpSnappyBundle::class] = ['all' => true];
}

if (class_exists(winzou\Bundle\StateMachineBundle\winzouStateMachineBundle::class)) {
    $bundles[winzou\Bundle\StateMachineBundle\winzouStateMachineBundle::class] = ['all' => true];
}

return $bundles;
