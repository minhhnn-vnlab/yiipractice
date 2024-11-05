<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\LoginHistory $model */

$this->title = 'Update Login History: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Login Histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="login-history-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
