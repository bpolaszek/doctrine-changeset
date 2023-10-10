<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use function dirname;

/**
 * @codeCoverageIgnore
 */
final class DoctrineChangeSetExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator([dirname(__DIR__).'/Resources/config/']));
        $loader->load('services.xml');
    }
}
