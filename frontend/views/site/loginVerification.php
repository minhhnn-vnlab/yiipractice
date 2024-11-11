<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\CodeVerifyForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
?>

<div class="login-verification">
    <h1>Xác nhận đăng nhập</h1>
    <p>Vui lòng nhập mã code để thực hiện đăng nhập.</p>

    <?php if ($method == 'email'): ?>
        <p>Mã code đã được gửi đến hộp thư <strong><?= Html::encode($email) ?></strong>. Vui lòng kiểm tra và nhập mã code.</p>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'setup-2fa-form',
    ]); ?>

    <?= $form->field($model, 'code')->textInput(['autofocus' => true])->label('Mã code') ?>

    <div class="form-group">
        <?= Html::submitButton('Xác nhận', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>