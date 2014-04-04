<?php if($message) : ?>
<div class="mor alert notice">
<p><?php print($message); ?></p>
</div>
<?php endif; ?>

	<legend>Search options</legend>
	<p><strong>Members found:</strong> </p>
	<p></p>


<?php //success
echo form_open($_form_base."&method=batch", array ("enctype" => "multipart/form-data" ));
?>	

<label>Choose a zip file to upload: <input type="file" name="userfile" /></label>
<br />
<input type="submit" name="submit" value="Upload" />
</form>

<p class="centerSubmit">
				<input name="edit_field_group_name" value="<?= lang('Banned selected'); ?>" class="submit" type="submit">&nbsp;&nbsp;					
</p>

<?php //success
echo form_open($_form_base."&method=batch", '');

?>	
</form>




