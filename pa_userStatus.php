<?php

use itdq\Trace;
use upes\AllTables;
use upes\PesLevelTable;
use upes\ContractTable;
use itdq\Loader;

Trace::pageOpening($_SERVER['PHP_SELF']);

$loader = new Loader();
$allCountries = $loader->load('COUNTRY',AllTables::$COUNTRY);
unset($allCountries['UK']);
unset($allCountries['India']);

$pesLevelByAccount  = PesLevelTable::prepareJsonArraysForPesSelection();
$allContractAccountMapping = ContractTable::prepareJsonObjectMappingContractToAccount();

$isCdi     = $_SESSION['isCdi']     ? ".not('.accessCdi')"       : null; 
$isPesTeam = $_SESSION['isPesTeam'] ? ".not('.accessPesTeam')"   : null;
$isUser    = $_SESSION['isUser']    ? ".not('.accessUser')"      : null;
?>
<div class='container'>

<?php

include_once 'includes/modalEditPersonRecord.html';
include_once 'includes/modalEditPesLevel.html';
include_once 'includes/modalCancelPesRequestConfirm.html';

?>
</div>

<div class='container-fluid	'>

<div class='col-sm-8 col-sm-offset-2'>
<h2>User Status Report</h2>

<table id='userStatusTable' class='table table-responsive table-striped' >
<thead>
<tr><th>Action</th><th>Email</th><th>CNUM</th><th>Full Name</th><th >Account</th><th >Contract</th><th >Country Of Residence</th><th>Requestor</th><th>Requested</th><th >Pes Level</th><th >Pes Description</th><th>Process Status</th><th>Process Status Changed</th><th >Pes Status</th><th>Cleared Date</th><th>Date Last Chased</th><th>Off Boarded Date</th><th>Off Boarded By</th></tr>
</thead>
</table>
</div>
</div>

<script type="text/javascript">

