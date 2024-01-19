<?php
/**
 * @package    Kick Manage Article
 *
 * @author     Kicktemp GmbH <hello@kicktemp.com>
 * @copyright  Copyright © 2022 Kicktemp GmbH. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://kicktemp.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Content\Site\Helper\QueryHelper;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Log\Log;

class plgTaskKickManageArticle extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 *
	 * @since 1.0.0
	 */
	protected const TASKS_MAP = [
		'kickmanagearticle.move' => [
			'langConstPrefix' => 'PLG_TASK_KICKMANAGEARTICLE_MOVE',
			'form'            => 'move',
			'method'          => 'moveArticle',
		],
	];

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler',
			'onContentPrepareForm' => 'enhanceTaskItemForm',
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 *
	 * @throws RuntimeException
	 * @throws LogicException
	 * @since 4.1.0
	 */
	protected function moveArticle(ExecuteTaskEvent $event): int
	{
		$params  = $event->getArgument('params');
		$toCatid = (int) $params->toCatid;
		$cf = (int) $params->customField;

		/** @var DatabaseDriver $db */
		$db            = Factory::getContainer()->get('DatabaseDriver');
		$query         = $db->getQuery(true);
		$now           = Factory::getDate('now', 'GMT');
		
		$query->select($db->quoteName('a.id'))
			->from($db->quoteName('#__content', 'a'))
			->join('INNER', $db->quoteName('#__fields_values', 'fv') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('fv.item_id') . ')')
			->where($db->quoteName('a.catid') . ' = :categoryId')
			->where($db->quoteName('fv.field_id') . ' = ' . $cf ) // the condition custom field
			->where('STR_TO_DATE(' . $db->quoteName('fv.value') . ', \'%d.%m.%Y %H:%i\') < :now') // Convert the date format
			->bind(':categoryId', $params->fromCatid)
			->bind(':now', $now->toSql());
		
		$db->setQuery($query);
		$pks = $db->loadColumn();

		//$toLog = json_encode($pks);
		//Log::add($toLog, Log::INFO, 'task');


		if (count($pks) && $toCatid)
		{
			// Remove zero values resulting from input filter
			$pks = array_filter($pks);

			// SQL UPDATE query to move the articles  
			$updateQuery = $db->getQuery(true);  
			$updateQuery->update($db->quoteName('#__content'))  
				->set($db->quoteName('catid') . ' = ' . $toCatid)  
				->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');  
	
			try {  
				$db->setQuery($updateQuery);  
				$db->execute();  
			} catch (\RuntimeException $e) {  
				$this->logTask($e->getMessage(), 'error');  
				return TaskStatus::NO_RUN;  
			}  	


		}

		return TaskStatus::OK;
	}
}
