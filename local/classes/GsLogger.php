<?
class GsLogger
{
	public $errors = array();
	public $events = array();

	private $levels = array(0=>'DBG', 1=>'INF', 2=>'WRN', 3=>'ERR');	
	private $last_time=0;

	function __construct() 
	{
		$this->last_time=microtime(true);
	}

	private function ToString($obj)
	{
		if(is_object($obj))
		{
			return print_r($obj, true);
		}
		if(is_array($obj))
		{
			return print_r($obj, true);
		}
		return strval($obj);
	}
	
	public function Debug($text)
	{
		$time = microtime(true);
		$text=$this->ToString($text);
		$this->events[] = array('time'=>$time, 'elapsed'=>$time-$this->last_time, 'level'=>0, 'text'=>$text);
		$this->last_time = microtime(true);
	}
	
	public function Info($text)
	{
		$time = microtime(true);
		$text=$this->ToString($text);
		$this->events[] = array('time'=>$time, 'elapsed'=>$time-$this->last_time,'level'=>1, 'text'=>$text);
		$this->last_time = microtime(true);
	}
	
	public function Warn($text)
	{
		$time = microtime(true);
		$text=$this->ToString($text);
		$this->events[] = array('time'=>$time, 'elapsed'=>$time-$this->last_time,'level'=>2, 'text'=>$text);
		$this->last_time = microtime(true);
	}
	
	public function Error($text)
	{
		$time = microtime(true);
		$text=$this->ToString($text);
		$this->errors[] = array('time'=>$time, 'text'=>$text);
		$this->events[] = array('time'=>$time, 'elapsed'=>$time-$this->last_time, 'level'=>3, 'text'=>$text);
		$this->last_time = microtime(true);
	}

	public function HasErrors()
	{
		$result = count($this->errors)>0;
		return $result;
	}
	
	public function ToHtml($minlevel = 1)
	{
		print "<style>
		.logger-level-0
		{
			color:#777;
		}
		.logger-level-2
		{
			font-weight:bold;
		}
		.logger-level-3
		{
			color:red;
			font-weight:bold;
		}
		</style>";
		print '<div>';
		print '<div><h5>Журнал событий:</h5></div>';
		foreach($this->events as $event)
		{
			if($event['level']>=$minlevel)
			{
				print '<div class="logger-level-'.$event['level'].'"><span>'.number_format($event['elapsed'], 3, ".", "").'</span>&nbsp;&nbsp;--&nbsp;&nbsp;<span>'.$event['text'].'</span></div>';
			}
		}
		print '</div>';
	}
	
	public function Dump()
	{
		var_dump('ERRORS:');
		var_dump($this->errors);
		var_dump('EVENTS:');
		var_dump($this->events);
	}

	public function ToText()
	{
		$result = "";
		foreach($this->events as $event)
		{
			$result.='['.$this->levels[$event['level']].'] - '.$event['text'].PHP_EOL;
		}
		return $result;
	}

	public function ToFile($dir, $name="log", $timestamp=true)
	{
		$dir = $_SERVER['DOCUMENT_ROOT'].$dir;
		if(is_dir($dir))
		{
			$filename = $name;
			if($timestamp) 
			{
				$now = new \DateTime();
				$filename = $filename.'_'.$now->format('YmdHis');
			}
			$filename = $filename.'.log';
			file_put_contents($dir.$filename, $this->ToText(), FILE_APPEND);

			return true;
		}
		return false;
	}
	
	public function ToLogger(&$anotherLogger)
	{
		$anotherLogger->errors = array_merge($anotherLogger->errors, $this->errors);
		$anotherLogger->events = array_merge($anotherLogger->events, $this->events);
	}
}
?>