var countryCodes =[{ "text":"India", "id":"IN"},{ "text":"United Kingdom of Great Britain and Northern Ireland (the)", "id":"GB"}, { "text":"Afghanistan", "id":"AF"}, { "text":"Albania", "id":"AL"}, { "text":"Algeria", "id":"DZ"}, { "text":"American Samoa", "id":"AS"}, { "text":"Andorra", "id":"AD"}, { "text":"Angola", "id":"AO"}, { "text":"Anguilla", "id":"AI"}, { "text":"Antarctica", "id":"AQ"}, { "text":"Antigua and Barbuda", "id":"AG"}, { "text":"Argentina", "id":"AR"}, { "text":"Armenia", "id":"AM"}, { "text":"Aruba", "id":"AW"}, { "text":"Australia", "id":"AU"}, { "text":"Austria", "id":"AT"}, { "text":"Azerbaijan", "id":"AZ"}, { "text":"Bahamas (the)", "id":"BS"}, { "text":"Bahrain", "id":"BH"}, { "text":"Bangladesh", "id":"BD"}, { "text":"Barbados", "id":"BB"}, { "text":"Belarus", "id":"BY"}, { "text":"Belgium", "id":"BE"}, { "text":"Belize", "id":"BZ"}, { "text":"Benin", "id":"BJ"}, { "text":"Bermuda", "id":"BM"}, { "text":"Bhutan", "id":"BT"}, { "text":"Bolivia (Plurinational State of)", "id":"BO"}, { "text":"Bonaire, Sint Eustatius and Saba", "id":"BQ"}, { "text":"Bosnia and Herzegovina", "id":"BA"}, { "text":"Botswana", "id":"BW"}, { "text":"Bouvet Island", "id":"BV"}, { "text":"Brazil", "id":"BR"}, { "text":"British Indian Ocean Territory (the)", "id":"IO"}, { "text":"Brunei Darussalam", "id":"BN"}, { "text":"Bulgaria", "id":"BG"}, { "text":"Burkina Faso", "id":"BF"}, { "text":"Burundi", "id":"BI"}, { "text":"Cabo Verde", "id":"CV"}, { "text":"Cambodia", "id":"KH"}, { "text":"Cameroon", "id":"CM"}, { "text":"Canada", "id":"CA"}, { "text":"Cayman Islands (the)", "id":"KY"}, { "text":"Central African Republic (the)", "id":"CF"}, { "text":"Chad", "id":"TD"}, { "text":"Chile", "id":"CL"}, { "text":"China", "id":"CN"}, { "text":"Christmas Island", "id":"CX"}, { "text":"Cocos (Keeling) Islands (the)", "id":"CC"}, { "text":"Colombia", "id":"CO"}, { "text":"Comoros (the)", "id":"KM"}, { "text":"Congo (the Democratic Republic of the)", "id":"CD"}, { "text":"Congo (the)", "id":"CG"}, { "text":"Cook Islands (the)", "id":"CK"}, { "text":"Costa Rica", "id":"CR"}, { "text":"Croatia", "id":"HR"}, { "text":"Cuba", "id":"CU"}, { "text":"Curacao", "id":"CW"}, { "text":"Cyprus", "id":"CY"}, { "text":"Czechia", "id":"CZ"}, { "text":"Cote d'Ivoire", "id":"CI"}, { "text":"Denmark", "id":"DK"}, { "text":"Djibouti", "id":"DJ"}, { "text":"Dominica", "id":"DM"}, { "text":"Dominican Republic (the)", "id":"DO"}, { "text":"Ecuador", "id":"EC"}, { "text":"Egypt", "id":"EG"}, { "text":"El Salvador", "id":"SV"}, { "text":"Equatorial Guinea", "id":"GQ"}, { "text":"Eritrea", "id":"ER"}, { "text":"Estonia", "id":"EE"}, { "text":"Eswatini", "id":"SZ"}, { "text":"Ethiopia", "id":"ET"}, { "text":"Falkland Islands (the) [Malvinas]", "id":"FK"}, { "text":"Faroe Islands (the)", "id":"FO"}, { "text":"Fiji", "id":"FJ"}, { "text":"Finland", "id":"FI"}, { "text":"France", "id":"FR"}, { "text":"French Guiana", "id":"GF"}, { "text":"French Polynesia", "id":"PF"}, { "text":"French Southern Territories (the)", "id":"TF"}, { "text":"Gabon", "id":"GA"}, { "text":"Gambia (the)", "id":"GM"}, { "text":"Georgia", "id":"GE"}, { "text":"Germany", "id":"DE"}, { "text":"Ghana", "id":"GH"}, { "text":"Gibraltar", "id":"GI"}, { "text":"Greece", "id":"GR"}, { "text":"Greenland", "id":"GL"}, { "text":"Grenada", "id":"GD"}, { "text":"Guadeloupe", "id":"GP"}, { "text":"Guam", "id":"GU"}, { "text":"Guatemala", "id":"GT"}, { "text":"Guernsey", "id":"GG"}, { "text":"Guinea", "id":"GN"}, { "text":"Guinea-Bissau", "id":"GW"}, { "text":"Guyana", "id":"GY"}, { "text":"Haiti", "id":"HT"}, { "text":"Heard Island and McDonald Islands", "id":"HM"}, { "text":"Holy See (the)", "id":"VA"}, { "text":"Honduras", "id":"HN"}, { "text":"Hong Kong", "id":"HK"}, { "text":"Hungary", "id":"HU"}, { "text":"Iceland", "id":"IS"},  { "text":"Indonesia", "id":"ID"}, { "text":"Iran (Islamic Republic of)", "id":"IR"}, { "text":"Iraq", "id":"IQ"}, { "text":"Ireland", "id":"IE"}, { "text":"Isle of Man", "id":"IM"}, { "text":"Israel", "id":"IL"}, { "text":"Italy", "id":"IT"}, { "text":"Jamaica", "id":"JM"}, { "text":"Japan", "id":"JP"}, { "text":"Jersey", "id":"JE"}, { "text":"Jordan", "id":"JO"}, { "text":"Kazakhstan", "id":"KZ"}, { "text":"Kenya", "id":"KE"}, { "text":"Kiribati", "id":"KI"}, { "text":"Korea (the Democratic People's Republic of)", "id":"KP"}, { "text":"Korea (the Republic of)", "id":"KR"}, { "text":"Kuwait", "id":"KW"}, { "text":"Kyrgyzstan", "id":"KG"}, { "text":"Lao People's Democratic Republic (the)", "id":"LA"}, { "text":"Latvia", "id":"LV"}, { "text":"Lebanon", "id":"LB"}, { "text":"Lesotho", "id":"LS"}, { "text":"Liberia", "id":"LR"}, { "text":"Libya", "id":"LY"}, { "text":"Liechtenstein", "id":"LI"}, { "text":"Lithuania", "id":"LT"}, { "text":"Luxembourg", "id":"LU"}, { "text":"Macao", "id":"MO"}, { "text":"Madagascar", "id":"MG"}, { "text":"Malawi", "id":"MW"}, { "text":"Malaysia", "id":"MY"}, { "text":"Maldives", "id":"MV"}, { "text":"Mali", "id":"ML"}, { "text":"Malta", "id":"MT"}, { "text":"Marshall Islands (the)", "id":"MH"}, { "text":"Martinique", "id":"MQ"}, { "text":"Mauritania", "id":"MR"}, { "text":"Mauritius", "id":"MU"}, { "text":"Mayotte", "id":"YT"}, { "text":"Mexico", "id":"MX"}, { "text":"Micronesia (Federated States of)", "id":"FM"}, { "text":"Moldova (the Republic of)", "id":"MD"}, { "text":"Monaco", "id":"MC"}, { "text":"Mongolia", "id":"MN"}, { "text":"Montenegro", "id":"ME"}, { "text":"Montserrat", "id":"MS"}, { "text":"Morocco", "id":"MA"}, { "text":"Mozambique", "id":"MZ"}, { "text":"Myanmar", "id":"MM"}, { "text":"Namibia", "id":"NA"}, { "text":"Nauru", "id":"NR"}, { "text":"Nepal", "id":"NP"}, { "text":"Netherlands (the)", "id":"NL"}, { "text":"New Caledonia", "id":"NC"}, { "text":"New Zealand", "id":"NZ"}, { "text":"Nicaragua", "id":"NI"}, { "text":"Niger (the)", "id":"NE"}, { "text":"Nigeria", "id":"NG"}, { "text":"Niue", "id":"NU"}, { "text":"Norfolk Island", "id":"NF"}, { "text":"Northern Mariana Islands (the)", "id":"MP"}, { "text":"Norway", "id":"NO"}, { "text":"Oman", "id":"OM"}, { "text":"Pakistan", "id":"PK"}, { "text":"Palau", "id":"PW"}, { "text":"Palestine, State of", "id":"PS"}, { "text":"Panama", "id":"PA"}, { "text":"Papua New Guinea", "id":"PG"}, { "text":"Paraguay", "id":"PY"}, { "text":"Peru", "id":"PE"}, { "text":"Philippines (the)", "id":"PH"}, { "text":"Pitcairn", "id":"PN"}, { "text":"Poland", "id":"PL"}, { "text":"Portugal", "id":"PT"}, { "text":"Puerto Rico", "id":"PR"}, { "text":"Qatar", "id":"QA"}, { "text":"Republic of North Macedonia", "id":"MK"}, { "text":"Romania", "id":"RO"}, { "text":"Russian Federation (the)", "id":"RU"}, { "text":"Rwanda", "id":"RW"}, { "text":"Reunion", "id":"RE"}, { "text":"Saint Barthelemy", "id":"BL"}, { "text":"Saint Helena, Ascension and Tristan da Cunha", "id":"SH"}, { "text":"Saint Kitts and Nevis", "id":"KN"}, { "text":"Saint Lucia", "id":"LC"}, { "text":"Saint Martin (French part)", "id":"MF"}, { "text":"Saint Pierre and Miquelon", "id":"PM"}, { "text":"Saint Vincent and the Grenadines", "id":"VC"}, { "text":"Samoa", "id":"WS"}, { "text":"San Marino", "id":"SM"}, { "text":"Sao Tome and Principe", "id":"ST"}, { "text":"Saudi Arabia", "id":"SA"}, { "text":"Senegal", "id":"SN"}, { "text":"Serbia", "id":"RS"}, { "text":"Seychelles", "id":"SC"}, { "text":"Sierra Leone", "id":"SL"}, { "text":"Singapore", "id":"SG"}, { "text":"Sint Maarten (Dutch part)", "id":"SX"}, { "text":"Slovakia", "id":"SK"}, { "text":"Slovenia", "id":"SI"}, { "text":"Solomon Islands", "id":"SB"}, { "text":"Somalia", "id":"SO"}, { "text":"South Africa", "id":"ZA"}, { "text":"South Georgia and the South Sandwich Islands", "id":"GS"}, { "text":"South Sudan", "id":"SS"}, { "text":"Spain", "id":"ES"}, { "text":"Sri Lanka", "id":"LK"}, { "text":"Sudan (the)", "id":"SD"}, { "text":"Suriname", "id":"SR"}, { "text":"Svalbard and Jan Mayen", "id":"SJ"}, { "text":"Sweden", "id":"SE"}, { "text":"Switzerland", "id":"CH"}, { "text":"Syrian Arab Republic", "id":"SY"}, { "text":"Taiwan (Province of China)", "id":"TW"}, { "text":"Tajikistan", "id":"TJ"}, { "text":"Tanzania, United Republic of", "id":"TZ"}, { "text":"Thailand", "id":"TH"}, { "text":"Timor-Leste", "id":"TL"}, { "text":"Togo", "id":"TG"}, { "text":"Tokelau", "id":"TK"}, { "text":"Tonga", "id":"TO"}, { "text":"Trinidad and Tobago", "id":"TT"}, { "text":"Tunisia", "id":"TN"}, { "text":"Turkey", "id":"TR"}, { "text":"Turkmenistan", "id":"TM"}, { "text":"Turks and Caicos Islands (the)", "id":"TC"}, { "text":"Tuvalu", "id":"TV"}, { "text":"Uganda", "id":"UG"}, { "text":"Ukraine", "id":"UA"}, { "text":"United Arab Emirates (the)", "id":"AE"},  { "text":"United States Minor Outlying Islands (the)", "id":"UM"}, { "text":"United States of America (the)", "id":"US"}, { "text":"Uruguay", "id":"UY"}, { "text":"Uzbekistan", "id":"UZ"}, { "text":"Vanuatu", "id":"VU"}, { "text":"Venezuela (Bolivarian Republic of)", "id":"VE"}, { "text":"Viet Nam", "id":"VN"}, { "text":"Virgin Islands (British)", "id":"VG"}, { "text":"Virgin Islands (U.S.)", "id":"VI"}, { "text":"Wallis and Futuna", "id":"WF"}, { "text":"Western Sahara", "id":"EH"}, { "text":"Yemen", "id":"YE"}, { "text":"Zambia", "id":"ZM"}, { "text":"Zimbabwe", "id":"ZW"}, { "text":"Aland Islands", "id":"AX"}];
var countryOfResidence = [{text:"India", id:"India"},{text:"UK",id:"UK"},{text:"------",id:"------"}];
var accountContractLookup = <?=json_encode($allContractAccountMapping); ?>;
var pesLevelByAccount = <?=json_encode($pesLevelByAccount);?>;

<?php
foreach($allCountries as $country) {
    ?>
        countryOfResidence.push({ text: '<?=$country;?>', id: '<?=$country;?>' });
    <?php
};
?>

</script>
<?php
Trace::pageLoadComplete($_SERVER['PHP_SELF']);
?>