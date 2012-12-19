{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; General Settings</title></head>
<body>
<form method="post" enctype="multipart/form-data" action="admin.php?t=g">
<div id="main">
    <div class="box">
			<div class="pgtitle">
				Image Settings
			</div>
			<div class="indent">
				Image thumbnail JPEG quality: <input type="text" name="THjpegqual" size="3" value="{$THjpegqual}" />%<br />
				Don't allow upload of duplicate images: <input type="checkbox" name="THdupecheck"{if $THdupecheck} checked="checked"{/if} /><br />
			</div>
	        <div class="pgtitle">
				Template Settings
			</div>
			<div class="indent">
				Use mod_rewrite for pretty URLs? <input type="checkbox" name="THuserewrite"{if $THuserewrite} checked="checked"{/if} />
				<i>May not work on all hosts</i><br />
				Default template set:
				<select name="THtplset">
	{foreach from=$tplsets item=set}
					<option value="{$set}"{if $set==$THtplset} selected="selected"{/if}>{$set}</option>
	{/foreach}
				</select>
				<i>Does not apply to admin pages</i><br />
				Template Testing Mode: <input type="checkbox" name="THtpltest"{if $THtpltest} checked="checked"{/if} /> <i>Useful for template editing</i><br />
				Delete cache: <input type="checkbox" name="fragcache" /> <i>Discards all other changes made on this page</i><br />
				Full debug mode: <input type="checkbox" name="DDDEBUG"{if $DDDEBUG} checked="checked"{/if} /> <i>Seriously don't turn this on</i><br />
			</div>
			<div class="pgtitle">
				Anti-spam Settings
			</div>
			<div class="indent">
				<ul>
					<li>reCAPTCHA requires an external library available from Google, as well as filling out the private and public keys below.
					<li>Human test attempts to out smart most bots via hidden fields.
				</ul>
				Anti spam methods:
	 			<select name="THvc">
					<option value="0"{if $THvc==0} selected="selected"{/if}>None</option>
					<option value="1"{if $THvc==1} selected="selected"{/if}>reCAPTCHA</option>
					<option value="2"{if $THvc==2} selected="selected"{/if}>Human Test</option>
				</select><br />
				reCAPTCHA keys<br />
				Public key:<input type="text" name="reCAPTCHAPublic" size="40" value="{$reCAPTCHAPublic}" /><br />
				Private key:<input type="text" name="reCAPTCHAPrivate" size="40" value="{$reCAPTCHAPrivate}" /><br />
			</div>
			<div class="pgtitle">
				Time Settings
			</div>
			<div class="indent">
				Time offset: <input type="text" name="THtimeoffset" size="4" value="{$THtimeoffset}" /> minutes.<br />
				<i>If you don't want to use the same time as the web server, use this to offset the time. A positive number sets time forward; a negative number sets it back.
				This will only affect future posts; the times of current posts will not be changed.</i><br />
				Date formatting string: <input type="text" name="THdatetimestring" size="60" value="{$THdatetimestring}" /><br />
				<i>This determines how dates are formatted. See <a href="http://us3.php.net/manual/en/function.strftime.php" target="_blank">this function documentation</a> for more information.<br />
				The server says it's {$smarty.now|date_format:$THdatetimestring}. With the current time offset, it's {math equation="x+(y*60)" x=$smarty.now y=$THtimeoffset assign="whee"}
				{$whee|date_format:$THdatetimestring}.</i>
			</div>
			<div class="pgtitle">
				Site Settings
			</div>
			<div class="indent">
				Site name: <input type="text" name="THname" size="20" value="{$THname}" /><br />
				<i>These two settings define what is displayed when a person does not enter a value for the text and name fields.  Name is not displayed if forced_anon is set for a board.)</i><br />
				Default text: <input type="text" name="THdefaulttext" size="60" value="{$THdefaulttext}" /><br />
				Default name: <input type="text" name="THdefaultname" size="60" value="{$THdefaultname}" /><br />
				<i>These boards are used for various in-code purposes, such as disabling filters and feeding the news page.
				Please remember to lock posting to moderators only and hide the boards by default unless you wish them to be accessable (and NOT FILTERED) to all users.</i><br />
				News board:
				<select name="THnewsboard">
					<option value="0"{if $THnewsboard==0} selected="selected"{/if}>None</option>
		{foreach from=$boards item=board}
					<option value="{$board.id}"{if $THnewsboard==$board.id} selected="selected"{/if}>/{$board.folder}/</option>
		{/foreach}
				</select>
				Moderator board:
				<select name="THmodboard">
					<option value="0"{if $THmodboard==0} selected="selected"{/if}>None</option>
					{foreach from=$boards item=board}
						<option value="{$board.id}"{if $THmodboard==$board.id} selected="selected"{/if}>/{$board.folder}/</option>
					{/foreach}
				</select>
			</div>
			<div class="pgtitle">
				  Utility Settings
				</div>
			<div class="indent">
				Please see the documentation for which PEAR libraries to install and how to use them<br />
				Path to PEAR <input type="text" name="THpearpath" size="12" value="{$THpearpath}" /><br />
				Use SVG functions <input type="checkbox" name="THuseSVG"{if $THuseSVG} checked="checked"{/if} /><br />
				SVG behavior <select name="THSVGthumbnailer">
					<option value="0"{if THSVGthumbnailer ==0} selected="selected"{/if}>SVG placeholder graphic</option>
					<option value="1"{if THSVGthumbnailer ==1} selected="selected"{/if}>Imagemagick thumbnail</option>
					<option value="2"{if THSVGthumbnailer ==2} selected="selected"{/if}>rsvg thumbnail</option>
				</select>
				<br />
				{* Use PDF functions <input type="checkbox" name="THusePDF"{if $THusePDF} checked="checked"{/if} /><br /> *}
				Use SWF metatag functions <input type="checkbox" name="THuseSWFmeta"{if $THuseSWFmeta} checked="checked"{/if} /><br />
				Use cURL functions <input type="checkbox" name="THusecURL"{if $THusecURL} checked="checked"{/if} /><br />
			</div>
			<div class="pgtitle">
					Profile Settings
			</div>
			<div class="indent">
				Registration e-mail name: <input type="text" name="THprofile_emailname" size="24" value="{$THprofile_emailname}" /><br />
				Registration e-mail address: <input type="text" name="THprofile_emailaddr" size="24" value="{$THprofile_emailaddr}" /><br />
				Registration policy: 
				<input type="radio" name="THprofile_regpolicy" {if $THprofile_regpolicy==1} checked="checked"{/if} value="1">Manually Approve
				<input type="radio" name="THprofile_regpolicy" {if $THprofile_regpolicy==2} checked="checked"{/if} value="2">Automatically Approve
				<br />
				Profile view policy: 
				<input type="radio" name="THprofile_viewuserpolicy" {if $THprofile_viewuserpolicy==1} checked="checked"{/if} value="1">Logged in users
				<input type="radio" name="THprofile_viewuserpolicy" {if $THprofile_viewuserpolicy==2} checked="checked"{/if} value="2">Anyone
				<input type="radio" name="THprofile_viewuserpolicy" {if $THprofile_viewuserpolicy==3} checked="checked"{/if} value="3">Mods/Admins only
				<br />
				{math equation="x/3600" x=$THprofile_cookietime assign="cookietime"} 
				Cookies last: <input type="text" name="THprofile_cookietime" size="3" value="{$cookietime}" />hours<br />
				Cookies path: <input type="text" name="THprofile_cookiepath" size="24" value="{$THprofile_cookiepath}" /> (should be left as / except under special circumstances<br />
				Send welcome email: <input type="checkbox" name="THprofile_emailwelcome"{if $THprofile_emailwelcome} checked="checked"{/if} /><br />
				Enforce lowercase usernames: <input type="checkbox" name="THprofile_lcnames"{if $THprofile_lcnames} checked="checked"{/if} /><br />
				Maximum profile image size: <input type="text" name="THprofile_maxpicsize" size="6" value="{$THprofile_maxpicsize}" />bytes<br />
			</div>
			<input type="submit" value="Submit" />
		</div>
</div>

</form>
{include file=admin-foot.tpl}