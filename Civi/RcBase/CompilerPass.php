<?php

namespace Civi\RcBase;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use CRM_RcBase_ExtensionUtil as E;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('action_provider')) {
            $actionProviderDefinition = $container->getDefinition('action_provider');
            $actionProviderDefinition->addMethodCall('addAction', [
                'RcBaseSetupOrganizationAndRelationship',
                'Civi\RcBase\Actions\SetupOrganizationAndRelationship',
                E::ts('Setup Organization And Relationship.'),
                [],
            ]);
        }
    }
}
