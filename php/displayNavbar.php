<?php
use itdq\PlannedOutages;
use itdq\Navbar;
use itdq\NavbarMenu;
use itdq\NavbarOption;
use itdq\NavbarDivider;
use itdq\OKTAGroups;

include ('itdq/PlannedOutages.php');
include ('itdq/DbTable.php');
$plannedOutagesLabel = "Planned Outages";
$plannedOutages = new PlannedOutages();
include ('UserComms/responsiveOutages_V2.php');

$navBarImage = ""; //a small image to displayed at the top left of the nav bar
$brandLabel = stripos($_ENV['environment'], 'newco') ? str_replace("_NEWCO","[Kyndryl]",strtoupper($_ENV['environment'])) : strtoupper($_ENV['environment']);
$brandLabel = str_replace("_UT"," UT ",$brandLabel);
$brandLabel = str_replace("_DEV"," DEV",$brandLabel);
$navBarSearch = false;

$pageDetails = explode("/", $_SERVER['PHP_SELF']);
$page = isset($pageDetails[2]) ? $pageDetails[2] : $pageDetails[1];

$navBarBrand = array($brandLabel,"index.php");
$navbar = new Navbar($navBarImage, $navBarBrand,$navBarSearch);

$cdiAdmin       = new NavbarMenu("CDI Admin");
$trace          = new NavbarOption('View Trace','pi_trace.php','accessCdi');
$traceControl   = new NavbarOption('Trace Control','pi_traceControl.php','accessCdi');
$traceDelete    = new NavbarOption('Trace Deletion', 'pi_traceDelete.php','accessCdi');
// $test           = new NavbarOption('Test', 'CDI_Test.php','accessCdi');
$cdiAdmin->addOption($trace);
$cdiAdmin->addOption($traceControl);
$cdiAdmin->addOption($traceDelete);
// $cdiAdmin->addOption($test);

$admin          = new NavBarMenu("uPES Admin");
$accounts       = new NavBarOption('Manage Accounts','pa_manageAccounts.php','accessCdi accessPesTeam');
$contracts      = new NavBarOption('Manage Contracts','pa_manageContracts.php','accessCdi accessPesTeam');
$pesLevels      = new NavBarOption('Manage Pes Levels','pa_managePesLevels.php','accessCdi accessPesTeam');
$countries      = new NavBarOption('Manage Countries','pa_manageCountries.php','accessCdi accessPesTeam');
$tracker        = new NavBarOption('Tracker','pc_pesTracker.php','accessCdi accessPesTeam');
$mailConvert    = new NavBarOption('Notes ID to Email','pa_mailConvert.php','accessCdi accessPesTeam');
$manualStatus   = new NavBarOption('Manual Status update','pa_statusUpdate.php','accessCdi accessPesTeam');
$pesStatusAudit = new NavBarOption('PES Status Change Log','pa_statusChangeLog.php','accessCdi accessPesTeam');
// $BPLookUp       = new NavbarOption('Blue Pages Lookup Form','pa_BPLookupForm.php','accessCdi accessPesTeam');
$uploadPerson   = new NavbarOption('Person Data Update Form','pc_otPersonDataUpload.php','accessCdi accessPesTeam');
$admin->addOption($accounts);
$admin->addOption($contracts);
$admin->addOption($pesLevels);
$admin->addOption($countries);
$admin->addOption($tracker);
$admin->addOption($mailConvert);
$admin->addOption($manualStatus);
$admin->addOption($pesStatusAudit);
// $admin->addOption($BPLookUp);
$admin->addOption($uploadPerson);

$user          = new NavBarMenu("uPES",'accessCdi accessPesTeam accessUser ' );
$userAdd       = new NavBarOption('Add to PES','pu_userAdd.php','accessCdi accessPesTeam accessUser ');
$userBoard     = new NavBarOption('Board to Contract','pu_userBoard.php','accessCdi accessPesTeam accessUser ');
$userStatus    = new NavBarOption('Status Report','pa_userStatus.php','accessCdi accessPesTeam accessUser ');
$user->addOption($userAdd);
$user->addOption($userBoard);
$user->addOption($userStatus);

