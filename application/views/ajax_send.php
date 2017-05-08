<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<div id="result"></div>
        <script>
            jQuery(document).ready(function ($) {
                var start = 1;
                var end = <?php echo $count; ?>;

                function send() {
                    $.get('/welcome/dot_product/' + start + '/' + end + '/<?=$list_id?>').done(function (data) {

                    });
                }

                send();


            })
        </script>
