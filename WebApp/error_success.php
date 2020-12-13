<?php if(count($errors)>0): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p> <?php echo $error; ?> </p>
        <?php endforeach ?>
    </div>
<?php endif ?>

<?php if(count($successes)>0): ?>
    <div class="success">
        <?php foreach ($succeses as $succes): ?>
            <p> <?php echo $succes; ?> </p>
        <?php endforeach ?>
    </div>
<?php endif ?>