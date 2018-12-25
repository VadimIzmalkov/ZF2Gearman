<?php
namespace Zf2Gearman\Entity;

interface WorkloadInterface
{
	public function __construct();

	public function getId();

	public function getJobClass();
	public function setJobClass($state);

	public function getWorkerState();
	public function setWorkerState(WorkerState $state);

	public function getCreatedAt();	
}