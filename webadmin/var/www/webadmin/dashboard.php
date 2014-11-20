<?php
/**
* NetworkInteraces may be something configureable in the future
* atleast the interfaces could be useful in multiple places
*/
$NetworkInterfaces = array("eth0");
include "inc/config.php";
include "inc/auth.php";
include "inc/functions.php";

$currentIP = trim(getCurrentIP());

$title = "Dashboard";

include "inc/header.php";
?>
<?php

if ($conf->needsToChangeAnyPasses())
{
?>
<span class="noticeMessage">WARNING: Credentials have not been changed for the following accounts:<br>
	<ul style="list-style-type: disc; padding-left:20px;">
		<?php
		if ($conf->needsToChangePass("webaccount"))
		{
			echo "<li>Web Application</li>\n";
		}
		if ($conf->needsToChangePass("shellaccount"))
		{
			echo "<li>Shell</li>\n";
		}
		if ($conf->needsToChangePass("afpaccount"))
		{
			echo "<li>AFP</li>\n";
		}
		if ($conf->needsToChangePass("smbaccount"))
		{
			echo "<li>SMB</li>\n";
		}
		?>
	</ul>
</span>
<?php
}
?>
<br>
	<div id="software-update-server">

		<h3>Software Update Server</h3>

		<div class="container">

			<ul>

				<li>
					<span>Last Sync:</span>
					<br>
					<br>
					<br>
					<span><?php if (trim(suExec("lastsussync")) != "") { print suExec("lastsussync"); } else { echo "Never"; } ?></span>
				</li>

				<li>
					<span>Sync Status:</span>
					<br>
					<br>
					<br>
					<span><?php if (getSyncStatus()) { echo "Running"; } else { echo "Not Running"; } ?></span>
				</li>

				<li>
					<span>Disk Usage:</span>
					<br>
					<br>
					<br>
					<span><?php echo suExec("getsussize"); ?></span>
				</li>

				<li>
					<span>Number of Branches:</span>
					<br>
					<br>
					<span><?php echo suExec("numofbranches"); ?></span>
				</li>

			</ul>

		</div>

	</div>


	<div id="netboot-server">

		<h3>NetBoot Server</h3>

		<div class="container">

			<ul>

				<li>
					<span>DHCP Status:</span>
					<br>
					<br>
					<br>
					<span><?php if (getNetBootStatus()) { echo "Running"; } else { echo "Not Running"; } ?></span>
				</li>

				<li>
					<span>Total NetBoot Image Size:</span>
					<br>
					<br>
					<span><?php echo suExec("netbootusage"); ?></span>
				</li>

				<li>
					<span>Number of Active SMB Connections:</span>
					<br>
					<br>
					<span><?php echo suExec("smbconns"); ?></span>
				</li>

				<li>
					<span>Number of Active AFP Connections:</span>
					<br>
					<br>
					<span><?php echo suExec("afpconns"); ?></span>
				</li>

				<li>
					<span>Shadow File Usage:</span>
					<br>
					<br>
					<span><?php echo suExec("shadowusage");?></span>
				</li>

			</ul>

		</div>

	</div>
	<div id="netboot-server">
	<?php
		/**
		* just copied the netboot-server style for showing the interface stats
		* this is kinda unclean so far since the style id "netboot-server" exists twice now
		* we use vnstat + vnstati here. vnstati generates the graphics.
		* be aware that this already works with multiple interfaces
		*/
		foreach ($NetworkInterfaces as $Interface ) {
	?>

		<h3>Network I/O - <?php echo $Interface; ?></h3>
		<div class="container" id="network-io">
			<ul>
				<li>
					<span><?php echo $Interface; ?>-hourly:</span>
					<br />
					<br />
					<br />
					<img src="/webadmin/images/vnstat/<?php echo $Interface; ?>_h.png" />
				</li>
				<li>
					<span><?php echo $Interface; ?>-summary:</span>
					<br />
					<br />
					<br />
					<img src="/webadmin/images/vnstat/<?php echo $Interface; ?>_s.png" />
				</li>
				<li>
					<span><?php echo $Interface; ?>-daily:</span>
					<br />
					<br />
					<br />
					<img src="/webadmin/images/vnstat/<?php echo $Interface; ?>_d.png" />
				</li>
				<li>
					<span><?php echo $Interface; ?>-monthly:</span>
					<br />
					<br />
					<br />
					<img src="/webadmin/images/vnstat/<?php echo $Interface; ?>_m.png" />
				</li>
			</ul>
		</div>
	</div>
<?php
	}
include "inc/footer.php";
?>
