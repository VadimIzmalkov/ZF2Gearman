<?php

namespace Zf2Gearman\Options;

class ModuleOptionsFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $options = new ModuleOptions(isset($config['zf2gearman']) ? $config['zf2gearman'] : []);
        
        return $options;
    }
}