<?php

namespace humhub\modules\todo\controllers;

use Yii;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\models\ChecklistItem;
use humhub\modules\todo\models\TaskList;
use humhub\modules\todo\models\TaskDependency;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\todo\permissions\CreateTasks;
use humhub\modules\todo\permissions\EditTasks;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use humhub\modules\file\models\File;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\User;
use humhub\modules\todo\services\CalendarSyncService;
use humhub\modules\todo\services\TaskHistoryService;
use humhub\modules\todo\services\TaskDependencyService;
use humhub\modules\todo\services\TaskDuplicationService;

class TaskController extends ContentContainerController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
                'delete-file' => ['POST'],
                'update-file-title' => ['POST'],
                'upload-file' => ['POST'],
                'checklist-add' => ['POST'],
                'checklist-toggle' => ['POST'],
                'checklist-move' => ['POST'],
                'checklist-delete' => ['POST'],
                'checklist-edit' => ['POST'],
                'change-status' => ['POST'],
                'kanban-status' => ['POST'],
                'quick-update' => ['POST'],
                'dependency-add' => ['POST'],
                'dependency-remove' => ['POST'],
                'duplicate' => ['POST'],
            ],
        ];

        return $behaviors;
    }

public function actionIndex()
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }

    // 🔐 Zugriff prüfen (nur Anzeige-Recht nötig)
    if (!$this->contentContainer->permissionManager->can(new ViewTasks())) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $viewMode = (string) Yii::$app->request->get('view', '');
    $viewSettings = Yii::$app->getModule('todo')->settings->contentContainer(Yii::$app->user->identity);
    if (in_array($viewMode, ['list', 'kanban'], true)) {
        $viewSettings->set('taskViewMode', $viewMode);
    } else {
        $viewMode = (string) $viewSettings->get('taskViewMode', 'list');
    }
    if (!in_array($viewMode, ['list', 'kanban'], true)) {
        $viewMode = 'list';
    }

    $query = Task::find()
        ->contentContainer($this->contentContainer)
        ->andWhere(['todo_task.parent_task_id' => null]);

    // Geschlossene Aufgaben werden separat angezeigt.
    $done = Yii::$app->request->get('done');

    if ($viewMode === 'kanban') {
        // The board displays every workflow state side by side.
    } elseif ($done) {
        $query->andWhere(['todo_task.status' => 'geschlossen']);
    } else {
        $query->andWhere(['todo_task.status' => ['offen', 'in_bearbeitung']]);
    }

    // 🔹 Filter "Meine Tasks"
    if (Yii::$app->request->get('my')) {

        $userId = Yii::$app->user->id;

        $query->joinWith('taskUsers')
            ->andWhere(['todo_task_user.user_id' => $userId]);
    }

    $priority = (string) Yii::$app->request->get('priority', '');
    if (in_array($priority, ['niedrig', 'mittel', 'hoch'], true)) {
        $query->andWhere(['todo_task.priority' => $priority]);
    }

    $taskListId = (int) Yii::$app->request->get('list_id', 0);
    if ($taskListId > 0) {
        $query->andWhere(['todo_task.task_list_id' => $taskListId]);
    }

    $assigneeId = (int) Yii::$app->request->get('assignee_id', 0);
    if ($assigneeId > 0) {
        $query->joinWith('taskUsers')
            ->andWhere(['todo_task_user.user_id' => $assigneeId]);
    }

    // 🔽 SORTIERUNG
    $groupBy = Yii::$app->request->get('group', 'list');

    $sort = Yii::$app->request->get('sort');
    $dir = Yii::$app->request->get('dir') === 'desc' ? SORT_DESC : SORT_ASC;

    if ($sort === 'due_date') {

        $query->orderBy([
            'todo_task.due_date' => $dir,
        ]);

    } elseif ($done) {

        // erledigte Tasks: neueste zuerst
        $query->orderBy([
            'todo_task.updated_at' => SORT_DESC,
            'todo_task.created_at' => SORT_DESC,
        ]);

    } else {

        // 1️⃣ überfällige offene Tasks zuerst
        $query->addOrderBy([
            new \yii\db\Expression("
                CASE 
                    WHEN todo_task.status != 'geschlossen'
                    AND todo_task.due_date IS NOT NULL
                    AND todo_task.due_date < CURDATE()
                    THEN 0 ELSE 1
                END
            "),
        ]);

        // 2️⃣ Tasks ohne Datum nach unten
        $query->addOrderBy([
            new \yii\db\Expression("
                CASE 
                    WHEN todo_task.due_date IS NULL
                    THEN 1 ELSE 0
                END
            "),
        ]);

        // 3️⃣ Datum aufsteigend
        $query->addOrderBy([
            'todo_task.due_date' => SORT_ASC,
        ]);

        // 4️⃣ neueste zuerst innerhalb gleicher Gruppen
        $query->addOrderBy([
            'todo_task.created_at' => SORT_DESC,
        ]);
    }

    $pagination = new Pagination([
        'totalCount' => (clone $query)->count(),
        'pageSize' => $viewMode === 'kanban' ? 100 : 25,
        'pageSizeLimit' => [1, 100],
    ]);

    $tasks = $query
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();

    // 🔹 Gruppierung nach Benutzer
    $tasksByUser = [];

    foreach ($tasks as $task) {

        if (empty($task->users)) {

            $tasksByUser['Nicht zugewiesen'][] = $task;
            continue;
        }

        foreach ($task->users as $user) {

            $tasksByUser[$user->displayName][] = $task;
        }
    }

    ksort($tasksByUser);

    $tasksByList = [];
    foreach ($tasks as $task) {
        $key = $task->taskList ? (string) $task->taskList->id : 'unsorted';
        if (!isset($tasksByList[$key])) {
            $tasksByList[$key] = [
                'list' => $task->taskList,
                'tasks' => [],
            ];
        }
        $tasksByList[$key]['tasks'][] = $task;
    }

    // Keep configured list order, including empty lists so a + button can still be used.
    $orderedTasksByList = [];
    foreach (TaskList::findForSpace((int) $this->contentContainer->id) as $list) {
        $key = (string) $list->id;
        $orderedTasksByList[$key] = $tasksByList[$key] ?? ['list' => $list, 'tasks' => []];
    }
    if (isset($tasksByList['unsorted']) || empty($orderedTasksByList)) {
        $orderedTasksByList = ['unsorted' => $tasksByList['unsorted'] ?? ['list' => null, 'tasks' => []]] + $orderedTasksByList;
    }

    return $this->render('index', [
        'tasks' => $tasks,
        'tasksByUser' => $tasksByUser,
        'tasksByList' => $orderedTasksByList,
        'groupBy' => $groupBy,
        'contentContainer' => $this->contentContainer,
        'pagination' => $pagination,
        'viewMode' => $viewMode,
        'taskLists' => TaskList::findForSpace((int) $this->contentContainer->id),
        'spaceUsers' => Membership::getSpaceMembersQuery($this->contentContainer)->all(),
    ]);
}

public function actionCreate()
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }

    // 🔐 Permission prüfen
    if (!$this->contentContainer->permissionManager->can(new CreateTasks())) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $model = new Task();

    $model->content->container = $this->contentContainer;

    $parentTask = null;
    $parentId = (int) Yii::$app->request->get('parent_id');
    if ($parentId > 0) {
        $parentTask = Task::find()
            ->contentContainer($this->contentContainer)
            ->andWhere(['todo_task.id' => $parentId])
            ->one();
        if (!$parentTask) {
            throw new NotFoundHttpException('Hauptaufgabe nicht gefunden.');
        }
        $model->parent_task_id = $parentTask->id;
        $model->task_list_id = $parentTask->task_list_id;
        $model->task_list_name = $parentTask->taskList ? $parentTask->taskList->name : '';
    }

    $prefillListId = (int) Yii::$app->request->get('list_id');
    if ($prefillListId > 0) {
        $prefillList = TaskList::findOne(['id' => $prefillListId, 'space_id' => $this->contentContainer->id]);
        if ($prefillList) {
            $model->task_list_name = $prefillList->name;
        }
    }

    if ($model->load(Yii::$app->request->post())) {

        if ($model->parent_task_id) {
            $parentTask = Task::find()
                ->contentContainer($this->contentContainer)
                ->andWhere(['todo_task.id' => (int) $model->parent_task_id])
                ->one();
            if (!$parentTask) {
                throw new HttpException(400, 'Ungültige Hauptaufgabe.');
            }
        }

        $this->resolveTaskList($model);

        // Uploads VOR save()
        $model->uploadFiles = UploadedFile::getInstances($model, 'uploadFiles');

        if ($model->save()) {

            Yii::$app->session->setFlash(
                'success',
                Yii::t('TodoModule.base', 'Aufgabe wurde erfolgreich erstellt.')
            );

            return $this->htmlRedirect(
                $this->contentContainer->createUrl('/todo/task/view', [
                    'id' => $model->id
                ])
            );
        }
    }

    return $this->render('create', [
        'model' => $model,
        'contentContainer' => $this->contentContainer,
        'parentTask' => $parentTask,
    ]);
}

