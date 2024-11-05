<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\LoginHistory $model */

$this->title = 'Create Login History';
$this->params['breadcrumbs'][] = ['label' => 'Login Histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="login-history-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
