<?php

$access_user = new access_user($pdo);
$access_artist = new access_artist($pdo);
$access_release = new access_release($pdo);

include('../account/head.php');

	// Switch controller
	if(!$template && $_GET["page"] === "users") {
		$template = "users";
	}
	if(!$template && $_GET["action"] === "sign-out") {
		$template = "sign-out";
	}
	if(!$template && !empty($_GET["username"]) && $_GET["action"] === "download" && $_SESSION["username"] === $_GET["username"]) {
		$template = "download-collection";
	}
	if(!$template && !empty($_GET["username"])) {
		$user = $access_user->access_user(["username" => sanitize($_GET["username"]), "get" => "all"]);
		if(is_array($user) && !empty($user)) {
			$template = "user";
		}
		else {
			$error = "Sorry, that user doesn't exist. Showing all users instead.";
			$template = "users";
		}
	}
	if(!$template && $_SESSION["loggedIn"]) {
		if($_GET['page'] === 'edit-avatar') {
			$template = 'edit-avatar';
		}
		else {
			$user = $access_user->access_user(["username" => sanitize($_SESSION["username"]), "get" => "all"]);
			if(is_array($user) && !empty($user)) {
				$template = "account";
			}
			else {
				$error = "Sorry, that user doesn't exist. Showing all users instead.";
				$template = "users";
			}
		}
	}
	$template = strlen($template) ? $template : "sign-in";
	
	// User list
	// =======================================================
	if($template === "users") {
		$users = $access_user->access_user(["get" => "list"]);
		
		$pageTitle = "Member list";
		
		breadcrumbs([ "Member list" => "/users/" ]);
		
		include("page-users.php");
	}
	
	// Sign out
	// =======================================================
	if($template === "sign-out") {
		$pageTitle = "Sign out";
		
		breadcrumbs([ "Sign out" => "/sign-out/" ]);
		
		include("page-sign-out.php");
	}
	
	// Download collection
	// =======================================================
	if($template === "download-collection") {
		include("page-download-collection.php");
	}
	
	// User profile
	// =======================================================
	if($template === "user") {
		$pageTitle = $user["username"]." member profile";
		
		breadcrumbs([ "Member list" => "/users/", $user["username"] => "/users/".$user["username"]."/" ]);
		
		include('page-user.php');
	}
	
	// Edit profile
	// =======================================================
	if($template === "account") {
		$pageTitle = "Edit account";
		
		breadcrumbs([ "Member list" => "/users/", $user["username"] => "/users/".$user["username"]."/", "Edit" => "/account/edit/" ]);
		
		include("page-edit.php");
	}
	
	// Edit avatar
	// =======================================================
	if($template === 'edit-avatar') {
		$page_title = "Edit avatar";
		
		breadcrumbs([ "Member list" => "/users/", $user["username"] => "/users/".$user["username"]."/", "Edit" => "/account/edit/" ]);
		
		include("page-edit-avatar.php");
	}
	
	// Sign in/register
	// =======================================================
	if($template === "sign-in") {
		$pageTitle = "Sign in or register";
		
		breadcrumbs([ "Sign in/register" => "/account/" ]);
		
		include("page-sign-in-register.php");
	}
?>