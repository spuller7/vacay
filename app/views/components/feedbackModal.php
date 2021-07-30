<?php

use app\core\Application;
use app\models\Feedback;

?>

<div id="add-feedback-dialog" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Provide Feedback</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= Application::$app->view->renderComponent('forms/feedback', ['feedback' => new Feedback]); ?>
            </div>
            <div class="modal-footer">
                <button id="submit-feedback-button" type="button" class="btn btn-primary">Submit Feedback</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Link the button to submit form above it
    $('#submit-feedback-button').on('click', function() {
        $('#add-feedback-form').submit();
    });
</script>
