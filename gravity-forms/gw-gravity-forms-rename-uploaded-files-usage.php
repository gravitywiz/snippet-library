<?php

# First and Last Name
new GW_Rename_Uploaded_Files( array(
	'form_id'  => 628,
	'field_id' => 3,
	'template' => '{Name (First):1.3}-{Name (Last):1.6}-{filename}',
	// most merge tags are supported, original file extension is preserved
) );

# Form Title Merge Tag
new GW_Rename_Uploaded_Files( array(
	'form_id'  => 12,
	'field_id' => 18,
	'template' => '{form_title}-{filename}', // most merge tags are supported, original file extension is preserved
) );

# Static File Name
new GW_Rename_Uploaded_Files( array(
	'form_id'  => 628,
	'field_id' => 5,
	'template' => 'static-file-name',
) );

# Static File Name and increment regardless of extension
# e.g. static-file-name.jpg static-file-name1.png static-file-name3.pdf, etc.
new GW_Rename_Uploaded_Files( array(
	'form_id'          => 628,
	'field_id'         => 5,
	'template'         => 'static-file-name',
	'ignore_extension' => true,
) );
