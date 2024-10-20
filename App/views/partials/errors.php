<?php

if (isset($errors) && count($errors)) :
    foreach ($errors as $error) :
?>
        <div class="message bg-red-100 p-3 my-3"><?= sanatize($error) ?></div>
<?php
    endforeach;
endif;

?>