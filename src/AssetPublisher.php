<?php

declare(strict_types=1);

namespace Sunxyw\AssetPublisher;

use ZM\Annotation\Framework\Setup;
use ZM\Plugin\PluginMeta;
use ZM\Store\FileSystem;

class AssetPublisher
{
    #[Setup]
    public function autoPublish(): void
    {
        logger()->info('AssetPublisher is running');
    }

    public function spawnPublishCommand(): void
    {
        $this->copyFile(__DIR__ . '/Commands/AssetPublishCommand.php', 'src/Commands/AssetPublishCommand.php');
    }

    public function publish(PluginMeta $plugin, string $type): void
    {
        dump($plugin, $type);
    }

    private function copyFile(string $source, string $target): void
    {
        // target is based on SOURCE_ROOT_DIR
        $target = SOURCE_ROOT_DIR . '/' . $target;
        FileSystem::createDir(dirname($target));
        FileSystem::ensureFileWritable($target);
        copy($source, $target);
    }
}
