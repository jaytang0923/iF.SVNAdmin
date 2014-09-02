<?php GlobalHeader(); ?>

<h1><?php Translate("Create Product Name"); ?></h1>
<p class="hdesc"><?php Translate("Create a new product to format your sources."); ?></p>
<div>
  <form method="POST" action="repositorycreateproduct.php">
	  
    
    <div class="form-field">
		<label for="ps"><?php Translate('Products Select:'); ?></label>
		<select name="ps" id="ps">
			<?php $a = file('data/products');?>
			<?php foreach ($a as $rp) : ?>
				<!-- <option value="<?php print($rp); ?>">
					<?php print($rp); ?>
				</option>
				-->
				<?php $rp=str_replace(PHP_EOL,'', $rp)?>
				<option value="<?php print($rp);?>"><?php print($rp);?></option>
			<?php endforeach; ?>
		</select>
		
		<input type="submit" name="delete_product" value="<?php Translate("Delete Product"); ?>" class="addbtn">
	</div>
    
     <div class="form-field">
      <label for="newproduct"><?php Translate("New Product name:"); ?></label>
      <input type="text" name="newproduct" id="newproduct" class="lineedit">
      <input type="submit" name="create_product" value="<?php Translate("Create Product"); ?>" class="addbtn">
    </div>
    
    
  </form>

</div>

<?php GlobalFooter(); ?>