public function actionUpdate($id)
{

	if (!$this->contentContainer) {
		throw new HttpException(404, 'Kein Space gefunden.');
	}
	
    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->where(['todo_task.id' => $id])
        ->one();

    if (!$model) {
        throw new HttpException(404, 'Task nicht gefunden.');
    }

    if (!$model->canManage()) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $oldStatus = $model->status;

    if ($model->load(Yii::$app->request->post())) {

        if ($model->status === 'geschlossen' && $oldStatus !== 'geschlossen') {
            $openChecklistCount = ChecklistItem::find()
                ->where(['task_id' => $model->id, 'is_done' => 0])
                ->count();

            if ($openChecklistCount > 0) {
                Yii::$app->session->setFlash(
                    'warning',
                    'Diese Aufgabe enthält noch ' . $openChecklistCount . ' offene Checklistenpunkte. Bitte schliesse sie in der Detailansicht, wenn du trotzdem fortfahren möchtest.'
                );
                $model->status = $oldStatus;
            } else {
                $model->closed_at = date('Y-m-d H:i:s');
                $model->closed_by = Yii::$app->user->id;
            }
        } elseif ($model->status !== 'geschlossen' && $oldStatus === 'geschlossen') {
            $model->closed_at = null;
            $model->closed_by = null;
        }

        $this->resolveTaskList($model);

        // Uploads VOR save()
        $model->uploadFiles = UploadedFile::getInstances($model, 'uploadFiles');

        if ($model->save()) {

            return $this->redirect(
                $this->contentContainer->createUrl('/todo/task/view', [
                    'id' => $model->id
                ])
            );
        }
    }

    return $this->render('update', [
        'model' => $model,
        'contentContainer' => $this->contentContainer,
    ]);
}

