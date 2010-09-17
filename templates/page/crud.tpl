<@ include file="page/crud/toptable.tpl" @>

<@ if $CRUD.Edit @>

	<input type="hidden" name="<@ $CRUD.Field @>" value="<@ $CRUD.ID @>" />

	<@ include file="page/crud/updatenew.tpl" @>

	<@ form name=$CRUD.Form @>

<@ /if @>

<@ if !$CRUD.Edit || !$CRUD.HideListOnEdit @>

	<@ include file="page/crud/bottomtable.tpl" @>

<@ /if @>
