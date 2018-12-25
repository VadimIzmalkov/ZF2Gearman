<?php
namespace Zf2Gearman\Service;

use Zf2Gearman\Entity\JobDataInterface;

interface WorkerWrapperInterface
{
	public function runNewWorker(string $functionName);
}