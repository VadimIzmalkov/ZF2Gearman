<?php 
namespace Zf2Gearman\Service;

abstract class IteratorJobService extends AbstractJobService
{	
	const LIMIT = 500;
	
	public function __construct($entityManager, $gearmanService, $moduleOptions)
	{
		parent::__construct($entityManager, $gearmanService, $moduleOptions);
	}

	abstract protected function getItems($offset);

	public function perform()
	{
		$this->gearmanService->log('Job started');
		$count = $this->iterate();
		$this->gearmanService->log('Job completed on ' . $count . ' items');
		return true;
	}

	protected function iterate()
	{
		$offset = 0;
		while (true) 
		{
			// complete iteration
			if(!$this->isAllowedNext()) 
			{
				$this->updateProgressAndPersist();
				$this->updateState(\Zf2Gearman\Entity\JobProgress::STATE_COMPLETED);

				return $this->getProgress();
			}

			// get users and continue
			// if(($this->getProgress() <= $this->getFinalProgress()) && $items = $this->getItems($offset)) 
			if($items = $this->getItems($offset))
			{
				$this->forEachItem($items);
			}
			else 
			{
				// complete iteration
				$this->updateState(\Zf2Gearman\Entity\JobProgress::STATE_COMPLETED);
				return $this->getProgress();
			}

			$this->beforeFlush();
			$this->updateProgressAndPersist();
			$this->flushAndClean();

			$offset += static::LIMIT;
		}
	}

	protected function forEachItem($items) 
	{
		foreach ($items as $item) 
		{
			if(!$this->isAllowedNext()) 
			{
				break;
			}
			$this->onItem($item);
		}
	}

	protected function onItem($item)
	{
		$this->entityManager->persist($item);
		$this->increaseProgress();
		return true;
	}
}