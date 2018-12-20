<?php
namespace Zf2Gearman\Service;

class GearmanServiceFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $service = new GearmanService($serviceLocator);
        return $service;
    }
}