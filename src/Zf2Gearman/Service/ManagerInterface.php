<?php
namespace Zf2Gearman\Service;

use Zf2Gearman\Entity\JobDataInterface;

interface ManagerInterface
{
	public function prepareWorkloadAndGet(array $data);
	public function addTask($workload, bool $force);

	public function runWorker($workload, bool $force);
}