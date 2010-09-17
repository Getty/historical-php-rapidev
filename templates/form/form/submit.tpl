<@ if !isset($form.nosubmit) @>
	<input type="submit" id="<@ $form.id @>" name="<@ $form.id @>" value="<@ if isset($form.submitvalue) @><@ $form.submitvalue @><@ else @>Abschicken<@ /if @>" class="submit<@ if isset($form.submitclass) @> <@ $form.submitvalue @><@ /if @>"/>
<@ else @>
	<input type="hidden" id="<@ $form.id @>" name="<@ $form.id @>" value="" />
<@ /if @>
