<?php 
namespace Zf2Gearman\Service;

abstract class AbstractJobService
{
	const FINAL_PROGRESS = 'default';

	protected $entityManager;
	protected $gearmanService;	

	protected $moduleOptions;

	private $jobProgressRepository; 
	private $jobParametersRepository; 	

	private $jobParametersId 	= null; 	// stores jobs data (newsletters, events, ips, clicks, whatever))
	private $jobProgressId		= null;		// stores progress status of the job (for IPC)

	private $jobParameters 		= null; 	// stores jobs data (newsletters, events, ips, clicks, whatever))
	private $jobProgress		= null;		// stores progress status of the job (for IPC)

	private $finalProgress 		= null;
	private $progress 			= 0;

	private $allowNext 			= true;

	abstract public function perform();

	protected function beforeFlush() { return true; }
	protected function init() { return true; }

	public function __construct($entityManager, $gearmanService, $moduleOptions)
	{
		$this->gearmanService 	= $gearmanService;
		$this->entityManager 	= $entityManager;

		$this->moduleOptions 	= $moduleOptions;

		$this->jobProgressRepository 		= $this->entityManager->getRepository(\Zf2Gearman\Entity\JobProgress::class);
		$this->jobParametersRepository 		= $this->entityManager->getRepository(\Zf2Gearman\Entity\JobParametersInterface::class);
	}

	public function reset(\Zf2Gearman\Entity\JobParametersInterface $jobParameters)
	{
		$jobProgress = $jobParameters->getJobProgress();
		
		$this->jobParameters = $jobParameters;
		$this->jobProgressId = $jobProgress->getId();

		$jobProgress->setInitialProgress(0);
		$jobProgress->setFinalProgress($this->finalProgress);
		$this->persistAndFlush($jobProgress);

		$this->progress 		= 0;
		$this->allowNext 		= true;

		$result = $this->init();

		$this->updateState(\Zf2Gearman\Entity\JobProgress::STATE_RUNNING);
	}

	protected function updateState($state, $message = null)
	{
		$jobProgress = $this->getJobProgress();
		$jobProgress->setState($state);

		if($state === \Zf2Gearman\Entity\JobProgress::STATE_RUNNING)
		{
			$jobProgress->setStartedAt(new \DateTime());
		}

		if($state === \Zf2Gearman\Entity\JobProgress::STATE_COMPLETED || $state === \Zf2Gearman\Entity\JobProgress::STATE_FAILED) 
		{
			$jobProgress->setCurrentProgress($this->progress);
			$jobProgress->setFinishedAt(new \DateTime());
		}

		if(!is_null($message))
		{
			$jobProgress->setError($jobProgress->getError() . "\n" . $message);
		}

		$this->persistAndFlush($jobProgress);
	}

	protected function getProgress() { return $this->progress; }
	protected function resetProgress() { $this->progress = 0;	}

	static public function calculateProgressPercent($jobProgress)
	{
		$currentProgress 	= $jobProgress->getCurrentProgress();
		$finalProgress 		= $jobProgress->getFinalProgress();

		return (float)$currentProgress / (float)$finalProgress * 100;
	}

	protected function updateProgressAndPersist()
	{
		$jobProgress = $this->getJobProgress();
		$jobProgress->setCurrentProgress($this->progress);
		$this->entityManager->persist($jobProgress);
	}

	protected function updateProgressAndFlush()
	{
		$jobProgress = $this->getJobProgress();
		$jobProgress->setCurrentProgress($this->progress);
		$this->persistAndFlush($jobProgress);
	}

	protected function increaseProgress()
	{
		$this->progress++; 

		return $this->progress;
	}

	protected function persistAndFlush($entity)
	{
		if(is_array($entity))
		{
			foreach ($entity as $e) 
			{
				$this->entityManager->persist($e);
			}
		}
		else
		{
			$this->entityManager->persist($entity);
		}

		$this->entityManager->flush();
	}

	protected function flushAndClean()
	{
		$this->gearmanService->log('flushing');
		$this->entityManager->flush();
		$this->entityManager->clear();
	}

	protected function getJobProgress() 
	{ 
		// IMPORTANT: because of using clean() (in $this->flushAndClean()) 
		// we need get access to JobProgress via ID (every time SELECT on each method calling), 
		// in otherwhise new entity JobProgress will be created after cleaning
		 
		return $this->jobProgressRepository->findOneBy(['id' => $this->jobProgressId]); 
		// return $this->jobParameters->getJobProgress();
	}

	protected function getJobParameters() 
	{ 
		return $this->jobParameters; 
		// return $this->jobParametersRepository->findOneBy(['id' => $this->jobParametersId]); 
	}

	protected function isAllowedNext() { return $this->allowNext; }
	protected function stopIteration() { $this->allowNext = false; }
	protected function getFinalProgress() { return $this->finalProgress; }
	protected function setFinalProgress($finalProgress) { $this->finalProgress = $finalProgress; }
}