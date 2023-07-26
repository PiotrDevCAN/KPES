<?php
$emailBody = <<<endOfEmailBody
<p>Hello &&candidate_first_name&&</p>
<p>We believe you are about to engage on a UKI FSS account.  Kyndryl are contractually obliged to ensure that anyone engaging on any FSS customer accounts are PES cleared.  To allow us to process this requirement, please return the attached Global Application Form at your earliest convenience.</p>
<ul>
<li>Once this is received a member of the PES team will be in contact to set up a webex meeting where the following documents will be viewed</li>
<li>Your ID Card (Front and Back)</li>
<li>Your workday screen showing your current address and Kyndryl start/service reference date</li>
<li>If your service reference date is less than 5 yeras in the past, further information may be requested by the PES team member on your call</li>
<p>If you have any questions, you do not have any of the listed documents or are unsure about the process please contact the PES Team.</p>
<p>Many Thanks for your cooperation,</p>
<p>&&account_name&& &nbsp; PES Team</p>
<p>Rachele Smith - Global PES Team Leader<br/>Julie Cullen - Global PES compliance Officer<br/>Jean Dover - Global PES Team Officer<br/>Carra Booth - Global PES SME</p>
<p><small>Phone: 44-131 656 0870 | Tie-Line: 37 580870<br/>E-mail: <a href='mailto:&&pestaskid&&'>&&pestaskid&&</a></small></p>
<p>IBM UK PES Team<br/>Atria One, 5th Floor<br/>144 Morrison Street, Edinburgh, EH3 8EX</br>United Kingdom</br>**Please input IBM UK Ltd, as we share a building**</p>
endOfEmailBody;