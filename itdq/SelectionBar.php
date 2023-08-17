<?php
namespace itdq;

/*
 * @author gb001399
 * @package itdqLib
 *
 */
class SelectionBar
{

    private $predicate;

    private $groupby;

    private $select;

    private $headings;

    private $pivot;

    private $HTMLget;

    private $selectBoxValue;

    private $autoRefresh;

    /**
     * Set $autoRefresh to FALSE and the form wont auto-submit when a drop box is changed, or data entered into an input field.
     *
     *
     * @param boolean $autoRefresh            
     */
    function __construct($autoRefresh = true)
    {
        $this->autoRefresh = $autoRefresh ? TRUE : FALSE;
    }

    function selectBox($label = 'Select', $first = 'Select', $column = 'column', $array = null, $state = '', $width = '100', $type = 'char', $operator = '=', $onChange = null)
    {
        $onChange = $this->autoRefresh ? " onchange='submit()' " : $onChange;
        $var = 'sbs' . strtr($label, ' ', '_');
        
        if (isset($_REQUEST[$var])) {
            $$var = $_REQUEST[$var];
            $_SESSION[$var] = $_REQUEST[$var];
        } else {
            if (isset($_SESSION[$var])) {
                // $_REQUEST[$var]= $_SESSION[$var];
                $$var = $_SESSION[$var];
                $_REQUEST[$var] = $_SESSION[$var];
            } else {
                $$var = null;
                unset($_REQUEST[$var]);
            }
        }
        echo "<div class='form-control'>";
        if ($label != null) {
            echo "<label class='control-label col-sm-2 '>$label</label>";
        }
        echo "<div class='col-sm-8 '  id='sbsDiv$label'>";
        echo "<SELECT id='sbs$label' name='sbs$label' $onChange $state";
        if ($$var != null) {
            echo "style='background-color:yellow'";
        }
        echo ">";
        if (! is_null($first)) {
            echo "<OPTION VALUE=''>$first</OPTION>";
        }
        foreach ($array as $array_key => $array_value) {
            if ($array_key == null) {
                $array_key = 'null';
            }
            if ($array_value == null) {
                $array_value = $array_key;
            }
            echo "<OPTION VALUE='" . htmlspecialchars($array_value, ENT_QUOTES) . "'";
            $var = 'sbs' . strtr($label, ' ', '_');
            $value = strtoupper($$var);
            if (htmlspecialchars_decode($array_value, ENT_QUOTES) == $$var or $array_value == $$var) {
                switch (trim($type)) {
                    case 'char':
                        echo " selected ";
                        $preparedValue = htmlspecialchars(str_replace('AMP;', '', htmlspecialchars_decode(trim($value), ENT_QUOTES)));
                        Trace::traceComment("Value: $value Prepared: $preparedValue ", __METHOD__, __LINE__);
                        if ($preparedValue != 'NULL') {
                            $this->predicate .= "AND UPPER($column)$operator";
                            $this->predicate .= "'" . $preparedValue . "' ";
                        } else {
                            $this->predicate .= "AND $column is null ";
                        }
                        break;
                    case 'int':
                        echo " selected ";
                        $this->predicate .= "AND $column" . $operator;
                        $this->predicate .= $$var . " ";
                        break;
                    case 'date':
                        echo " selected ";
                        if ($$var == 'null') {
                            $this->predicate .= "AND $column is null ";
                        } else {
                            $this->predicate .= "AND $column" . $operator . "DATE('";
                            $this->predicate .= $$var . "') ";
                        }
                        break;
                    case 'timestamp':
                        echo " selected ";
                        $this->predicate .= "AND $column" . $operator . "TIMESTAMP('";
                        $this->predicate .= $$var . "') ";
                        break;
                    case 'null':
                        echo " selected ";
                        $this->predicate .= "AND $column is null ";
                        break;
                    case 'notNull':
                        echo " selected ";
                        $this->predicate .= "AND $column is not null ";
                        break;
                    case 'isIn':
                        echo " selected ";
                        $this->predicate .= "AND $column in (";
                        foreach ($array as $value) {
                            $this->predicate .= "'$value',";
                        }
                        $this->predicate .= ") ";
                        $this->predicate = str_replace(",)", ")", $this->predicate);
                        break;
                    case 'begins':
                        echo " selected ";
                        $this->predicate .= "AND UPPER($column) like '" . trim($value) . "%' ";
                        break;
                    case 'end':
                        echo " selected ";
                        $this->predicate .= "AND UPPER($column) like '%" . trim($value) . "' ";
                        break;
                    case 'includes':
                        echo " selected ";
                        $this->predicate .= "AND UPPERR($column) like '%" . trim($value) . "%' ";
                        break;
                    default:
                        echo " selected ";
                        $this->predicate .= "AND UPPER($column)$operator";
                        $this->predicate .= "'" . str_replace('AMP;', '', $value) . "'";
                        break;
                }
                
                $this->pivot .= ", TRIM(" . $value . ") as $value";
                $this->groupby .= ", $value";
                $this->selectBoxValue = $value;
            }
            echo ">" . $array_key . "</OPTION>";
        }
        echo "</SELECT>";
        echo "</div>";
        echo "</div>";
    }

