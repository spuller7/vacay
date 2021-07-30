<?php

use \app\core\Application;
use \app\classes\Html;

$subjects = Application::$app->users;

?>

<form id="add-feedback-form" action="todo/addFeedback" method="POST" class="ajax_form">
    <div class="form-group">
        <label>Subject</label>
        <select name="subject_id" class="form-control <?= $feedback->hasError('subject_id') ? 'is-invalid' : ''; ?>">
            <option value="">&mdash;</option>
            <?php foreach ($subjects as $user): ?>
                <?php if ($user->getID() != Application::$app->loggedInUser->getID()) : ?>
                    <option value="<?= $user->getID(); ?>" <?= Html::selected($feedback->subject_id, $user->getID()) ?>><?= $user->getFullName(); ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">
            <?= $feedback->getFirstError('subject_id'); ?>
        </div>
    </div>

    <div class="form-group">
        <label>Comment</label>
        <textarea name="comment" class="form-control <?= $feedback->hasError('comment') ? 'is-invalid' : ''; ?>"></textarea>
        <div class="invalid-feedback">
            <?= $feedback->getFirstError('comment'); ?>
        </div>
    </div>

    <div class="form-group">
        <label>Response Type</label>
        <div class="disposition-icons <?= $feedback->hasError('disposition') ? 'is-invalid' : ''; ?>">
            <label class="disposition-icon">
                <input type="radio" name="disposition" value="NEGATIVE" <?= HTML::checked($feedback->disposition, "NEGATIVE");?>>
                <i class="fa fa-4x fa-poop"></i>
                <p>Shit</p>
            </label>
            <label class="disposition-icon">
                <input type="radio" name="disposition" value="NEUTRAL" <?= HTML::checked($feedback->disposition, "NEUTRAL");?>>
                <i class="fa fa-4x fa-meh"></i>
                <p>Neutral</p>
            </label>
            <label class="disposition-icon">
                <input type="radio" name="disposition" value="POSITIVE" <?= HTML::checked($feedback->disposition, "POSITIVE");?>>
                <i class="fa fa-4x fa-grin-hearts"></i>
                <p>Positive</p>
            </label>
        </div>
        <div class="invalid-feedback">
                <?= $feedback->getFirstError('disposition'); ?>
            </div>
    </div>
</form>