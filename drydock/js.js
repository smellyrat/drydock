//Cookie code from http://www.quirksmode.org/js/cookies.html
function readCookie(name)
{
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++)
        {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
}

function vctest() {
    it=getit();
    if (it) {
        vcf=document.getElementById("vc");
        it.open("GET","vctest.php?c="+vcf.value,true);
        btn=document.getElementById("subbtn");
        before=btn.value;
        it.onreadystatechange=function() {
            //btn.value="State: "+it.readyState;
            if (it.readyState==4) {
                if (it.responseText=="Y") {
                    btn.value="Submitting..."
                    document.getElementById("postform").submit();
                    }
                else {
                    alert("The verification code seems to be incorrect. Please try again.");
                    vcf.focus();
                    btn.value=before;
                    btn.disabled=false;
                    }
                }
            }
        it.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        it.send("");
        btn.value="Checking VC...";
        btn.disabled=true;
        }
    else {
        document.getElementById("postform").submit();
        //document.postform.submit();
        //return true;
        }
    }
        
            

function getit() {
    var it=null;
    try {
        it=new XMLHttpRequest();
        }
    catch(e) {
        //alert("XMLHttpRequest failed.");
        try {
            it=new ActiveXObject("Msxml2.XMLHTTP");
            }
        catch(e) {
            //alert("Msxml2.XMLHTTP failed.");
            try {
                it=new ActiveXObject("Microsoft.XMLHTTP");
                }
            catch(e) {
                //alert("Microsoft.XMLHTTP failed.");
                alert("Your browser won't support this feature.");
                }
            }
        }
    return(it);
    }
    
function oldtoggmenu()
{
    $("#idxmenuitem").toggle();
    
    if( $("#idxmenuitem").is(':visible'))
    {
        $("#main").css("margin-right", "154px");
    }
    else
    {
        $("#main").css("margin-right", "0px");
    }
}


function ToggleMenu(name, duration, path)
{
	var nameEQ = name + "-menu=";
	var foundCookie = false;
	var ca = document.cookie.split(';');

	// First we check for the presence of a cookie
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') { c = c.substring(1,c.length); }
		if (c.indexOf(nameEQ) == 0) { foundCookie = true; break; }
	}

	var date = new Date();
	// The presence of a cookie means that the menu has been hidden. Since we're toggling that, let's erase the cookie.
	if(foundCookie==true)
	{
		date.setTime(date.getTime()+(-1*24*60*60*1000)); // setting the cookie's expiry date to the past means it gets removed
		var expires = "; expires="+date.toGMTString();
		document.cookie = name+"-menu="+value+expires+"; path="+path
                
                $("#idxmenuitem").show();
		$("#main").css("margin-right", "154px");
                
	} else { // Add the cookie, hide the menu.
		date.setTime(date.getTime()+duration);
		var expires = "; expires="+date.toGMTString();
		document.cookie = name+"-menu="+value+expires+"; path="+path;
                
                $("#idxmenuitem").hide();
                $("#main").css("margin-right", "0px");
	}
}