    function inputField($label = 'Select', $column, $state = '', $width = '100', $caseSensitive = true)
    {
        $onChange = $this->autoRefresh ? " onchange='submit()' " : null;
        $var = 'sbif' . strtr($label, ' ', '_');
        if (isset($_REQUEST[$var])) {
            $$var = $_REQUEST[$var];
            $_SESSION[$var] = $_REQUEST[$var];
        } else {
            if (isset($_SESSION[$var])) {
                $$var = $_SESSION[$var];
            } else {
                $$var = null;
            }
        }
        $color = null;
        // style='background-color:yellow'
        $var = 'sbif' . strtr($label, ' ', '_');
        $value = $$var;
        
        $color = empty($value) ? null : "style='background-color:yellow'";
        if ($label != null) {
            echo "<TH style='text-align:right'>$label:</TH>";
        }
        $var = 'sbif' . strtr($label, ' ', '_');
        $value = $$var;
        echo "<TD><INPUT type='text' id='$var' name='$var' value='$value' $state style='width:$width' $onChange $color /></TD>";
        if ($value != null) {
            $this->predicate .= " AND ";
            $this->predicate .= $caseSensitive ? $column : " upper($column) ";
            $this->predicate .= " like ";
            $this->predicate .= $caseSensitive ? "'$value'" : "'" . strtoupper($value) . "' ";
        }
    }

    function checkBox($label = 'X', $column = 'column', $value = 'Y', $state = null, $pwd = null, $name = null, $forceChecked = false)
    {
        if (substr($label, 0, 1) == '*') {
            $label = substr($label, 1, strlen($label) - 1);
            $as = ' AS ' . $label;
        } else {
            $as = null;
        }
        
        $var = 'sbc' . strtr($label, ' ', '_');
        if (isset($_REQUEST[$var])) {
            $$var = $_REQUEST[$var];
        } else {
            $$var = null;
        }
        
        if (empty($name)) {
            $nameField = " NAME='" . $var . "' ";
        } else {
            $nameField = " NAME='$name' ";
        }
        
        echo "<TH id='chkbox" . $column . "' style='text-align:left'><INPUT TYPE='checkbox' $nameField VALUE='$value' $state";
        
        if (strtoupper($$var) == 'Y' or $forceChecked) {
            echo "CHECKED />$label</TH>";
            $this->headings .= ", $column ";
            if ($pwd != null) {
                $this->select .= " , DECRYPT_CHAR($column,'$pwd') AS $label ";
            } else {
                $this->select .= " , $column ";
            }
            $this->groupby .= " , $column ";
            $this->HTMLget .= "&amp;$var=Y";
        } else {
            echo " />$label</TH>";
        }
    }

    function radio($label = 'Select', $group = 'group', $default = 'default', $state = '')
    {
        $onClick = $this->autoRefresh ? " onclick=submit() " : null;
        
        $var = 'sbr' . $group;
        if (isset($_REQUEST[$var])) {
            $$var = $_REQUEST[$var];
            $_SESSION[$var] = $_REQUEST[$var];
        } else {
            if (isset($_SESSION[$var])) {
                $$var = $_SESSION[$var];
            } else {
                $$var = $default;
                $_SESSION[$var] = $default;
            }
        }
        
        echo "<TH><INPUT TYPE='radio' NAME='" . $var . "' VALUE='$label' $state $onClick ";
        // echo "<BR>$var :" . $$var . ": $label :";
        if ($$var == $label) {
            echo "CHECKED />$label</TH>";
        } else {
            echo " />$label</TD>";
        }
    }

    /**
     *
     * @param char $label            
     */
    function submitButton($label = 'Refresh', $class = 'button-blue')
    {
        echo "<TD>";
        echo ! empty($class) ? "<span class='$class'>" : null;
        echo "<input type='submit' name='sbb$label' value='$label' />";
        echo ! empty($class) ? "</span>" : null;
        echo "</TD>";
    }

    function button($label = 'button', $type = 'submit', $class = 'button-blue', $onclick = null)
    {
        echo "<TD>";
        echo ! empty($class) ? "<span class='$class'>" : null;
        echo "<input type='$type' name='sbb$label' id='sbb$label' value='$label' $onclick />";
        echo ! empty($class) ? "</span>" : null;
        echo "</TD>";
    }

    function readonlyBox($title = 'Title', $label = 'Name', $size = 8, $maxLength = 8, $value = 0)
    {
        echo "<TH>$title</TH><TD><input type='text' id='$label' readonly name='$label' size='$size' maxlength='$maxLength' value=$value /></TD>";
    }

    function getPredicate()
    {
        Trace::traceVariable($this->predicate, __METHOD__, __LINE__);
        return $this->predicate;
    }

    function getSelect()
    {
        Trace::traceVariable($this->select, __METHOD__, __LINE__);
        return $this->select;
    }

    function getSelectBoxValue()
    {
        Trace::traceVariable($this->selectBoxValue, __METHOD__, __LINE__);
        return $this->selectBoxValue;
    }

    function getGroupBy()
    {
        Trace::traceVariable($this->groupby, __METHOD__, __LINE__);
        return $this->groupby;
    }

    function getHeadings()
    {
        Trace::traceVariable($this->headings, __METHOD__, __LINE__);
        return $this->headings;
    }

    function getPivot()
    {
        Trace::traceVariable($this->pivot, __METHOD__, __LINE__);
        return $this->pivot;
    }

    function howManySCols()
    {
        return substr_count($this->select, ",");
    }

    function howManyPCols()
    {
        return substr_count($this->pivot, ",");
    }

    function getHTMLget()
    {
        return $this->HTMLget;
    }

    function setAutoRefresh($setting = true)
    {
        $this->autoRefresh = $setting;
    }

    function iterateVisible($length = 'short')
    {
        echo "<B>" . __METHOD__ . "</B>\n";
        if ($length != 'short') {
            echo "<BR>";
        }
        foreach ($this as $key => $value) {
            if (is_array($value)) {
                echo "<B>$key =></B>";
                print_r($value);
            } else {
                print "<B>$key =></B> $value\n";
            }
            if ($length != 'short') {
                echo "<BR>";
            }
        }
    }
}