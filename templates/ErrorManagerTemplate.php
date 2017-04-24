<?php
include_once 'ErrorManager.css.html';
?>
<div class="code-error <?= $this->noticeClass ?>">
    <h3><?= $this->errType ?></h3>
    <p>
        <span class="error-msg"><?= $this->errStr ?></span>&ensp;on line <b class="error-line"><?= $this->errLine ?></b>
        <?php if($this->fatal) { ?>
        <br>Script execution has stopped.
        <?php } ?>
    </p>
</div>