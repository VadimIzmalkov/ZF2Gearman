<?php

namespace Zf2Gearman\Options;

class ModuleOptionsFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $options = new ModuleOptions(isset($config['gearman']) ? $config['gearman'] : []);
        
        return $options;
    }
}