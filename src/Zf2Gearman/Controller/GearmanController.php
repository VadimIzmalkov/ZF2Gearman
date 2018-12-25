<?php 
namespace Zf2Gearman\Controller;

use Zend\Console\Request as ConsoleRequest;
use Zend\View\Model\JsonModel;

// use Zf2Gearman\Entity\JobDataInterface;
use Zf2Gearman\Exception\GearmanException;
use Zf2Gearman\Service\ManagerInterface as GearmanManagerInterface;

class GearmanController extends \Zend\Mvc\Controller\AbstractActionController
{
	private $entityManager;
	private $gearmanManager;

	// public function __construct($entityManager, $gearmanClient)
	public function __construct(GearmanManagerInterface $gearmanManager)
	{
		// $this->entityManager = $entityManager;
		$this->gearmanManager = $gearmanManager;
	}

	// Helpful for using in CRON or AJAX
	public function addTaskAction()
	{
		if ($this->getRequest() instanceof ConsoleRequest) {
			$force = $this->getRequest()->getParam('force', 0);
			$workloadId = $this->getRequest()->getParam('workloadid', 0);		
		} elseif($this->request->isXmlHttpRequest()) {
			$force = $this->params()->fromQuery('force', 0);
			$workloadId	= $this->params()->fromQuery('workloadid', 0);
		} elseif($this->request->isPost()) {
			$force = $this->params()->fromPost('force', 0);
			$workloadId = $this->params()->fromPost('workloadid', 0);
		} else {
			return $this->notFoundAction();
		}

		$result = $this->gearmanManager->addTask((int)$workloadId, $force);

		return new JsonModel(['result' => true]);
	}

	public function runWorkerConsoleAction()
	{
		$request = $this->getRequest();

		if (!$request instanceof ConsoleRequest) {
            throw new GearmanException('You can only use from a console');
		}

		$force 		= $request->getParam('force', false);
		$workloadId = $request->getParam('workloadid', false);

		if(!$workloadId) {
            throw new GearmanException('Gearman job data ID is missed');
        }

		$result = $this->gearmanManager->runWorker((int)$workloadId, $force);
	}
}
