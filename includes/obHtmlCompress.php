<?php
function ob_html_compress($buf){
    return str_replace(array("\n","\r"),'',$buf);
}