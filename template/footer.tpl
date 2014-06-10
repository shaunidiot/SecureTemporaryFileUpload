           
        </div>

    </div>

    <!-- /container -->

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js">
    </script>
    <script src="js/bootstrap.min.js"></script>
	<?php 
	if ($include_countdown) {
	?>
		<script src="js/jquery.countdown.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
		  $("#file_expiry").countdown({date:"<?php echo $countdown_unix * 1000; ?>", htmlTemplate: "%{h} <span class=\"small\">hours</span> %{m} <span class=\"small\">minutes</span> %{s} <span class=\"small\">seconds</span>"}); //Date and Time (in 24 hour format)
		  });
	   </script>
   <?php } ?>
    <script>
        $(document)
            .on('change', '.btn-file :file', function () {
                var input = $(this),
                    numFiles = input.get(0).files ? input.get(0).files.length : 1,
                    label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                input.trigger('fileselect', [numFiles, label]);
            });

        $(document).ready(function () {
            $('.btn-file :file').on('fileselect', function (event, numFiles, label) {

                var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;

                if (input.length) {
                    input.val(log);
                } else {
                    if (log) alert(log);
                }

            });
        });
    </script>
</body>

</html>
