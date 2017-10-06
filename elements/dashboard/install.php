<?php
$success = true;
if (function_exists('exif_read_data')) {
	?>
    <div class="alert alert-success">
	   <p><?php echo t('The EXIF PHP extension is currently enabled.') ?></p>
        <p><?php echo t('You can proceed with installing this package.') ?></p>
    </div>
	<?php
} else {
	$success = false;
	?>
    <div class="alert alert-danger">
        <p><?php echo t('The EXIF PHP extension is currently disabled or not installed, but this package requires it.') ?></p>
        <p><?php echo t('Please enable it and repeat the tests.') ?></p>
    </div>
    <?php
}
if (function_exists('imagerotate') && function_exists('imageflip')) {
	?>
    <div class="alert alert-success">
	   <p><?php echo t('You have the GD library installed and your PHP version is high enough to support this package.') ?></p>
    </div>
	<?php
} elseif (function_exists('imagerotate')) {
	?>
    <div class="alert alert-warning">
        <p><?php echo t('Your PHP version is too OLD (the %s PHP function is missing).', '<code>imageflip</code>') ?></p>
        <p><?php echo t("This package won't be able to fix some rarely used rotation systems (in most cases this shouldn't be a problem).") ?></p>
    </div>
    <?php
} else {
	$success = false;
	?>
    <div class="alert alert-danger">
        <p><?php echo t('The GD PHP extension is currently disabled or not installed, but this package requires it.') ?></p>
        <p><?php echo t('Please enable it and repeat the tests.') ?></p>
    </div>
    <?php
}

if ($success === false) {
	?>
    <input type="checkbox" required="required" style="display: none" />
    <script>
    $(document).ready(function() {
        var $form = $('.ccm-pane-body').closest('form');
        $form.on('submit', function (e) { e.preventDefault(); });
        $form.find('input[type="submit"]').replaceWith($('<button class="btn warning ccm-button-right" />')
            .text("<?php echo t('Repeat tests') ?>")
            .on('click', function(e) {
                e.preventDefault();
                window.location.reload();
            })
        )
        ;
    });
    </script>
    <?php
}