public function actionQuickUpdate($id)
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }

    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->andWhere(['todo_task.id' => (int) $id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException();
    }

    $field = (string) Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    switch ($field) {
        case 'status':
            if (!$model->canWorkOn()) {
                throw new \yii\web\ForbiddenHttpException();
            }
            $value = (string) $value;
            if (!in_array($value, ['offen', 'in_bearbeitung', 'geschlossen'], true)) {
                throw new HttpException(400, 'Ungültiger Status.');
            }

            $oldStatus = $model->status;

            if ($value === 'geschlossen' && $oldStatus !== 'geschlossen') {
                $openChecklistCount = ChecklistItem::find()
                    ->where(['task_id' => $model->id, 'is_done' => 0])
                    ->count();

                $confirmed = Yii::$app->request->post('confirm_open_checklist') === '1';

                if ($openChecklistCount > 0 && !$confirmed) {
                    Yii::$app->session->setFlash(
                        'warning',
                        'Diese Aufgabe enthält noch ' . $openChecklistCount . ' offene Checklistenpunkte.'
                    );

                    return $this->redirect(
                        $this->contentContainer->createUrl('/todo/task/view', ['id' => $model->id])
                    );
                }
            }

            $model->status = $value;

            if ($value === 'geschlossen' && $oldStatus !== 'geschlossen') {
                $model->closed_at = date('Y-m-d H:i:s');
                $model->closed_by = Yii::$app->user->id;
            } elseif ($value !== 'geschlossen' && $oldStatus === 'geschlossen') {
                $model->closed_at = null;
                $model->closed_by = null;
            }
            break;

        case 'priority':
            if (!$model->canManage()) {
                throw new \yii\web\ForbiddenHttpException();
            }
            $value = (string) $value;
            if (!in_array($value, ['niedrig', 'mittel', 'hoch'], true)) {
                throw new HttpException(400, 'Ungültige Priorität.');
            }
            $model->priority = $value;
            break;

        case 'due_date':
            if (!$model->canManage()) {
                throw new \yii\web\ForbiddenHttpException();
            }
            $value = trim((string) $value);
            if ($value === '') {
                $model->due_date = null;
            } else {
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                $errors = \DateTime::getLastErrors();
                if (
                    !$date ||
                    ($errors !== false && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0)) ||
                    $date->format('Y-m-d') !== $value
                ) {
                    throw new HttpException(400, 'Ungültiges Fälligkeitsdatum.');
                }
                $model->due_date = $value;
            }
            break;

        default:
            throw new HttpException(400, 'Dieses Feld kann nicht direkt geändert werden.');
    }

    if (!$model->save()) {
        Yii::$app->session->setFlash(
            'error',
            'Die Änderung konnte nicht gespeichert werden.'
        );
    }

    return $this->redirect(
        $this->contentContainer->createUrl('/todo/task/view', ['id' => $model->id])
    );
}

