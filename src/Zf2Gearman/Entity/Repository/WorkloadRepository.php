<?php
namespace Zf2Gearman\Entity\Repository;

use Zf2Gearman\Entity\WorkloadInterface;

class WorkloadRepository extends \Doctrine\ORM\EntityRepository
{
	public function createAndGet(array $data)
	{
        // for unknown reason entity manager appears to be closed after flushing 
        // Due to interface flush is not working??
        // TODO: investigate and fix
        // $em = $this->createNewEntityManager();
        $em = $this->getEntityManager();

		// create entity from interface
		$workloadEntityInfo = $em->getClassMetadata(WorkloadInterface::class);
        $workloadClass = $workloadEntityInfo->name;
		$workload = new $workloadClass;

        // fill entity
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);
            if(is_callable(array($this, $method))){
                $workload->$method($value);
            }
        }

        $em->persist($workload);
        // $em->flush();

        return $workload;
	}

    protected function createNewEntityManager() {

        $em = $this->getEntityManager();
        return $em->create(
            $em->getConnection(),
            $em->getConfiguration(),
            $em->getEventManager()
        );
    }
}