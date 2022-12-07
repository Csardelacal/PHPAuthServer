<?php namespace defer;

use AndrewBreksa\RSMQ\Exceptions\QueueNotFoundException;
use AndrewBreksa\RSMQ\RSMQClient;
use JsonException;
use Serializable;

class TaskFactory
{
	
	/**
	 * 
	 * @var RSMQClient
	 */
	private $client;
	
	/**
	 * 
	 * @var string
	 */
	private $queue;
	
	public function __construct(RSMQClient $client, string $queue)
	{
		$this->client = $client;
		$this->queue = $queue;
	}
	
	/**
	 * 
	 * @param int $defer
	 * @param string $task
	 * @param Serializable|mixed[]|string|bool|int|float $settings
	 * 
	 * @throws JsonException
	 */
	public function defer(int $defer, string $task, $settings) : string
	{
		if ($defer > 86400 * 365 * 50) {
			$defer = $defer - time();
		}
		
		try {
			$this->client->getQueueAttributes($this->queue);
		}
		catch (QueueNotFoundException $e) {
			$this->client->createQueue($this->queue, 300, 0, -1);
		}
		
		$id = $this->client->sendMessage($this->queue, json_encode([
			'task' => $task,
			'settings' => $settings
		], JSON_THROW_ON_ERROR), $defer);
		
		
		return strval($id);
	}
	
}