public function actionChangeStatus($id)
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }

    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->andWhere(['todo_task.id' => (int) $id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException();
    }

    if (!$model->canWorkOn()) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $newStatus = (string) Yii::$app->request->post('status');
    if (!in_array($newStatus, ['offen', 'in_bearbeitung', 'geschlossen'], true)) {
        throw new HttpException(400, 'Ungültiger Status.');
    }

    $oldStatus = $model->status;

    if ($newStatus !== $oldStatus) {
        $model->status = $newStatus;

        if ($newStatus === 'geschlossen') {
            $model->closed_at = date('Y-m-d H:i:s');
            $model->closed_by = Yii::$app->user->id;
        } elseif ($oldStatus === 'geschlossen') {
            $model->closed_at = null;
            $model->closed_by = null;
        }

        if (!$model->save()) {
            Yii::$app->session->setFlash(
                'error',
                'Der Status konnte nicht geändert werden.'
            );
        } else {
            Yii::$app->session->setFlash(
                'success',
                'Status geändert.'
            );
        }
    }

    return $this->redirect(
        $this->contentContainer->createUrl('/todo/task/view', ['id' => $model->id])
    );
}

public function actionKanbanStatus($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!$this->contentContainer) {
        Yii::$app->response->statusCode = 404;
        return ['success' => false, 'message' => Yii::t('TodoModule.base', 'Kein Space gefunden.')];
    }

    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->andWhere(['todo_task.id' => (int) $id, 'todo_task.parent_task_id' => null])
        ->one();

    if (!$model) {
        Yii::$app->response->statusCode = 404;
        return ['success' => false, 'message' => Yii::t('TodoModule.base', 'Aufgabe nicht gefunden.')];
    }
    if (!$model->canWorkOn()) {
        Yii::$app->response->statusCode = 403;
        return ['success' => false, 'message' => Yii::t('TodoModule.base', 'Du darfst den Status dieser Aufgabe nicht ändern.')];
    }

    $newStatus = (string) Yii::$app->request->post('status');
    if (!in_array($newStatus, ['offen', 'in_bearbeitung', 'geschlossen'], true)) {
        Yii::$app->response->statusCode = 400;
        return ['success' => false, 'message' => Yii::t('TodoModule.base', 'Ungültiger Status.')];
    }

    $oldStatus = $model->status;
    if ($newStatus === $oldStatus) {
        return ['success' => true];
    }

    if ($newStatus === 'geschlossen') {
        $openChecklistCount = (int) ChecklistItem::find()
            ->where(['task_id' => $model->id, 'is_done' => 0])
            ->count();
        if ($openChecklistCount > 0 && Yii::$app->request->post('confirm_open_checklist') !== '1') {
            Yii::$app->response->statusCode = 409;
            return [
                'success' => false,
                'requiresConfirmation' => true,
                'message' => Yii::t('TodoModule.base', 'Diese Aufgabe enthält noch {count} offene Checklistenpunkte. Trotzdem schliessen?', ['count' => $openChecklistCount]),
            ];
        }
    }

    $model->status = $newStatus;
    if ($newStatus === 'geschlossen') {
        $model->closed_at = date('Y-m-d H:i:s');
        $model->closed_by = Yii::$app->user->id;
    } elseif ($oldStatus === 'geschlossen') {
        $model->closed_at = null;
        $model->closed_by = null;
    }

    if (!$model->save()) {
        Yii::$app->response->statusCode = 422;
        return ['success' => false, 'message' => implode(' ', $model->getFirstErrors()) ?: Yii::t('TodoModule.base', 'Der Status konnte nicht geändert werden.')];
    }

    return ['success' => true];
}

