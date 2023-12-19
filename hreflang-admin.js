jQuery(document).ready(function($) {
    $('#addHreflangField').on('click', function() {
        var table = $('#hreflang_meta table');
        var newRow = $('<tr><td><input type="text" name="hreflang_country[]" placeholder="Country Code"></td><td><input type="text" name="hreflang_url[]" placeholder="URL"></td></tr>');
        table.append(newRow);
    });
});
