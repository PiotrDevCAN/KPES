<?php

use itdq\OKTAGroups;

// bool result = employee_in_gorup ( mixed group, string employee [, int depth] )
// returns TRUE or FALSE if $employee is one of the groups in $group.
// $group can be an array of groups or a string. $employee can be a DN or
// an email address.
function employee_in_group($group, $employee, $depth = 2)
{
    return false;
    
    return OKTAGroups::inAGroup($group, $employee, $depth);       
}
?>