public function actionDelete($id)
{

	if (!$this->contentContainer) {
		throw new HttpException(404, 'Kein Space gefunden.');
	}

    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->where(['todo_task.id' => $id])
        ->one();

    if (!$model) {
        throw new HttpException(404, 'Task nicht gefunden.');
    }

    if (!$model->canDelete()) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $model->delete();

   return $this->redirect(
    Yii::$app->request->referrer
    ?: $this->contentContainer->createUrl('/todo/task/index')
);
}

public function actionDuplicate($id)
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }
    if (!$this->contentContainer->permissionManager->can(new CreateTasks())) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $source = Task::find()
        ->contentContainer($this->contentContainer)
        ->andWhere(['todo_task.id' => (int) $id])
        ->one();
    if (!$source || !$source->canView()) {
        throw new NotFoundHttpException();
    }

    $copy = TaskDuplicationService::duplicate($source);
    if (!$copy) {
        Yii::$app->session->setFlash('error', 'Die Aufgabe konnte nicht dupliziert werden.');
        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $source->id]));
    }

    Yii::$app->session->setFlash('success', 'Aufgabe wurde als offene Kopie erstellt.');
    return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $copy->id]));
}

public function actionView($id)
{

	if (!$this->contentContainer) {
		throw new HttpException(404, 'Kein Space gefunden.');
	}
    // 🔐 Permission prüfen
    if (!$this->contentContainer->permissionManager->can(new ViewTasks())) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $task = Task::find()
        ->contentContainer($this->contentContainer)
        ->andWhere(['todo_task.id' => $id])
        ->one();

    if (!$task) {
        throw new NotFoundHttpException();
    }

    return $this->render('view', [
        'task' => $task,
        'contentContainer' => $this->contentContainer,
        'dependencyCandidates' => Task::find()
            ->contentContainer($this->contentContainer)
            ->andWhere(['<>', 'todo_task.id', $task->id])
            ->andWhere(['not in', 'todo_task.id', $task->getBlockingTasks()->select('todo_task.id')])
            ->orderBy(['todo_task.title' => SORT_ASC])
            ->limit(200)
            ->all(),
    ]);
}

public function actionDependencyAdd($id)
{
    $task = $this->findManageableTask($id);
    $blockingTaskId = (int) Yii::$app->request->post('blocking_task_id');
    $blockingTask = Task::find()
        ->contentContainer($this->contentContainer)
        ->andWhere(['todo_task.id' => $blockingTaskId])
        ->one();

    if (!$blockingTask || TaskDependencyService::wouldCreateCycle((int) $task->id, $blockingTaskId)) {
        Yii::$app->session->setFlash('error', 'Diese Abhängigkeit ist ungültig oder würde einen Kreis erzeugen.');
    } else {
        $dependency = new TaskDependency(['task_id' => $task->id, 'blocking_task_id' => $blockingTaskId]);
        if ($dependency->save()) {
            TaskHistoryService::record($task, 'dependency_added', 'Voraussetzung hinzugefügt: ' . $blockingTask->title);
        }
    }

    return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $task->id]));
}

public function actionDependencyRemove($id, $blockingTaskId)
{
    $task = $this->findManageableTask($id);
    $blockingTask = Task::findOne((int) $blockingTaskId);
    if (TaskDependency::deleteAll(['task_id' => $task->id, 'blocking_task_id' => (int) $blockingTaskId])) {
        TaskHistoryService::record($task, 'dependency_removed', 'Voraussetzung entfernt: ' . ($blockingTask?->title ?? '#' . $blockingTaskId));
    }
    return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $task->id]));
}

public function actionDeleteFile($id, $guid)
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }

    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->where(['todo_task.id' => $id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException();
    }

    if (!$model->canManage()) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $file = File::findOne(['guid' => $guid]);
    if (!$file || !$file->isAssignedTo($model)) {
        throw new NotFoundHttpException();
    }

    $fileTitle = trim((string) $file->title) ?: $file->file_name;
    $file->delete();
    TaskHistoryService::record($model, 'file_deleted', 'Datei gelöscht: ' . $fileTitle);

    Yii::$app->session->setFlash(
        'success',
        Yii::t('TodoModule.base', 'Datei gelöscht.')
    );

    return $this->redirect(
        $this->contentContainer->createUrl('/todo/task/view', [
            'id' => $model->id
        ])
    );
}


