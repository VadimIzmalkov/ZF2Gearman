<?php
namespace Zf2Gearman\Controller;

class IndexControllerFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator 	= $serviceLocator->getServiceLocator();

        $entityManager 	= $parentLocator->get(\Doctrine\ORM\EntityManager::class);
        $gearmanService = $parentLocator->get(\Zf2Gearman\Service\GearmanService::class);

        $controller = new IndexController($entityManager, $gearmanService);

        return $controller;
    }
}