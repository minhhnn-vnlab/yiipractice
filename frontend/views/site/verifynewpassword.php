<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
$this->title = 'Tạo mật khẩu mới';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-verify-new-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Tài khoản của bạn đã được mở khóa. Hãy tạo mật khẩu mới để bảo vệ tài khoản của bạn.</p>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'newPassword')->passwordInput() ?>

    <?= $form->field($model, 'confirmPassword')->passwordInput() ?>
    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Xác nhận', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>