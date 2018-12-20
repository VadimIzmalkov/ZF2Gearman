<?php 
/**
 * Fryday
 *
 * @link 		https://www.fryday.net/
 * @author 		06/24/2017 Vadim Izmalkov
 */

namespace Zf2Gearman\Controller;

use Zend\Console\Request as ConsoleRequest,
	Zend\View\Model\JsonModel;

/**
 * Controls requests to queue manager Gearman
 *
 */
class IndexController extends \Zend\Mvc\Controller\AbstractActionController //\VisoftBaseModule\Controller\AbstractCrudController //\Zend\Mvc\Controller\AbstractActionController
{
	const URI_ADD_JOB = '/gearman/add-job';
	
	private $entityManager;
	private $gearmanService;

	public function __construct($entityManager, $gearmanService)
	{
		$this->entityManager = $entityManager;
		$this->gearmanService = $gearmanService;
	}

	public function addJobAction()
	{
		if ($this->getRequest() instanceof ConsoleRequest) 
		{
			$createOwnWorker 	= $this->getRequest()->getParam('createownworker', 0);
			$jobId 				= $this->getRequest()->getParam('jobid', 0);		
		} 
		elseif($this->request->isXmlHttpRequest()) 
		{
			$createOwnWorker 	= $this->params()->fromQuery('create-own-worker', 0);
			$jobId 				= $this->params()->fromQuery('job-id', 0);
		} 
		elseif($this->request->isPost()) 
		{
			$createOwnWorker 	= $this->params()->fromPost('create-own-worker', 0);
			$jobId 				= $this->params()->fromPost('job-id', 0);
		} 
		else 
		{
			return $this->notFoundAction();
		}

		$result = $this->gearmanService->addJob($jobId, $createOwnWorker);

		return new JsonModel(['result' => true]);
	}

	public function addBackgroundJobConsoleAction()
	{
		$request = $this->getRequest();

		if (!$request instanceof ConsoleRequest)
		{
            throw new \RuntimeException('You can only use from a console');
		}
        
        $jobId = $request->getParam('jobid', false);
        if(!$jobId)
        {
            throw new \RuntimeException('Gearman job id missed');
        }
        
		$job = $this->entityManager->find(\Zf2Gearman\Entity\JobParametersInterface::class, $jobId);
		if(!$job)
		{
            throw new \RuntimeException('Gearman job not exists');
		}

        $createOwnWorker = $request->getParam('createownworker', false);

		$result = $this->gearmanService->addBackgroundJob($job, $createOwnWorker);
	}

	public function testAction()
	{
		$jobId = $this->params()->fromPost('job-id');
		return new JsonModel(['job-id' => $jobId]);
	}
}