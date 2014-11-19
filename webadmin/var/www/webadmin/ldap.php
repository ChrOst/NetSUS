<?php

include "inc/config.php";
include "inc/auth.php";
include "inc/functions.php";

$ldaperror = "";
$ldapsuccess = "";


$title = "LDAP";


if (isset($_POST['saveLDAPConfiguration'])) {
	if ($_POST['searchdn'] == "") {
		$ldaperror = "Specify Searchdn.";
	}
	else if ($_POST['domain'] == "") {
		$ldaperror = "Specify a domain.";
	}
	else {
		$conf->setLdapSetting("searchdn", $_POST['searchdn']);
		$conf->setLdapSetting("domain", $_POST['domain']);
		$ldapsuccess = "Saved LDAP configuration.";
	}
}

if (isset($_POST['addgroup']) && isset($_POST['newgroup']) && $_POST['newgroup'] != "") {
	$conf->addLdapGroup($_POST['newgroup']);
}

if (isset($_GET['deletegroup']) && $_GET['deletegroup'] != "") {
	$conf->deleteLdapGroup($_GET['deletegroup']);
}

include "inc/header.php";

if ($ldaperror != "") {
	echo "<div class=\"errorMessage\">ERROR: " . $ldaperror . "</div>";
}
if ($ldapsuccess != "") {
	echo "<div class=\"successMessage\">" . $ldapsuccess . "</div>";
}
?>
<script>
function validateLDAPGroup() {
	if (document.getElementById("newgroup").value != "") {
		document.getElementById("addgroup").disabled = false;
	}
	else {
		document.getElementById("addgroup").disabled = true;
	}
}
</script>
<h2>LDAP</h2>
<div id="form-wrapper">
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" name="LDAP" id="LDAP">
		<div id="form-inside">
			<input type="hidden" name="userAction" value="LDAP">
			<span class="label">Domain</span>
			<input type="text" name="domain" id="domain" value="<?php echo $conf->getLdapSetting('domain'); ?>" />
			<br />
			<span class="label">Searchdn</span>
			<input type="text" name="searchdn" id="searchdn" value="<?php echo $conf->getLdapSetting('searchdn');?>" />
			<br />
			<input type="submit" value="Save" name="saveLDAPConfiguration" id="saveLDAPConfiguration" class="insideActionButton" />
			<br />
			<br />
			<span class="label">Administration Groups</span>
			<input type="text" name="newgroup" id="newgroup" value="" onKeyUp="validateLDAPGroup();" onChange="validateLDAPGroup();" />
			<input type="submit" name="addgroup" id="addgroup" class="insideActionButton" value="Add" disabled="disabled" />
			<br />
			<table class="branchesTable">
				<tr>
					<th>Administrator</th>
					<th></th>
				</tr>
				<?php foreach($conf->getLdapSetting('groups') as $key => $Group) { ?>
						<tr class="<?=($key % 2 == 0 ? "object0" : "object1")?>">
							<td><?php echo $Group?></td>
							<td><a href="ldap.php?service=LDAP&deleteGroup=<?php echo urlencode($Group)?>">Delete</a>
						</tr>
				<?php } ?>
			</table>
		</div>
		<div id="form-buttons">
			<div id="read-buttons">
				<input type="button" id="back-button" name="action" class="alternativeButton" value="Back" onclick="document.location.href='settings.php'">
			</div>
		</div>
	</form>
</div>


<?php include("inc/footer.php"); ?>

