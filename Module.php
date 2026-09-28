<?php

namespace humhub\modules\todo;

use Yii;
use yii\base\Event;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Menu;
use humhub\modules\ui\menu\MenuLink;
use humhub\helpers\ControllerHelper;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\todo\search\TaskSearchProvider;

class Module extends ContentContainerModule
{
    public $id = 'todo';

    public $controllerNamespace = 'humhub\modules\todo\controllers';


public function init()
{
    parent::init();

    Yii::setAlias('@todo', __DIR__);

    $translations = ['TodoModule.*' => [
        'class' => \yii\i18n\PhpMessageSource::class,
        'basePath' => '@todo/messages',
        'sourceLanguage' => 'de',
        'fileMap' => [
            'TodoModule.base' => 'base.php',
        ],
    ]] + Yii::$app->i18n->translations;
    Yii::$app->i18n->translations = $translations;

    Event::on(Menu::class, Menu::EVENT_INIT, function ($event) {

        $menu = $event->sender;
        $space = $menu->space;

        if (
            !$space ||
            !$space->moduleManager->isEnabled('todo') ||
            !$space->permissionManager->can(new ViewTasks())
        ) {
            return;
        }


			// Überfällige eigene Tasks prüfen
			$overdueMyTasks = Task::find()
				->contentContainer($space)
				->joinWith('taskUsers')
				->andWhere(['todo_task_user.user_id' => Yii::$app->user->id])
				->andWhere(['!=', 'todo_task.status', 'geschlossen'])
				->andWhere(['todo_task.deleted_at' => null, 'todo_task.archived_at' => null])
				->andWhere(['<', 'todo_task.due_date', date('Y-m-d')])
				->count();
			
			
			// Icon bestimmen (nur eigene überfällige Tasks markieren)
			$icon = $overdueMyTasks > 0
				? 'exclamation-circle'
				: 'check-square';
			
			
			// Menüeintrag setzen
			$menu->addEntry(new MenuLink([
				'label' => 'ToDo',
				'url' => $space->createUrl('/todo/task/index'),
				'icon' => $icon,
				'htmlOptions' => $overdueMyTasks > 0 ? ['class' => 'text-danger'] : [],
				'sortOrder' => 300,
				'isActive' => ControllerHelper::isActivePath('todo', 'task'),
			]));
        });
    }


    public function getConfigUrl()
    {
        return ['/todo/config/index'];
    }


    public function getContentContainerConfigUrl(ContentContainerActiveRecord $container)
    {
        return $container->createUrl('/todo/space-config/index');
    }


    public static function getSearchProviders()
    {
        return [
            TaskSearchProvider::class,
        ];
    }


    public function getContentContainerTypes()
    {
        return [Space::class];
    }


    public function getContentContainerName(ContentContainerActiveRecord $container)
    {
        return 'ToDo';
    }


    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        return 'Pendenzenliste im Space';
    }


    public function getMigrationNamespace()
    {
        return 'humhub\modules\todo\migrations';
    }


    public function getContainerPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof Space) {
            return [
                new \humhub\modules\todo\permissions\ViewTasks(),
                new \humhub\modules\todo\permissions\CreateTasks(),
                new \humhub\modules\todo\permissions\EditTasks(),
                new \humhub\modules\todo\permissions\DeleteTasks(),
            ];
        }

        return [];
    }
}
