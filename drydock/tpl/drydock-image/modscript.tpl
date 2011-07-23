{literal}
<script type="text/javascript">
    $(document).ready( function () {
        
        $(".jsmod").each( function(i) {
            
            var x = String($(this).text());
            var splitter = x.lastIndexOf(","); // where is the last comma?
            var board = x.substr(0,splitter); // everything before the comma is the board ID
            var post = x.substr(splitter+1); // everything after the last comma is the post ID          
           
           $(this).html("[Edit]");
           $(this).attr("href", "{/literal}{$THurl}{literal}editpost.php?post=" + post + "&board=" + board);
           $(this).show();
           
        });
    });
</script>
{/literal}
