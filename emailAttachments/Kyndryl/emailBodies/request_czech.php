<?php
$emailBody = <<<endOfEmailBody
<p>Hello &&candidate_first_name&&</p>
<p>We believe you are about to engage on a UKI FSS account.  Kyndryl are contractually obliged to ensure that anyone engaging on any FSS customer accounts are PES cleared.  To allow us to process this requirement, please return the below required documents to us at your earliest convenience.</p>
<ul>
<li>Fully completed Application Form(s) :<br/>&&name_of_application_form&&</li>
<li>A Certified copy of the photo page of your Passport or ID Card along with your VISA/Work Permit if required</li>
<li>If your ID card does not contain your current address, please send a certified copy of a utility bill/Bank statement (less than 3 months old) - as evidence of your current address</li>
<li>A Criminal record check report, conducted by yourself via Czechpoint office and HR (dated no more than 3 months in the past)*</li>
<li>An email from your Manager confirming your Service Reference Date <b>OR</b> a screen print from the relevant page of the appropriate system (eg. WorkDay).<br/>*<b>Further information and evidence</b> may be required *</li>
<p><b>The Certification MUST be done by a Kynryl/IBM'er<b>, to confirm that they have seen the original document. Either physically by another Kydryl/IBM’er or Virtually (over webex) by your Manager.<p> For Physical - The following statement should be added on each document, on the same side as the image.</p>
<h4><span style='color:red'><center>True & Certified Copy<br/>Name of certifier in BLOCK CAPITALS<br/>Kyndryl/IBM Serial number of certifier<br/>Certification Date<br/>Signature of certifier</center></span></h4>
<p>or Virtual - an email can be sent directly to the PES team by your certifier <b>to confirm that they have seen the original document. Either physically or Virtually (over webex).<p> For Virtual - an email can be sent directly to the PES team, by your certifier <b>to confirm each original documents/screens (in a list) which have been viewed, over video conferencing, along with the date and time they were viewed.<b> OR Physical - The following statement should be added on each document, on the same side as the image.</p>
<h4><span style='color:red'><center>True & Certified Copy<br/>Name of certifier in BLOCK CAPITALS<br/>IBM Serial number of certifier<br/>Certification Date<br/>Signature of certifier</center></span></h4>
<p>If you have any questions, you do not have any of the listed documents or are unsure about the process please contact the PES Team.</p>
<p>Many Thanks for your cooperation,</p>
<p>&&account_name&& &nbsp; PES Team</p>
<p>Rachele Smith - Global PES Compliance Officer<br/>Zoe O'Flaherty - Global PES compliance Officer<br/>Jean Dover - Global PES Team Leader<br/>Carra Booth - Global PES SME</p>
<p><small>Phone: 44-131 656 0870 | Tie-Line: 37 580870<br/>E-mail: <a href='mailto:&&pestaskid&&'>&&pestaskid&&</a></small></p>
<p>IBM UK PES Team<br/>Atria One, 5th Floor<br/>144 Morrison Street, Edinburgh, EH3 8EX</br>United Kingdom</br>**Please input IBM UK Ltd, as we share a building**</p>
endOfEmailBody;