Redirecing to AAIB, please wait...
<input type="hidden" value="{$payment_url}" class="value_url">
<script type="text/javascript">
{literal}
	$('document').ready( function() {
		window.location.href = $(".value_url").val();
	});
{/literal}
</script>