public function actionUploadFile($id)
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }

    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->where(['todo_task.id' => $id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException();
    }

    if (!$model->canManage()) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $uploadedFile = UploadedFile::getInstanceByName('uploadFile');
    $title = trim((string) Yii::$app->request->post('title'));

    if (!$uploadedFile) {
        Yii::$app->session->setFlash('error', Yii::t('TodoModule.base', 'Bitte eine Datei auswählen.'));
        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $model->id]));
    }

    if (mb_strlen($title) > 255) {
        Yii::$app->session->setFlash('error', Yii::t('TodoModule.base', 'Der Dateititel darf höchstens 255 Zeichen lang sein.'));
        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $model->id]));
    }

    // Dieselbe Datei-Validierung wie im normalen Aufgabenformular verwenden.
    $model->uploadFiles = [$uploadedFile];
    if (!$model->validate(['uploadFiles'])) {
        Yii::$app->session->setFlash(
            'error',
            implode(' ', $model->getErrors('uploadFiles')) ?: Yii::t('TodoModule.base', 'Die Datei konnte nicht hochgeladen werden.')
        );
        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $model->id]));
    }

    if (!$model->saveUploadedFile($uploadedFile, $title !== '' ? $title : null)) {
        Yii::$app->session->setFlash('error', Yii::t('TodoModule.base', 'Die Datei konnte nicht gespeichert werden.'));
    } else {
        TaskHistoryService::record($model, 'file_added', 'Datei hinzugefügt: ' . ($title !== '' ? $title : $uploadedFile->name));
        Yii::$app->session->setFlash('success', Yii::t('TodoModule.base', 'Datei hinzugefügt.'));
    }

    return $this->redirect(
        $this->contentContainer->createUrl('/todo/task/view', [
            'id' => $model->id,
        ])
    );
}


