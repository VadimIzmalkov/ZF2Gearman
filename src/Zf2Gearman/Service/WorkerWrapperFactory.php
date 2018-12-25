<?php
namespace Zf2Gearman\Service;

class WorkerWrapperFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
    	$entityManger 		= $serviceLocator->get(\Doctrine\ORM\EntityManager::class);
    	$moduleOptions 		= $serviceLocator->get(\Zf2Gearman\Options\ModuleOptions::class);

        $service = new WorkerWrapper($entityManger, $serviceLocator, $moduleOptions);

        return $service;
    }
}