<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Mở khóa tài khoản';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-unlock">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Một mã code đã gửi đến email của bạn. Hãy nhập mã code để xác thực mở tài khoản.</p>

    <?php $form = ActiveForm::begin(['id' => 'code-verify-form']); ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>
    <div class="form-group">
        <?= Html::submitButton('Xác nhận', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>