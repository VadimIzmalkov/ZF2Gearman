<?php
namespace Zf2Gearman\Entity\Repository;

use Zf2Gearman\Entity\WorkerState;

class WorkerStateRepository extends \Doctrine\ORM\EntityRepository
{
	public function create(array $data, $flush = true)
	{
        $workerState = new WorkerState;
        $workerState->setWorkload($data['workload']);
        $workerState->setHost($data['host']);
        
        $this->getEntityManager()->persist($workerState);

        $this->getEntityManager()->flush();
    }
}