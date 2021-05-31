<?php
use itdq\PlannedOutages;
use itdq\Navbar;
use itdq\NavbarMenu;
use itdq\NavbarOption;
use itdq\NavbarDivider;

include ('itdq/PlannedOutages.php');
include ('itdq/DbTable.php');
$plannedOutagesLabel = "Planned Outages";
$plannedOutages = new PlannedOutages();
include ('UserComms/responsiveOutages_V2.php');

$navBarImage = ""; //a small image to displayed at the top left of the nav bar
$navBarBrand = array(strtoupper($_ENV['environment']),"index.php");
$navBarSearch = false;

$pageDetails = explode("/", $_SERVER['PHP_SELF']);
$page = isset($pageDetails[2]) ? $pageDetails[2] : $pageDetails[1];

$navbar = new Navbar($navBarImage, $navBarBrand,$navBarSearch);

$cdiAdmin       = new NavbarMenu("CDI Admin");
$trace          = new NavbarOption('View Trace','pi_trace.php','accessCdi');
$traceControl   = new NavbarOption('Trace Control','pi_traceControl.php','accessCdi');
$traceDelete    = new NavbarOption('Trace Deletion', 'pi_traceDelete.php','accessCdi');
$test           = new NavbarOption('Test', 'CDI_Test.php','accessCdi');
$cdiAdmin->addOption($trace);
$cdiAdmin->addOption($traceControl);
$cdiAdmin->addOption($traceDelete);
$cdiAdmin->addOption($test);


$admin          = new NavBarMenu("uPES Admin");
$accounts       = new NavBarOption('Manage Accounts','pa_manageAccounts.php','accessCdi accessPesTeam');
$contracts      = new NavBarOption('Manage Contracts','pa_manageContracts.php','accessCdi accessPesTeam');
$pesLevels      = new NavBarOption('Manage Pes Levels','pa_managePesLevels.php','accessCdi accessPesTeam');
$countries      = new NavBarOption('Manage Countries','pa_manageCountries.php','accessCdi accessPesTeam');
$tracker        = new NavBarOption('Tracker','pc_pesTracker.php','accessCdi accessPesTeam');
$mailConvert    = new NavBarOption('Notes ID to Email','pa_mailConvert.php','accessCdi accessPesTeam');
$manualStatus   = new NavBarOption('Manual Status update','pa_statusUpdate.php','accessCdi accessPesTeam');
$pesStatusAudit = new NavBarOption('PES Status Change Log','pa_statusChangeLog.php','accessCdi accessPesTeam');
$BPLookUp       = new NavbarOption('Blue Pages Lookup Form','pa_BPLookupForm.php','accessCdi accessPesTeam');
$admin->addOption($accounts);
$admin->addOption($contracts);
$admin->addOption($pesLevels);
$admin->addOption($countries);
$admin->addOption($tracker);
$admin->addOption($mailConvert);
$admin->addOption($manualStatus);
$admin->addOption($pesStatusAudit);
$admin->addOption($BPLookUp);

$user          = new NavBarMenu("uPES",'accessCdi accessPesTeam accessUser ' );
$userAdd       = new NavBarOption('Add to PES','pu_userAdd.php','accessCdi accessPesTeam accessUser ');
$userBoard     = new NavBarOption('Board to Contract','pu_userBoard.php','accessCdi accessPesTeam accessUser ');
$userStatus    = new NavBarOption('Status Report','pa_userStatus.php','accessCdi accessPesTeam accessUser ');
$user->addOption($userAdd);
$user->addOption($userBoard);
$user->addOption($userStatus);

$reports          = new NavBarMenu("Reports",'accessCdi accessPesTeam accessReports' );
$overviewByAccount= new NavbarOption('By Account', 'pr_byAccount.php','accessCdi accessPesTeam accessReports');
$processStatus    = new NavbarOption('Process Status', 'pr_processStatus.php','accessCdi accessPesTeam accessReports');
$recheckUpcoming  = new NavbarOption('Upcoming Rechecks', 'pr_upcomingRechecks.php','accessCdi accessPesTeam');
$miCleared        = new NavbarOption('MI Cleared', 'pr_miReportCleared.php','accessCdi accessPesTeam');
$miProvCleared    = new NavbarOption('MI Prov Cleared', 'pr_miReportProvCleared.php','accessCdi accessPesTeam');

$reports->addOption($overviewByAccount);
$reports->addOption($processStatus);
$reports->addOption($recheckUpcoming);
$reports->addOption($miCleared);
$reports->addOption($miProvCleared);

$navbar->addMenu($cdiAdmin);
$navbar->addMenu($admin);
$navbar->addMenu($user);
$navbar->addMenu($reports);

$outages = new NavbarOption($plannedOutagesLabel, 'ppo_PlannedOutages.php','accessCdi accessPesTeam accessUser');
$navbar->addOption($outages);

$navbar->createNavbar($page);

$isCdi       = employee_in_group($_SESSION['cdiBg'],  $_SESSION['ssoEmail']) ? ".not('.accessCdi')" : null;
$isPesTeam   = employee_in_group($_SESSION['pesTeamBg'],  $_SESSION['ssoEmail']) ? ".not('.accessPesTeam')" : null;
$isUser      = ".not('.accessUser')";

$isCdi        = stripos($_ENV['environment'], 'dev') ? ".not('.accessCdi')"        : $isCdi;
$isPesTeam    = stripos($_ENV['environment'], 'dev')  ? ".not('.accessPesTeam')"   : $isPesTeam;
$isUser       = stripos($_ENV['environment'], 'dev')  ? ".not('.accessUser')"      : $isUser;


$_SESSION['isCdi']       = !empty($isCdi)     ? true : false;
$_SESSION['isPesTeam']   = !empty($isPesTeam) ? true : false;
$_SESSION['isUser']      = !empty($isUser)    ? true : false;

$plannedOutagesId = str_replace(" ","_",$plannedOutagesLabel);

?>
<script>
$('.navbarMenuOption')<?=$isCdi?><?=$isPesTeam?><?=$isUser?>.remove();
$('.navbarMenu').not(':has(li)').remove();

$('li[data-pagename="<?=$page;?>"]').addClass('active').closest('li.dropdown').addClass('active');
<?php



if($page != "index.php" && substr($page,0,3)!='cdi'){

    ?>
    var pageAllowed = $('li[data-pagename="<?=$page;?>"]').length;

	if(pageAllowed==0 ){
		window.location.replace('index.php');
		alert("You do not have access to:<?=$page?>");
	}
	<?php
}

?>

$(document).ready(function () {

    $('button.accessRestrict')<?=$isCdi?><?=$isPesTeam?><?=$isUser?>.remove();
    $('li.accessRestrict')<?=$isCdi?><?=$isPesTeam?><?=$isUser?>.remove();

    <?=!empty($isUser)      ? '$("#userLevel").html("User");console.log("user");' : null;?>
    <?=!empty($isPesTeam)   ? '$("#userLevel").html("Pes Team");console.log("pes");' : null;?>
    <?=!empty($isCdi)       ? '$("#userLevel").html("CDI");console.log("CDI");' : null;?>

    var poContent = $('#<?=$plannedOutagesId?> a').html();
	var badgedContent = poContent + "&nbsp;" + "<?=$plannedOutages->getBadge();?>";
	$('#<?=$plannedOutagesId?> a').html(badgedContent);

});
</script>

