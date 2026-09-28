<?php

namespace humhub\modules\todo\models;

use Yii;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\todo\notifications\TaskAssigned;
use humhub\modules\user\models\User;
use humhub\modules\activity\models\Activity;
use humhub\interfaces\ViewableInterface;
use humhub\modules\file\components\FileManager;
use humhub\modules\file\models\File;
use humhub\modules\todo\permissions\CreateTasks;
use humhub\modules\todo\permissions\EditTasks;
use humhub\modules\todo\permissions\DeleteTasks;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\todo\services\CalendarSyncService;
use humhub\modules\todo\services\TaskAuthorizationService;
use humhub\modules\todo\services\TaskHistoryService;
use humhub\modules\todo\models\TaskList;
use humhub\modules\user\helpers\UserHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class Task extends ContentActiveRecord implements ViewableInterface
{
    public $createActivityOnInsert = false;

    /** @var string HumHub permission required to create this content type. */
    protected $createPermission = CreateTasks::class;

    /** @var string HumHub permission used for managing this content type. */
    protected $managePermission = EditTasks::class;
    

    public static function getContentContainerTypes(): array
    {
        return [
            \humhub\modules\space\models\Space::class
        ];
    }

    public $wallEntryClass = \humhub\modules\todo\widgets\WallEntry::class;

    public $user_ids = [];

    /** Name of the selected/new task list, resolved by the controller per Space. */
    public $task_list_name = '';

    public $uploadFiles;


    public static function tableName(): string
    {
        return 'todo_task';
    }


    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['priority'], 'in', 'range' => ['niedrig', 'mittel', 'hoch']],
            [['status'], 'in', 'range' => ['offen', 'in_bearbeitung', 'geschlossen']],
            [['closed_by'], 'integer'],
            [['closed_at'], 'safe'],
            [['sync_to_calendar'], 'boolean'],
            [['task_list_id'], 'integer'],
            [['task_list_name'], 'string', 'max' => 100],

            [['user_ids'], 'each', 'rule' => ['string', 'max' => 36]],

            [['uploadFiles'], 'file',
                'maxFiles' => 10,
                'extensions' => ['png','jpg','jpeg','pdf','doc','docx','xlsx'],
                'skipOnEmpty' => true
            ],
        ];
    }


    public function attributeLabels()
    {
        return [
            'title' => Yii::t('TodoModule.base', 'Titel'),
            'task_list_name' => Yii::t('TodoModule.base', 'Aufgabenliste'),
            'description' => Yii::t('TodoModule.base', 'Beschreibung'),
            'priority' => Yii::t('TodoModule.base', 'Priorität'),
            'due_date' => Yii::t('TodoModule.base', 'Fällig'),
            'sync_to_calendar' => Yii::t('TodoModule.base', 'Im Kalender eintragen'),
            'user_ids' => Yii::t('TodoModule.base', 'Zuständig'),
            'status' => Yii::t('TodoModule.base', 'Status'),
            'uploadFiles' => Yii::t('TodoModule.base', 'Dateien'),
        ];
    }


    public function getClosedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'closed_by']);
    }

    /**
     * Returns the HumHub file manager for this task.
     */
    public function getFileManager(): FileManager
    {
        return new FileManager(['record' => $this]);
    }

    /**
     * Store uploaded files with HumHub and attach them to this task.
     */
    public function saveUploads(): void
    {
        if (empty($this->uploadFiles)) {
            return;
        }

        foreach ($this->uploadFiles as $uploadedFile) {
            if ($uploadedFile instanceof UploadedFile) {
                $this->saveUploadedFile($uploadedFile);
            }
        }

        $this->uploadFiles = [];
    }

    /**
     * Store one already validated upload and optionally set a human-readable title.
     */
    public function saveUploadedFile(UploadedFile $uploadedFile, ?string $title = null): bool
    {
        $file = new File();
        $file->file_name = $uploadedFile->name;
        $file->title = trim((string) $title) !== '' ? trim((string) $title) : $uploadedFile->baseName;
        $file->mime_type = FileHelper::getMimeType($uploadedFile->tempName) ?: 'application/octet-stream';
        $file->show_in_stream = 0;

        if (!$file->save()) {
            Yii::error('Could not create HumHub file record for ToDo upload: ' . implode('; ', $file->getErrorSummary(true)), __METHOD__);
            return false;
        }

        try {
            $file->setStoredFile($uploadedFile);
            $this->getFileManager()->attach($file);
            return true;
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            try {
                $file->delete();
            } catch (\Throwable $cleanupException) {
                Yii::error($cleanupException, __METHOD__);
            }
            return false;
        }
    }

    /**
     * File downloads call the attached object's visibility check.
     * Require both HumHub content visibility and the module-specific ViewTasks permission.
     */
    public function canView($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);

        if (!$this->content || !$this->content->canView($user)) {
            return false;
        }

        $container = $this->content->container;
        if (!$container) {
            return false;
        }

        return $container->getPermissionManager($user)->can(new ViewTasks());
    }

    public function isCreatedBy($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);
        return $user !== null
            && $this->content !== null
            && (int) $this->content->created_by === (int) $user->id;
    }

    public function isAssignedTo($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);
        if ($user === null || !$this->id) {
            return false;
        }

        return TaskUser::find()
            ->where(['task_id' => $this->id, 'user_id' => $user->id])
            ->exists();
    }

    /** Full task editing: managers or the person who created the task. */
    public function canManage($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);
        $container = $this->content?->container;

        return $container !== null && TaskAuthorizationService::canManage(
            $container->getPermissionManager($user)->can(new EditTasks()),
            $this->isCreatedBy($user)
        );
    }

    /** Workflow editing: full managers plus assigned people. */
    public function canWorkOn($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);
        $container = $this->content?->container;

        return $container !== null && TaskAuthorizationService::canWorkOn(
            $container->getPermissionManager($user)->can(new EditTasks()),
            $this->isCreatedBy($user),
            $this->isAssignedTo($user)
        );
    }

    /** Deleting: users with DeleteTasks or the person who created the task. */
    public function canDelete($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);
        $container = $this->content?->container;

        return $container !== null && TaskAuthorizationService::canDelete(
            $container->getPermissionManager($user)->can(new DeleteTasks()),
            $this->isCreatedBy($user)
        );
    }

    /**
     * Remove leftovers from the legacy runtime-based storage after an update.
     * New uploads never use this location.
     */
    private function deleteLegacyUploadFolder(): void
    {
        if (!$this->id) {
            return;
        }

        $path = Yii::getAlias('@runtime/todo-files/' . $this->id);
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $filePath = $path . DIRECTORY_SEPARATOR . $name;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        @rmdir($path);
    }

    public function getTaskList()
    {
        return $this->hasOne(TaskList::class, ['id' => 'task_list_id']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('todo_task_user', ['task_id' => 'id']);
    }


    public function getTaskUsers()
    {
        return $this->hasMany(TaskUser::class, ['task_id' => 'id']);
    }

    public function getChecklistItems()
    {
        return $this->hasMany(ChecklistItem::class, ['task_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getHistoryEntries()
    {
        return $this->hasMany(TaskHistory::class, ['task_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }


    public function afterFind()
    {
        parent::afterFind();

        $this->user_ids = array_map(fn($user) => $user->guid, $this->users);
        $this->task_list_name = $this->taskList ? $this->taskList->name : '';
    }

public function afterSave($insert, $changedAttributes)
{
    parent::afterSave($insert, $changedAttributes);


    /**
     * Reminder zurücksetzen wenn Fälligkeitsdatum geändert wurde
     */
    if (
        !$insert
        && array_key_exists('due_date', $changedAttributes)
        && $changedAttributes['due_date'] != $this->due_date
    ) {
        $this->reminder_sent_at = null;
        $this->updateAttributes(['reminder_sent_at']);
    }


    /**
     * Alte User sichern
     */
    $oldUserIds = [];

    if (!$insert) {
        $oldUserIds = TaskUser::find()
            ->select('user_id')
            ->where(['task_id' => $this->id])
            ->column();
    }


    /**
     * Alte Zuordnungen löschen
     */
    TaskUser::deleteAll(['task_id' => $this->id]);


    /**
     * Neue Zuordnungen speichern
     */
    $newUserIds = [];

    if (is_array($this->user_ids)) {

        foreach ($this->user_ids as $guid) {

            $user = User::find()->where(['guid' => $guid])->one();

            if ($user) {

                $rel = new TaskUser();
                $rel->task_id = $this->id;
                $rel->user_id = $user->id;
                $rel->save();

                $newUserIds[] = $user->id;
            }
        }
    }

    if ($insert) {
        TaskHistoryService::record($this, 'created', 'Aufgabe erstellt');
    } else {
        $labels = [
            'title' => 'Titel geändert',
            'description' => 'Beschreibung geändert',
            'priority' => 'Priorität geändert',
            'status' => 'Status geändert',
            'due_date' => 'Fälligkeit geändert',
            'task_list_id' => 'Aufgabenliste geändert',
            'sync_to_calendar' => 'Kalendersynchronisierung geändert',
        ];
        foreach ($labels as $attribute => $message) {
            if (array_key_exists($attribute, $changedAttributes) && $changedAttributes[$attribute] != $this->$attribute) {
                TaskHistoryService::record($this, 'task_updated', $message);
            }
        }
    }

    $addedUserIds = array_diff($newUserIds, $oldUserIds);
    $removedUserIds = array_diff($oldUserIds, $newUserIds);
    foreach (User::findAll(['id' => $addedUserIds]) as $user) {
        TaskHistoryService::record($this, 'assignee_added', $user->displayName . ' wurde zugewiesen');
    }
    foreach (User::findAll(['id' => $removedUserIds]) as $user) {
        TaskHistoryService::record($this, 'assignee_removed', $user->displayName . ' wurde entfernt');
    }


    /**
     * Statuswechsel → erledigt Notification
     */
    if (
        !$insert
        && !Yii::$app->user->isGuest
        && isset($changedAttributes['status'])
        && $changedAttributes['status'] !== 'geschlossen'
        && $this->status === 'geschlossen'
    ) {

        $users = [];
        $userIds = [];

        foreach ($this->users as $user) {

            if ($user->id == Yii::$app->user->id) {
                continue;
            }

            if (!in_array($user->id, $userIds)) {
                $users[] = $user;
                $userIds[] = $user->id;
            }
        }


		/**
		 * Creator zusätzlich informieren
		 */
		if ($this->content->created_by != Yii::$app->user->id) {
		
			$creator = $this->content->createdBy;
		
			if ($creator && !in_array($creator->id, $userIds)) {
				$users[] = $creator;
				$userIds[] = $creator->id;
			}
		}


        /**
         * Notification senden
         */
        foreach ($users as $user) {

            Yii::$app->notification->send(
				new \humhub\modules\todo\notifications\TaskCompleted([
					'originator' => Yii::$app->user->identity,
					'source' => $this
				]),
				$user
			);
        }
    }


    /**
     * Notification für neue Zuweisungen
     */
    $notifyUserIds = $insert
        ? $newUserIds
        : array_diff($newUserIds, $oldUserIds);

    foreach ($notifyUserIds as $userId) {

        if ($userId == Yii::$app->user->id) {
            continue;
        }

        $user = User::findOne($userId);

        if ($user) {

            Yii::$app->notification->send(
                new TaskAssigned([
                    'originator' => Yii::$app->user->identity,
                    'source' => $this
                ]),
                $user
            );
        }
    }


    /**
     * Uploads speichern
     */
    $this->saveUploads();

    /**
     * Optional mit dem HumHub-Kalender synchronisieren.
     */
    if (!CalendarSyncService::syncTask($this)) {
        Yii::$app->session->setFlash(
            'warning',
            Yii::t('TodoModule.base', 'Die Aufgabe wurde gespeichert, der Kalendereintrag konnte aber nicht synchronisiert werden.')
        );
    }
}



    public function beforeDelete()
    {
        // Checklist rows are removed through the database cascade. Remove their
        // linked Calendar entries first because their ActiveRecord afterDelete()
        // callbacks are not invoked by a DB-level cascade.
        foreach ($this->checklistItems as $item) {
            CalendarSyncService::deleteLinkedEntry($item);
        }

        CalendarSyncService::deleteLinkedEntry($this);

        return parent::beforeDelete();
    }

	public function afterDelete()
	{
        foreach (File::findByRecord($this) as $file) {
            $file->delete();
        }
        $this->deleteLegacyUploadFolder();

		TaskUser::deleteAll([
			'task_id' => $this->id
		]);
	
		Activity::deleteAll([
			'object_model' => self::class,
			'object_id' => $this->id
		]);
	
		parent::afterDelete();
	}

    public function getContentTitle(): string
    {
        return $this->title;
    }
    

    
    public function getUrl()
	{
		if (!$this->content || !$this->content->container) {
			return null;
		}
	
		return $this->content->container->createUrl('/todo/task/view', [
			'id' => $this->id,
		]);
	}

public function getActivityTitle()
{
    return Yii::t('TodoModule.base',
        '{user} hat ToDo "{title}" erstellt',
        [
            'user' => $this->content->createdBy->displayName,
            'title' => $this->title,
        ]
    );
}

public function getContentName(): string
{
    return Yii::t('TodoModule.base', 'ToDo');
}

public function getCreateActivityClass()
{
    return \humhub\modules\todo\activities\TaskCreated::class;
}

public function getContentDescription(): string
{
    return $this->title;
}

}
