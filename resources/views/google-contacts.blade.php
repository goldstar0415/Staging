<script type="text/javascript">
    window.opener.modalContactsCallback({!! $contacts->toJson() !!});
    window.close();
</script>