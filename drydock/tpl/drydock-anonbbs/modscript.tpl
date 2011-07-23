{literal}
<script type="text/javascript">
    $(document).ready( function () {
        
        $(".jsmod").each( function(i, val) {
            
            var x = String(val.html());
            var splitter = x.lastIndexOf(","); // where is the last comma?
            var board = x.substr(0,splitter); // everything before the comma is the board ID
            var post = x.substr(splitter+1); // everything after the last comma is the post ID          
           
           val.html("[Edit]");
           val.attr("href", "{/literal}{$THurl}{literal}editpost.php?post=" + post + "&board=" + board);
           val.show();
           
        });
    });
</script>
{/literal}