$reports                    = new NavBarMenu("Reports",'accessCdi accessPesTeam accessReports' );
$overviewByAccount          = new NavbarOption('By Account', 'pr_byAccount.php','accessCdi accessPesTeam accessReports');
$overviewByContract         = new NavbarOption('By Contract - <b>NEW!</b>', 'pr_byContract.php','accessCdi accessPesTeam accessReports');
$processStatusByAccount     = new NavbarOption('Process Status By Account', 'pr_processStatus.php','accessCdi accessPesTeam accessReports');
$processStatusByContract    = new NavbarOption('Process Status By Contract - <b>NEW!</b>', 'pr_processStatusByContract.php','accessCdi accessPesTeam accessReports');
$recheckUpcomingByAccount   = new NavbarOption('Upcoming Rechecks By Account', 'pr_upcomingRechecks.php','accessCdi accessPesTeam');
$recheckUpcomingByContract  = new NavbarOption('Upcoming Rechecks By Contract - <b>NEW!</b>', 'pr_upcomingRechecksByContract.php','accessCdi accessPesTeam');
$miClearedByAccount         = new NavbarOption('MI Cleared By Account', 'pr_miReportCleared.php','accessCdi accessPesTeam');
$miClearedByContract        = new NavbarOption('MI Cleared By Contract - <b>NEW!</b>', 'pr_miReportClearedByContract.php','accessCdi accessPesTeam');
$miProvClearedByAccount     = new NavbarOption('MI Prov Cleared By Account', 'pr_miReportProvCleared.php','accessCdi accessPesTeam');
$miProvClearedByContract    = new NavbarOption('MI Prov Cleared By Contract - <b>NEW!</b>', 'pr_miReportProvClearedByContract.php','accessCdi accessPesTeam');

$reports->addOption($overviewByAccount);
$reports->addOption($overviewByContract);
$reports->addOption( new NavbarDivider('accessPesTeam accessCdi'));
$reports->addOption($processStatusByAccount);
$reports->addOption($processStatusByContract);
$reports->addOption( new NavbarDivider('accessPesTeam accessCdi'));
$reports->addOption($recheckUpcomingByAccount);
$reports->addOption($recheckUpcomingByContract);
$reports->addOption( new NavbarDivider('accessPesTeam accessCdi'));
$reports->addOption($miClearedByAccount);
$reports->addOption($miClearedByContract);
$reports->addOption( new NavbarDivider('accessPesTeam accessCdi'));
$reports->addOption($miProvClearedByAccount);
$reports->addOption($miProvClearedByContract);

$navbar->addMenu($cdiAdmin);
$navbar->addMenu($admin);
$navbar->addMenu($user);
$navbar->addMenu($reports);

$outages = new NavbarOption($plannedOutagesLabel, 'ppo_PlannedOutages.php','accessCdi accessPesTeam accessUser');
$navbar->addOption($outages);

$navbar->createNavbar($page);

$OKTAGroups = new OKTAGroups();
$isCdi       = $OKTAGroups->inAGroup($_SESSION['cdiBgAz'],  $_SESSION['ssoEmail']) ? ".not('.accessCdi')" : null;
$isPesTeam   = $OKTAGroups->inAGroup($_SESSION['pesTeamBgAz'],  $_SESSION['ssoEmail']) ? ".not('.accessPesTeam')" : null;
$isUser      = ".not('.accessUser')";

$isCdi        = (stripos($_ENV['environment'], 'dev') || stripos($_ENV['environment'], 'local')) ? ".not('.accessCdi')"     : $isCdi;
$isPesTeam    = (stripos($_ENV['environment'], 'dev') || stripos($_ENV['environment'], 'local')) ? ".not('.accessPesTeam')" : $isPesTeam;
$isUser       = (stripos($_ENV['environment'], 'dev') || stripos($_ENV['environment'], 'local')) ? ".not('.accessUser')"    : $isUser;

$_SESSION['isCdi']       = !empty($isCdi)     ? true : false;
$_SESSION['isPesTeam']   = !empty($isPesTeam) ? true : false;
$_SESSION['isUser']      = !empty($isUser)    ? true : false;

$plannedOutagesId = str_replace(" ","_",$plannedOutagesLabel);

?>
<script type="text/javascript">

$('.navbarMenuOption')<?=$isCdi?><?=$isPesTeam?><?=$isUser?>.remove();
$('.navbarMenu').not(':has(li)').remove();

$('li[data-pagename="<?=$page;?>"]').addClass('active').closest('li.dropdown').addClass('active');
<?php

if($page != "index.php" 
    && substr($page,0,3)!='cdi' 
    && $page != "dn_pesTrackerEmail.php"
    && $page != "dn_pesTrackerRecentEmail.php"
    && $page != "dn_pesTrackerActivePlusEmail.php"
){

    ?>
    var pageAllowed = $('li[data-pagename="<?=$page;?>"]').length;

	if(pageAllowed==0 ){
		window.location.replace('index.php');
		alert("You do not have access to:<?=$page?>");
	}
	<?php
}

?>

var isCdi = '<?=$_SESSION['isCdi'];?>'; 
var isPesTeam = '<?=$_SESSION['isPesTeam'];?>';
var isUser = '<?=$_SESSION['isUser'];?>';

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

