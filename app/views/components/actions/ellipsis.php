<div class="<?= !$force_ellipsis ? 'ellipsis_container' : null; ?>">
    <?php
        if (!$force_ellipsis)
        {
            $a = clone $actions[0];
            
            $a->title($a->text)
            ->text(null)
            ->classes('hover_action_circle')
            ->render();
        }
    ?>

    <div class="additional_options_menu generic_ellipsis actions <?= $force_ellipsis ? 'force_ellipsis' : null; ?>" title="Actions Menu">
        <div class="additional_options">
            <ul class="<?= count($actions) > 10 ? 'two_columns' : null; ?>">
                <?php foreach($actions as $a): ?>
                    <li>
                        <?= $a->render(); ?>
                    </li>
                <?php endforeach; ?>

            </ul>
        </div>

        <div class="action_circle">
            <div class="dot_container">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
            
    </div>
</div>
            