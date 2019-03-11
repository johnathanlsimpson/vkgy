<?php
  function background($input_file) {
   //if(!empty($input_file) && file_exists("..".$input_file)) {
			if(!empty($input_file)) {
     global $background_image;
     $background_image = $input_file;
   }
  }
?>