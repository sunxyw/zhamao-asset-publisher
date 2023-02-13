<?php

namespace Sunxyw\AssetPublisher\Commands;

use Sunxyw\AssetPublisher\AssetPublisher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use ZM\Command\Command;
use ZM\Plugin\PluginManager;
use ZM\Plugin\PluginMeta;
use ZM\Store\FileSystem;

#[AsCommand(name: 'asset:publish', description: 'Publish assets')]
class AssetPublishCommand extends Command
{
    private array $plugins;

    private static array $asset_types = [
        'config',
        'commands',
    ];

    protected function configure()
    {
        $this->addArgument('plugin', InputArgument::OPTIONAL, 'Plugin name');
        $this->addArgument('type', InputArgument::OPTIONAL, 'Asset type');
    }

    protected function handle(): int
    {
        $plugin = $this->input->getArgument('plugin');
        $type = $this->input->getArgument('type');
        $this->loadPluginList();

        if ($plugin === null) {
            $plugin = $this->selectPlugin();
        } else {
            $plugin = $this->plugins[$plugin] ?? null;
            if ($plugin === null) {
                logger()->error('Plugin not found');
                return 1;
            }
        }

        if ($type === null) {
            $type = $this->selectAssetType();
        } elseif (!in_array($type, self::$asset_types, true)) {
            logger()->error('Invalid asset type');
            return 1;
        }

        (new AssetPublisher())->publish($plugin, $type);
        logger()->info('Publish success');

        return 0;
    }

    private function loadPluginList(): void
    {
        $load_dir = config('global.plugin.load_dir');
        if (empty($load_dir)) {
            $load_dir = WORKING_DIR . '/plugins';
        } elseif (FileSystem::isRelativePath($load_dir)) {
            $load_dir = WORKING_DIR . '/' . $load_dir;
        }
        $load_dir = zm_dir($load_dir);
        PluginManager::addPluginsFromDir($load_dir);

        // 从 composer 依赖加载插件
        if (config('global.plugin.composer_plugin_enable', true)) {
            PluginManager::addPluginsFromComposer();
        }

        // get static property plugins from PluginManager by reflection
        $reflection = new \ReflectionClass(PluginManager::class);
        $property = $reflection->getProperty('plugins');
        $property->setAccessible(true);
        $this->plugins = $property->getValue();
    }

    private function selectPlugin(): PluginMeta
    {
        $plugin_names = array_keys($this->plugins);
        $question = new ChoiceQuestion('Please select a plugin', $plugin_names);
        $plugin_name = $this->getHelper('question')->ask($this->input, $this->output, $question);
        return $this->plugins[$plugin_name];
    }

    private function selectAssetType(): string
    {
        $question = new ChoiceQuestion('Please select an asset type', self::$asset_types);
        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }
}
