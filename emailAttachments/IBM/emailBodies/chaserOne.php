<?php
$pesEmail = 'Hello &&fullname&&,';
$pesEmail.= '<p>A short while ago we contacted you regarding your &&accountname&& PES process. We required further information or documents, however, we do not appear to have received a response.</p>';
$pesEmail.= '<p>Please can you reply at your earliest convenience or contact us with any questions you may have.</p>';

$pesEmail.= '<p>Many thanks for your cooperation</p>';
$pesEmail.= '<h3>&&accountname&&  PES Team</h3>';

$pesEmailPattern = array('/&&fullname&&/','/&&accountname&&/');