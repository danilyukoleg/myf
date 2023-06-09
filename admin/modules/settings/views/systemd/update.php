<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Systemd */

$this->title = 'Обновить процесс ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => "Настройки и мониторинг", 'url' => '/systemd/'];
$this->params['breadcrumbs'][] = ['label' => 'Фоновые процессы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>
<div class="systemd-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
