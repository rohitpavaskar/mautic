<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 */

namespace MauticPlugin\MauticIntegrationsBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class PluginConfiguration implements ConfigurationInterface
{
    // This is setup to validate plugin's composer.json
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('');

        $rootNode
            ->children()
                ->scalarNode('name')->cannotBeEmpty()->end()
                ->scalarNode('description')->cannotBeEmpty()->end()
                ->scalarNode('author')->cannotBeEmpty()->end()
                ->scalarNode('version')
                    ->validate()
                        ->ifTrue(function ($s) {
                            return preg_match('#[0-9]+\.[0-9]+(\.[0-9]+)?#', $s) !== 1;
                        })
                        ->thenInvalid('Invalid version definition')->end()
                    ->end()
                ->scalarNode('requires')->end()
                ->enumNode('type')->values(['mautic-plugin'])->end()
                ->scalarNode('license')->cannotBeEmpty()->end()
                ->scalarNode('icon')->cannotBeEmpty()->end()
                ->scalarNode('minimum_stability')->cannotBeEmpty()->end()
                ->arrayNode('authors')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('email')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('require')
                        ->requiresAtLeastOneElement()
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
