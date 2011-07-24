{include file=head.tpl}
<title>{$THname} &#8212; Editing profile of {$user.username}</title>
</head>
<body>
    <div id="main">
        <div class="box">

            <div class="pgtitle">User profile: {$user.username}</div><div>

                {if $imgErrString}
                    <div style="color: red;">{$imgErrString}</div>
                {/if}

                {if $passErrString}
                    <div style="color: red;">{$passErrString}</div>
                {/if}

                <form id="profileedit" action="{$THurl}profiles.php?action=edit&amp;user={$user.username}" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="edit_update" value="1" />
                    <table><tr><td style="width: 50%;">

                                {if $user.has_picture}
                                    <img src="{$THurl}images/profiles/{$user.username}.{$user.has_picture}"
                                         align="left" alt="User profile picture" />
                                    <input type="checkbox" name="remove_picture" value="1" /> Remove picture
                                {else}
                                    <img src="{$THurl}static/nopicture.png" align="left" alt="No picture provided" />
                                {/if}
                            </td></tr><tr><td style="width: 50%;">
                            {if $user.pic_pending}
                                    <img src="{$THurl}static/time.png" alt="Picture pending" />{$user.username} has a picture awating admin approval.
                                {else}
                                    <strong>Upload a new picture: </strong></td><td><input type="file" name="picture" /></td></tr>
                            <tr><td colspan=2>To be displayed on the main site, it first must be manually approved by an admin.
                                    File must be a JPEG, GIF, or PNG no larger than 500x500 or {$maxProfImgSize} bytes.
                                    If the image is too large, it will be resized.</td></tr>
                                {/if}

                        {if $user.capcode}
                            <tr><td>

                                    {if $capcode} 
                                        <strong>Current capcode displays as:</strong></td><td>{$capcode}
                                    </td></tr><tr><td>
                                {/if}

                                    {if $user.proposed_capcode}}
                                        <strong>Capcode awaiting approval:</strong></td><td>{$user.proposed_capcode}
                                    {else}
                                        <strong>Propose a capcode:</strong></td><td><input type="text" name="capcode" length="128" maxlength="128" />
                                        <span style="font-style: italic; font-size: smaller;">(admin approval required)</span>
                                    {/if}
                                </td></tr>
                            {/if}

                        <tr><td>
                                <strong>Gender:</strong>
                            </td>
                            <td>
                                <select name="gender">
                                    <option value="U" {if $user.gender == "U" || !$user.gender}selected="selected"{/if}>--</option> 
                                    <option value="M" {if $user.gender == "M"}selected="selected"{/if}>M</option> 
                                    <option value="F" {if $user.gender == "F"}selected="selected"{/if}>F</option> 
                                </select>
                            </td></tr>
                        <tr><td>
                                <strong>Age:</strong>
                            </td>
                            <td>
                                <input type="text" name="age" value="{$user.age|escape}" length="3" maxlength="3" />
                            </td></tr>
                        <tr><td>
                                <strong>Location:</strong>
                            </td>
                            <td>
                                <input type="text" name="location" value="{$user.location|escape}"/>
                            </td></tr>
                        <tr><td>
                                <strong>Contact information:</strong>
                            </td>
                            <td>
                                <input type="text" name="contact" value="{$user.contact|escape}"/>
                            </td></tr>
                        <tr><td>
                                <strong>Description:</strong>
                            </td>
                            <td>
                                <textarea name="description" rows="5" columns="30">
                                    {$user.description|escape}
                                </textarea>
                            </td></tr>
                            {if $sessUsername == $user.username}
                            <tr><td>
                                    <strong>Password:</strong>
                                </td>
                                <td><input type="password" name="password" />
                                    (Confirm <input type="checkbox" name="changepass" value="1">)
                                </td>
                            </tr>
                            {/if}
                            </table>
                            <input type="submit" value="Submit" id="subbtn" /></form><br />
                        [<a href="{$THurl}profiles.php?action=viewprofile&amp;user={$user.username}">User profile</a>]
                    </div>
                    {include file=bottombar.tpl}