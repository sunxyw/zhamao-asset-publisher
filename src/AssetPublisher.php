<?php

declare(strict_types=1);

namespace Sunxyw\AssetPublisher;

use ZM\Annotation\Framework\Setup;
use ZM\Plugin\PluginMeta;

class AssetPublisher
{
    #[Setup]
    public function autoPublish(): void
    {
        logger()->info('AssetPublisher is running');
    }

    public function spawnPublishCommand(): void
    {
        logger()->info('spawnPublishCommand is running');
    }

    public function publish(PluginMeta $plugin, string $type): void
    {
        dump($plugin, $type);
    }
}
