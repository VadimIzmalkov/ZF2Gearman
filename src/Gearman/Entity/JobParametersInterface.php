<?php
namespace Zf2Gearman\Entity;

interface JobParametersInterface
{
	public function __construct($type);

	public function getId();

	public function getCreatedAt();

	public function getType();

	public function getJobProgress();
	public function setJobProgress(\Zf2Gearman\Entity\JobProgress $jobProgress);

    public function getParentJobParameters();
    public function setParentJobParameters(\Zf2Gearman\Entity\JobParametersInterface $parentJobParameters);

    public function getChildJobsParameters();
}