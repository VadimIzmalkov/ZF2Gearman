<?php
namespace Zf2Gearman\Service;

class ManagerFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
    	$entityManger 		= $serviceLocator->get(\Doctrine\ORM\EntityManager::class);
    	$workerWrapper 		= $serviceLocator->get(WorkerWrapper::class);
    	$moduleOptions 		= $serviceLocator->get(\Zf2Gearman\Options\ModuleOptions::class);

        $service = new Manager($entityManger, $workerWrapper, $moduleOptions);

        return $service;
    }
}