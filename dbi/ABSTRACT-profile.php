<?php

/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-profile.php
	Description:    Abstract interface for a ThornProfileDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
	
*/

/**
 * This class exists to handle the database queries related
 * to profile management, not only including the addition
 * of users and the like, but also the updating of profiles,
 * the granting of permissions, and the login process.
 */
interface absThornProfileDBI
{
	/**
	 * Retrieve an assoc containing the userdata for a given user/pass combination (used for logins)
	 * 
	 * @param string $username The username of the (approved) profile to retrieve
	 * @param string $password The corresponding (unsalted) password
	 * 
	 * @return array Assoc containing user info (if name and password are valid), or null
	 */
	function getuserdata_login($username, $password);

	/**
	 * Retrieve an assoc containing the userdata for a given user/id combination (used for persistent logins)
	 * 
	 * @param string $username The username of the (approved) profile to retrieve
	 * @param string $id The corresponding user ID
	 * 
	 * @return array An assoc containing user info (if name and ID are valid), or null
	 */
	function getuserdata_cookielogin($username, $id);

	/**
	 * Retrieve an assoc containing the userdata for a given (approved) user
	 * 
	 * @param string $username The username of the (approved) profile to retrieve
	 * 
	 * @return array The user info in assoc-format, or null if there was no valid name
	 */
	function getuserdata($username);

	/**
	 * Update a profile to have a new ID and timestamp
	 * 
	 * @param string $username The username of the profile to update
	 * @param string $id The new ID value
	 */
	function updateuser($username, $id);

	/**
	 * Retrieve a list of all the users whose approval status is equal to 1
	 * (i.e. approved, not banned)
	 * 
	 * @return array An array of assoc-arrays containg individual users
	 */
	function getuserlist();

	/**
	 * Retrieves a capcodeto based on an incoming capcodefrom
	 * 
	 * @param string $capcode The capcodefrom associated with the capcodeto
	 * 
	 * @return string The capcodeto, or null if there is no match
	 */
	function getusercapcode($capcode);

	/**
	 * Retrieves the extension associated with a user's profile picture, if any
	 * 
	 * @param string $username The username of the target profile
	 * 
	 * @return string The extension of the picture, or null if there is none
	 */
	function getuserimage($username);

	/**
	 * Retrieves the extension associated with a user's PENDING profile picture, if any
	 * 
	 * @param string $username The username of the target profile
	 * 
	 * @return string The extension of the picture, or null if there is none
	 */
	function getpendinguserimage($username);
	
	/**
	 * Retrieve a user's email
	 * 
	 * @param string $username The username of the target profile
	 * 
	 * @return string The email for the username
	 */
	function getemail($username);

	/**
	 * Register a new user into the system
	 * 
	 * @param string $username The new user's name
	 * @param string $password The (unsalted) password for the new user
	 * @param int $userlevel The access level for the new user
	 * @param string $email The email address of the new user
	 * @param int $approved The initial approval status of the new user
	 * 
	 * @return resource The result of the myquery call
	 */
	function registeruser($username, $password, $userlevel, $email, $approved);

	/**
	 * Updates a given user's information.
	 * 
	 * @param string $username Username of the target profile
	 * @param string $age The user's age
	 * @param string $gender The user's gender
	 * @param string $location The user's location
	 * @param string $contact The user's contact information
	 * @param string $description The user's personal description
	 * @param string $picture_ext The extension of the user's profile picture
	 * @param string $picture_pending The extension of the currently proposed (but unapproved) profile picture
	 */
	function updateuserinfo($username, $age, $gender, $location, $contact, $description, $picture_ext, $picture_pending);

	/**
	 * Updates a given user's permissions.
	 * 
	 * @param string $username Username of the target profile
	 * @param int $admin The user's administrator status
	 * @param int $moderator The user's global moderator status
	 * @param int $userlevel The user's access level
	 * @param string $boards The boards a (per-board) moderator has access to
	 * @param string $capcode The capcode hash associated with this user
	 */
	function updateuserpermissions($username, $admin, $moderator, $userlevel, $boards, $capcode);

	/**
	 * Propose a new capcodeto for a given user, up to 128 chars long
	 * 
	 * @param string $username The username of the profile requesting a new capcode
	 * @param string $capcode The new proposed capcodeto
	 */
	function proposeusercapcode($username, $capcode);

	/**
	 * Set a user's password to the specified string
	 * 
	 * @param string $username The user whose password will be set
	 * @param string $password The (unsalted) password
	 */
	function setuserpass($username, $password);

	/**
	 * Suspend a user's account.
	 * 
	 * @param string $username The username to suspend
	 */
	function suspenduser($username);

	/**
	 * Check whether a user exists or not
	 * 
	 * @param string $username The username to check
	 * 
	 * @return bool Does the user exist?
	 */
	function userexists($username);

	/**
	 * Check whether a particular email has already been associated with an account
	 * 
	 * @param string $email The email to check
	 * 
	 * @return bool A boolean indicating if it was found in the database
	 */
	function emailexists($email);

	/**
	 * Check whether the currently logged in user can edit a given user's profile
	 * 
	 * @param string $username The username associated with the profile to check
	 * 
	 * @return bool A boolean indicating if the logged in user can edit
	 */
	function caneditprofile($username);
	
	/**
	 * Approve or deny a particular section of a user's profile, such
	 * as a proposed picture, a pending capcode, or a pending registration.
	 * 
	 * @param string $username The user account in question
	 * @param string $type The type of section to be approved.  Can be
	 * "account", "picture", or "capcode".  Defaults to "", which does nothing.
	 * @param bool $approved If this is an approval action (if not, it will be
	 * considered a denial)
	 */
	function approvalaction($username, $type="", $approved);
	
	/**
	 * Return a list of users who fulfill at least one of the following criteria:
	 * - Pending registration status
	 * - Pending picture proposal
	 * - Pending capcode proposal
	 * 
	 * @return array An array of assoc-arrays of user data (can be size 0)
	 */
	function getprofilemodqueue();

}
?>
