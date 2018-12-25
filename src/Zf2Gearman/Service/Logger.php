<?php
namespace Zf2Gearman\Service;

use Zend\Log\Logger as ZendLogger;
use Zend\Log\Writer\Stream as StreamWriter;

use Zf2Gearman\Exception\GearmanException;

class Logger implements LoggerInterface
{
	const DIR_RELATIVE = '/log/gearman';

	private $enable = true;

	private $zendLogger;

	public function __construct()
	{
		$this->zendLogger = new ZendLogger;
		self::makeDirIfNotExist(self::getAbsolutePath());
	}

    public function setLogFileName($logFileName)
    {
        $logFilePath = self::getAbsolutePath().'/'.$logFileName.'_progress_'.date('d-m-Y_H-i-s').'.log';
        $stream = @fopen($logFilePath, 'w', false);

        if(!$stream) {
            throw new GearmanException('Failed to open stream'); 
        }

        $writer = new StreamWriter($stream);
        $this->zendLogger->addWriter($writer);
    }

	public function log($message)
	{
		if($this->enable) {
            $this->zendLogger->info($message);
        }
	}

    public static function getAbsolutePath() 
    { 
        $absolutePath = getcwd().self::DIR_RELATIVE;

        self::makeDirIfNotExist($absolutePath);

        return $absolutePath;
    }

    public static function makeDirIfNotExist($dir)
    {
    	if (!is_dir($dir)) {
            $oldmask = umask(0);
            if (!mkdir($dir, 0777, true))
                die('Failed to create folders' . $dir);
            umask($oldmask);
        }
    }
}