public function actionUpdateFileTitle($id, $guid)
{
    if (!$this->contentContainer) {
        throw new HttpException(404, 'Kein Space gefunden.');
    }

    $model = Task::find()
        ->contentContainer($this->contentContainer)
        ->where(['todo_task.id' => $id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException();
    }

    if (!$model->canManage()) {
        throw new \yii\web\ForbiddenHttpException();
    }

    $file = File::findOne(['guid' => $guid]);
    if (!$file || !$file->isAssignedTo($model)) {
        throw new NotFoundHttpException();
    }

    $title = trim((string) Yii::$app->request->post('title'));
    if (mb_strlen($title) > 255) {
        throw new HttpException(400, 'Der Dateititel darf höchstens 255 Zeichen lang sein.');
    }

    if ($title === '') {
        $title = pathinfo($file->file_name, PATHINFO_FILENAME);
    }

    $oldTitle = trim((string) $file->title) ?: $file->file_name;
    $file->title = $title;

    if (!$file->save(true, ['title'])) {
        Yii::$app->session->setFlash(
            'error',
            Yii::t('TodoModule.base', 'Der Dateititel konnte nicht gespeichert werden.')
        );
    } else {
        if ($oldTitle !== $title) {
            TaskHistoryService::record($model, 'file_updated', 'Datei umbenannt: ' . $oldTitle . ' → ' . $title);
        }
        Yii::$app->session->setFlash(
            'success',
            Yii::t('TodoModule.base', 'Dateititel gespeichert.')
        );
    }

    return $this->redirect(
        $this->contentContainer->createUrl('/todo/task/view', [
            'id' => $model->id
        ])
    );
}



    public function actionChecklistAdd($id)
    {
        $task = $this->findTaskForChecklist($id);
        $title = trim((string) Yii::$app->request->post('title'));
        $dueDate = trim((string) Yii::$app->request->post('due_date'));

        if ($title !== '') {
            $maxOrder = (int) ChecklistItem::find()->where(['task_id' => $task->id])->max('sort_order');
            $item = new ChecklistItem([
                'task_id' => $task->id,
                'title' => $title,
                'due_date' => $dueDate !== '' ? $dueDate : null,
                'sync_to_calendar' => (bool) Yii::$app->request->post('sync_to_calendar', false),
                'is_done' => false,
                'sort_order' => $maxOrder + 10,
            ]);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$item->save()) {
                    throw new HttpException(400, 'Checklistenpunkt konnte nicht gespeichert werden.');
                }

                $this->syncChecklistAssignees(
                    $item,
                    Yii::$app->request->post('assigned_user_guids', [])
                );

                if (!CalendarSyncService::syncChecklistItem($item)) {
                    Yii::$app->session->setFlash(
                        'warning',
                        'Der Checklistenpunkt wurde gespeichert, der Kalendereintrag konnte aber nicht synchronisiert werden.'
                    );
                }

                TaskHistoryService::record($task, 'checklist_added', 'Checklistenpunkt hinzugefügt: ' . $item->title);

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $task->id]));
    }

    public function actionChecklistEdit($id, $itemId)
    {
        $task = $this->findTaskForChecklist($id);
        $item = $this->findChecklistItem($task, $itemId);
        $title = trim((string) Yii::$app->request->post('title'));

        if ($title === '') {
            throw new HttpException(400, 'Der Checklistenpunkt darf nicht leer sein.');
        }

        $dueDate = trim((string) Yii::$app->request->post('due_date'));
        $oldTitle = $item->title;
        $item->title = $title;
        $item->due_date = $dueDate !== '' ? $dueDate : null;
        $item->sync_to_calendar = (bool) Yii::$app->request->post('sync_to_calendar', false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$item->save()) {
                throw new HttpException(400, 'Checklistenpunkt konnte nicht gespeichert werden.');
            }

            $this->syncChecklistAssignees(
                $item,
                Yii::$app->request->post('assigned_user_guids', [])
            );

            if (!CalendarSyncService::syncChecklistItem($item)) {
                Yii::$app->session->setFlash(
                    'warning',
                    'Der Checklistenpunkt wurde gespeichert, der Kalendereintrag konnte aber nicht synchronisiert werden.'
                );
            }


            TaskHistoryService::record(
                $task,
                'checklist_updated',
                $oldTitle === $item->title
                    ? 'Checklistenpunkt bearbeitet: ' . $item->title
                    : 'Checklistenpunkt umbenannt: ' . $oldTitle . ' → ' . $item->title
            );

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $task->id]));
    }

    public function actionChecklistToggle($id, $itemId)
    {
        $task = $this->findTaskForChecklist($id);
        $item = $this->findChecklistItem($task, $itemId);

        $wasDone = (bool) $item->is_done;
        if ($wasDone) {
            $item->is_done = false;
            $item->completed_by = null;
            $item->completed_at = null;
        } else {
            $item->is_done = true;
            $item->completed_by = Yii::$app->user->id;
            $item->completed_at = date('Y-m-d H:i:s');
        }

        $item->save(false, ['is_done', 'completed_by', 'completed_at']);
        TaskHistoryService::record(
            $task,
            $wasDone ? 'checklist_reopened' : 'checklist_completed',
            ($wasDone ? 'Checklistenpunkt wieder geöffnet: ' : 'Checklistenpunkt erledigt: ') . $item->title
        );

        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $task->id]));
    }

    public function actionChecklistMove($id, $itemId, $direction)
    {
        $task = $this->findTaskForChecklist($id);
        $item = $this->findChecklistItem($task, $itemId);

        if (!in_array($direction, ['up', 'down'], true)) {
            throw new HttpException(400, 'Ungültige Richtung.');
        }

        $query = ChecklistItem::find()->where(['task_id' => $task->id]);
        if ($direction === 'up') {
            $other = $query->andWhere(['<', 'sort_order', $item->sort_order])
                ->orderBy(['sort_order' => SORT_DESC, 'id' => SORT_DESC])->one();
        } else {
            $other = $query->andWhere(['>', 'sort_order', $item->sort_order])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->one();
        }

        if ($other) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $currentOrder = $item->sort_order;
                $item->sort_order = $other->sort_order;
                $other->sort_order = $currentOrder;
                $item->save(false, ['sort_order']);
                $other->save(false, ['sort_order']);
                TaskHistoryService::record($task, 'checklist_moved', 'Checklistenpunkt verschoben: ' . $item->title);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $task->id]));
    }

    public function actionChecklistDelete($id, $itemId)
    {
        $task = $this->findTaskForChecklist($id);
        $item = $this->findChecklistItem($task, $itemId);
        $itemTitle = $item->title;
        $item->delete();
        TaskHistoryService::record($task, 'checklist_deleted', 'Checklistenpunkt gelöscht: ' . $itemTitle);

        return $this->redirect($this->contentContainer->createUrl('/todo/task/view', ['id' => $task->id]));
    }

    private function resolveTaskList(Task $model): void
    {
        $name = trim((string) $model->task_list_name);
        if ($name === '') {
            $model->task_list_id = null;
            return;
        }

        $list = TaskList::find()->where([
            'space_id' => $this->contentContainer->id,
            'name' => $name,
        ])->one();

        if (!$list) {
            // Anyone who may create/edit the task may create a list inline.
            $canCreateList = $model->isNewRecord
                ? $this->contentContainer->permissionManager->can(new CreateTasks())
                : $model->canManage();

            if (!$canCreateList) {
                throw new \yii\web\ForbiddenHttpException('Keine Berechtigung zum Erstellen einer Aufgabenliste.');
            }

            $list = TaskList::findOrCreateForSpace((int) $this->contentContainer->id, $name);
            if (!$list) {
                throw new HttpException(400, 'Aufgabenliste konnte nicht erstellt werden.');
            }
        }

        $model->task_list_id = (int) $list->id;
    }

    private function findTaskForChecklist($id): Task
    {
        if (!$this->contentContainer) {
            throw new HttpException(404, 'Kein Space gefunden.');
        }

        $task = Task::find()
            ->contentContainer($this->contentContainer)
            ->andWhere(['todo_task.id' => $id])
            ->one();

        if (!$task) {
            throw new NotFoundHttpException();
        }

        if (!$task->canWorkOn()) {
            throw new \yii\web\ForbiddenHttpException();
        }

        return $task;
    }

    private function findManageableTask($id): Task
    {
        if (!$this->contentContainer) {
            throw new HttpException(404, 'Kein Space gefunden.');
        }
        $task = Task::find()
            ->contentContainer($this->contentContainer)
            ->andWhere(['todo_task.id' => (int) $id])
            ->one();
        if (!$task) {
            throw new NotFoundHttpException();
        }
        if (!$task->canManage()) {
            throw new \yii\web\ForbiddenHttpException();
        }
        return $task;
    }

    private function syncChecklistAssignees(ChecklistItem $item, $userGuids): void
    {
        if ($userGuids === null || $userGuids === '') {
            $userGuids = [];
        } elseif (!is_array($userGuids)) {
            $userGuids = array_filter(array_map('trim', explode(',', (string) $userGuids)));
        }

        $userGuids = array_values(array_unique(array_filter(array_map('strval', $userGuids))));
        $userIds = [];

        if ($userGuids) {
            $users = User::find()->where(['guid' => $userGuids])->all();
            if (count($users) !== count($userGuids)) {
                throw new HttpException(400, 'Mindestens eine ausgewählte Person ist ungültig.');
            }

            $userIds = array_map(static fn(User $user) => (int) $user->id, $users);
            $memberCount = (int) Membership::find()->where([
                'space_id' => $this->contentContainer->id,
                'user_id' => $userIds,
                'status' => Membership::STATUS_MEMBER,
            ])->count();

            if ($memberCount !== count($userIds)) {
                throw new HttpException(400, 'Zuständig können nur Mitglieder dieses Spaces sein.');
            }
        }

        Yii::$app->db->createCommand()
            ->delete('todo_checklist_item_user', ['checklist_item_id' => $item->id])
            ->execute();

        if ($userIds) {
            $rows = array_map(
                static fn(int $userId) => [(int) $item->id, $userId],
                $userIds
            );
            Yii::$app->db->createCommand()
                ->batchInsert('todo_checklist_item_user', ['checklist_item_id', 'user_id'], $rows)
                ->execute();
        }
    }

    private function findChecklistItem(Task $task, $itemId): ChecklistItem
    {
        $item = ChecklistItem::findOne([
            'id' => $itemId,
            'task_id' => $task->id,
        ]);

        if (!$item) {
            throw new NotFoundHttpException();
        }

        return $item;
    }

}
