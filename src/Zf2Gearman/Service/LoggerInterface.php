<?php
namespace Zf2Gearman\Service;

use Zf2Gearman\Entity\JobDataInterface;

interface LoggerInterface
{
	public function log($message);
}