<a href="<?= $a->url; ?>"
    <?php foreach ($a->data_attrs as $name => $attr): ?>
        data-<?= $name; ?>="<?= $attr; ?>"
    <?php endforeach; ?>

    <?php if ($a->class): ?>
        class="<?= implode(' ', $a->class); ?>"
    <?php endif; ?>

    <?php if ($a->title): ?>
        title="<?= $a->title; ?>"
    <?php endif; ?>

    <?php if ($a->target): ?>
        title="<?= $a->target; ?>"
    <?php endif; ?>
>
    <i class="fa fa-<?= $a->icon; ?>"></i>

    <?php if ($a->text): ?>
        <span><?= $a->text; ?></span>
    <?php endif; ?>
</a>

    