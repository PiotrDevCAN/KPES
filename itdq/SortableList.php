<?php
namespace itdq;

use itdq\OKTAGroups;

/**
 * This class knows how to call a SELECT against a Table or View and display all the columns that are returned to it.
 *
 * Has some features that make it a very simple and powerful way to produce reports.
 * Part of the power comes from the fact it will handle DB2 VIEWS, so sometimes the best way to produce a new report is to build a VIEW in DB2 and
 * use this class to display that view - you can put a lot of the logic into the SQL.
 *
 * @author GB001399
 * @package esoft
 *
 */
class SortableList
{

    protected $rowCountId;

    /**
     *
     * @var string - Needs to hold a unique string for this page - so HTML variables that are page dependant can be named uniquely.
     */
//    protected $id;

    /**
     * This var is used to build any URL's links needed, so it tends to be the name of the page invoking the report.
     *
     * @var string
     */
//     protected $page;

    /**
     * Holds arc or desc for use in the ORDER BY element of the SELECT statement.
     *
     * @var string
     */
//    protected $ord;

    /**
     * HOlds the COlumn Number that will be sorted on in the ORDER BY element of the SELECT statement
     *
     *
     * @var integer
     */
//    protected $col;

    protected $sql;

    protected $pwd;

    protected $csv;

    protected $excel;

    /**
     * Holds the selection bar that will contain the checkboxes that will determine which columns are used in the SELECT
     *
     * @var selectionBar
     */
    protected $colSelect;

    /**
     * Holds an array of fields to be built into the $colSelect SelectionBar
     *
     * @var array
     */
    protected $fields;

    /**
     * Holds the SelectionBar object for the report.
     *
     * Typically you would populated this array in the __construct of any descended classes.
     *
     *
     * @var SelectionBar
     */
    protected $predicateSelect;

    /**
     *
     * Holds the options related to saving the Selection Bar associated with the List.
     *
     * @var SelectionBar;
     */
    protected $profileSelectionBar;

    /**
     * Set to TRUE in the constructor if you support the saving of the Drop Down values.
     * Default is FALSE.
     *
     * @var boolean
     */
    protected $profileSaveable;

    /**
     * used to hold a predicate string built from the Profile.
     *
     * @var string;
     */
    protected $profilePredicate;
    // used to hold a predicate string built from the Profile.
    /**
     * Defines any Fields you DO NOT want saved by the Profile Save feature.
     *
     * @var array
     */
    protected $dontSaveTheseProfileFields;

    /**
     * Holds an array, containing details of the Select Boxes to be created in the $predicateSelect Selection Bar *
     *
     * Array requires the KEYS 'label','first','column','array', 'type', 'operator'
     *
     *
     * @var array
     */
    protected $dropSelect;

    /**
     * Isn't used - was designed to enable "pivot table" like functionality, but I've never really used it.
     *
     * @deprecated
     *
     * @var SelectionBar
     */
    protected $pivotSelect;

    /**
     * Holds an array, containing details for ReadOnly <INPUT> boxes, that will be displayed in the SELECTION BAR and can then be populated by the methods that display the data.
     * So a typical use, would be to have a read only box on the Selection Bar that is updated with the total number of records displayed.
     *
     * Arrays requires the KEYS 'title','label','size', 'maxLength','value'
     *
     * @var array
     */
    protected $readonlyBoxes;

    /**
     * Used to hold any values you want to have written to and $readonlyBoxes once the screen has been completed.
     *
     * So - at the point in your code where you know the value, simply store it in this array, with a key that matches
     * the field name - then at the end of displaying the data, this Class calls it updateMyFormFields and writes the values stored in
     * this array to the appropriate fields on the screen.
     *
     * <B>NOTE</B>For this to work - it expects the readonly boxes to exist in a form called myForm
     *
     * @var array
     */
    protected $myFormFields;

    /**
     * Neat way of controling exactly which columns are to be displayed.
     *
     * @var array - Create an Array here where KEY is the column name, value is anything. Any Columns that exist in this array will not be displayed in the report.
     *
     */
    protected $hiddenColumns;
    /**
     * Neat way of controling labelling columns.
     *
     * @var array - Create an Array here where KEY is the column name, value is title that will be displayed in the listing for this column.
     *
     */
    protected $headingTitles;

    /**
     * Used to populate $predicateSelect Selection Bar.
     * ->inputField
     *
     * Array requires the KEYS 'label' and 'column'
     *
     * @var array
     */
    protected $inputFields;

    /**
     * Used to display Action Buttons above the report.
     *
     * Array key is a label, the data is an array('label'=>...., 'onclick'=>.....)
     *
     * @var array
     */
    protected $actionButtons;

    /**
     * Used to set how many drop down Selection boxes should appear on each row of the Selection Bar
     *
     * @var integer
     */
    protected $selPerRow;

    /**
     * Lets you provide a CLASS etc to the <TABLE> tag that wraps around the report.
     *
     * @var string
     */
    protected $tableTag;

    /**
     * Lets you provide a Title row above the table - lifted from the ADE Application.
     * (Needs to be complete TR statement)
     *
     * @var string
     */
    protected $tableTitle;

    protected $tableRowLight;

    protected $tableRowDark;

    /**
     * Lets you provide the CLASS for the Drop Selection Table
     *
     * @var string
     */
    protected $dropSelectTableClass;

    /**
     * Lets you provide a CLASS etc to the <TH> tags in the report.
     *
     * @var string
     */
    protected $tableTHclass;

    /**
     * Lets you provide the TAG ie <TH>.
     *
     * @var string
     */
    protected $tableTHtag;

    protected $tableTHtagEnd;

    /**
     * Lets you provide a CLASS etc to the <TD> tags in the report
     *
     * @var string
     */
    protected $tableTDclass;

    /**
     * Lets you provide the TAG ie <TD.
     *
     * @var string
     */
    protected $tableTDtag;

    protected $tableTDtagEnd;

    /**
     * You don't always want the word 'edit' at the top of the "editlink" column, so this lets you set the heading for the "editlink" column
     *
     * @var $editLabel unknown_type
     */
    protected $editLabel;

    protected $deleteLabel;

    /**
     *
     * @var string
     */
    protected $emailBody;

    /**
     * Holds the name of the table being listed.
     *
     * @var string
     */
    protected $table;

    protected $tableName;

    /**
     * Holds the DBTable object for the table being listed.
     *
     * @var unknown_type
     */
    protected $DbTable;

    /**
     * True - then we'll put out a Total Line at the end of the report
     *
     * @var unknown_type
     */
    protected $displayTotals;

    /**
     * Holds the totals value for the final row - you need to populate these cells in processField()
     *
     * @var unknown_type
     */
    protected $Totals;

    protected $total;
    // Not sure why we need this but we do.
    /**
     * Holds an array of column names, when the value in one of these columns changes - we display the Sub Total Row
     */
    protected $triggerSubTotal;
    // When these columns change - print a subtotal
    protected $dontSubTotal;
    // When you are subtotaling - ignore these columns, even if they are numeric
    public $withTFoot;

    /*
     * Holds the Column Headings, used in the Subtotal process. *
     */
    protected $Headings;

    protected $ColumnsInTable;

    protected $updateFormFieldsScript;

    public static $parmPivot = TRUE;

    public static $parmNoPivot = FALSE;

    public static $parmNoEditLink = FALSE;

    public static $parmEditLink = TRUE;

    public static $parmNoDeleteLink = FALSE;

    public static $parmDeleteLink = TRUE;

    public static $parmFull = TRUE;

    public static $parmNotFull = FALSE;

    public static $parmSubTotal = TRUE;

    public static $parmNoSubTotal = FALSE;

    /**
     * Sets up some of the properties used to control the functionality.
     * So classes that inherit from this class, may need to set some of these properties for themselves as opposed to taking the defaults from here.
     *
     * Note the use of TWO SelectionBar objects, one will be used to tailor the SELECT predicate, whilst the other is used to add additional columns to the select
     *
     *
     * @param string $screen
     *            stored in $id and $page
     * @param string $csv
     *            not null, then should be the resource returned from fopen on the CSV the report should be written to.
     * @param string $excel
     *            not null, then should be the resoruce returned from the ExcelWriter call.
     * @param string $pwd
     *            the table is encrpyted, this should be it's password.
     */
    function __construct($tableName=null, $pwd = null)
    {
        Trace::traceComment(null, __METHOD__);
        $this->csv = null;
        $this->excel = null;
        $this->pwd = $pwd;
        $this->colSelect = new SelectionBar();
        $this->predicateSelect = new SelectionBar();
        $this->selPerRow = 4;
        $this->tableName = $tableName;
        if(!empty($tableName)){
            $this->DbTable = new DbTable ( $tableName, $pwd );
            $this->fields = $this->DbTable->getColumns ();
        } else {
            $this->DbTable = null;
            $this->fields = null;
        }
        $this->tableTag = "<TABLE class='table responsive' >";
        $this->tableTitle = false;
        $this->tableTHclass = "class='bar-blue-med-dark' ";
        $this->tableTHtag = "<th ";
        $this->tableTHtagEnd = " </th>";
        $this->tableTDclass = "  ";
        $this->tableTDtag = "<td> ";
        $this->tableTDtagEnd = " </td>";

        $this->dropSelectTableClass = 'bar-blue-med-light';
        $this->withTFoot = false;

        $this->editLabel = empty($this->editLabel) ? 'Edit' : $this->editLabel;
        $this->deleteLabel = empty($this->deleteLabel) ? 'Delete' : $this->deleteLabel;
        $this->displayTotals = false;
        $this->setTotal();
        $this->rowCountId = 'document.myForm';
        $this->ColumnsInTable = 0;

        $this->profileSaveable = isset($this->profileSaveable) ? $this->profileSaveable : FALSE; // Default to FALSE
        $this->profileSelectionBar = new SelectionBar();
        if (! isset($this->dontSaveTheseProfileFields)) {
            // Do it like this in case any of the Classes that extend us have already setup some dont save fields.
            $this->dontSaveTheseProfileFields = array();
        }
        $this->dontSaveTheseProfileFields['sbifProfile_Name'] = 1;
        $this->profilePredicate = FALSE;
        // $this->profileSaveable = TRUE;
        $this->saveProfile(); // Call it anyway - it works out if it needs to do anything.
        $this->deleteProfile(); // Call it anyway - it works out if it needs to do anything.
        $this->updateFormFieldsScript = null;
    }

    /**
     * Sets $this->col and $this->ord which will be used to populate the SORT element of the SELECT
     *
     * The values are picked up from $_SESSION
     */
//     function getVals()
//     {
//         $global_col = 'col' . $this->id;
//         $global_ord = 'ord' . $this->id;

//         if (isset($_GET['col'])) {
//             $this->col = $_GET['col'];
//             $_SESSION[$global_col] = $this->col;
//         } else {
//             if (isset($_SESSION[$global_col])) {
//                 $this->col = $_SESSION[$global_col];
//             } else {
//                 $this->col = 1;
//             }
//         }
//         if (isset($_GET['ord'])) {
//             $this->ord = $_GET['ord'];
//             $_SESSION[$global_ord] = $this->ord;
//         } else {
//             if (isset($_SESSION[$global_ord])) {
//                 $this->ord = $_SESSION[$global_ord];
//             } else {
//                 $this->ord = 'asc';
//             }
//         }
//     }

    /**
     * Runs $this->sql and returns the result set from sqlsrv_query
     *
     * Very basic. Most classes descended from this will need to develop a more comprehensive method. *
     *
     *
     * @return resource resource returned by the sqlsrv_query
     */
    function fetchList()
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        $this->sql = str_replace("='NULL'", " IS NULL ", $this->sql);
        $this->sql .= " FOR READ ONLY ";
        $this->sql2 = htmlspecialchars_decode($this->sql, ENT_QUOTES);

        Trace::traceVariable($this->sql2, __METHOD__, __LINE__);

        $rs = sqlsrv_query($GLOBALS['conn'], $this->sql2, array(
            'cursor' => DB2_SCROLLABLE
        ));

        if (! $rs) {
            echo "<BR/> Error:" . print_r(sqlsrv_errors());
            echo "<BR/> Msg:" . print_r(sqlsrv_errors());
            exit("<BR/>Error in " . __METHOD__ . " : $rs : $this->sql");
        }
        return $rs;
    }

    /**
     * Displays the results of fetchList
     *
     * Checks if you're supposed to have selected from the colSelect selection bar, that you have in fact done so.
     * Then called displayHeading followed by displayData parsing to them the parms passed into itself.
     *
     * If $editLink = TRUE then you MUST supply a method called editLin
     *
     * @param resource $rs
     *            DB2 RESULT SET to be displayed.
     * @param boolean $pivot
     *            really used.
     * @param boolean $full
     *            TRUE then doesn't expect there to be a SELECTION BAR COLSELECT. Rather it expects to be running a SELECT *
     * @param boolean $editLink
     *            the EDIT column be prefixed to the report if so we call the editLink method at the start of each row.
     * @param boolean $deleteLink
     *            the DELETE column be appended to the report
     * @param string $width
     *            %age of the Table on the Screen.
     */
    function displayTable($rs, $subTotal = false, $full = false, $editLink = false, $deleteLink = false, $width = '95%', $totalOnly = false)
    {
        ob_start();
        Trace::traceComment(null, __METHOD__);


        echo "<div class='panel panel-primary'>";
        $panelTitle = empty($this->tableTitle) ? 'Report' : $this->tableTitle;
        echo "<div class='panel-heading'>$panelTitle</div>";

        $this->displayTableHeading($rs);

        $this->editLink = $editLink;
        $this->deleteLink = $deleteLink;

        if (isset($this->colSelect)) {
            $rows = $this->colSelect->getSelect();
        }

        if (! $full && $rows == null) {
            echo "<H2>Please select one or more columns $rows</H2>";
        } else {

            echo "<div class='table-responsive'>";

            echo str_replace('wwwww', $width, $this->tableTag);
            // if(sqlsrv_fetch_array($rs)){
            echo "\n<thead>";
           // echo $this->tableTitle ? $this->tableTitle : null;
            $this->displayHeading($subTotal, $full, $editLink, $deleteLink, $rs);
            echo "</thead>\n";

            if ($this->withTFoot) {
                echo "\n<tfoot>";
                $this->displayTableFooter($editLink, $deleteLink);
                echo "\n</tfoot>";
            }
            echo "\n<tbody>";
            $this->displayData($rs, $subTotal, $editLink, $deleteLink, $totalOnly);
            echo "</tbody>\n";
            // }
            echo "</TABLE>\n";
            echo ! empty($this->updateFormFieldsScript) ? $this->updateFormFieldsScript : null;

            echo "</div>";

        }

        echo "</div>";
        ob_end_flush();
    }


    function displayTableHeading($rs){
        return;
    }

    /**
     * Displays the ROWS returned from fetchList.
     * (Can Subtotal and Total columns too and display them at the end of the report)
     *
     * Shouldn't really need to change this method as it does invoke other methods at key points, making customisation a lot simpler.
     *
     * Before displaying a row - it will call displayRow() to check the row should be displayed, overwrite this method if you need to prevent some rows from being displayed.
     * The contents of the "edit" column are determined by the method insertEdit(), this class has a dummy insertEdit() method that does NOTHING.
     *
     * Even if the row is to be displayed, any column defined in the array $this->hiddenColumns will NOT be displayed.
     *
     * Each Cell is actually displayed by processField() method. This class has a very simply processField() that just displays the value in a <TD></TD> cell. By overrididing this method you can get control of the format of the output etc etc.
     * The contents of the "delete" column are determined by the method insertDelete(), this class has a dummy insertDelete() method that does NOTHING.
     *
     * A count of the rows actually displayed is maintained and written to $this->myFormFields['lines'] to be written into the readonly <INPUT> field by updateMyForms() method once this method has completed processing.
     *
     * It will also invoke buildDetailDiv() in order to let you build <DIV></DIV> sections that can be toggled by the user. Again this class provides a dummy buildDetailDiv() that does NOTHING.
     *
     * If $excel is not null, then writeLine() is invoked to write the ROW to the excel file.
     * If $csv is not null, then fputCsv() is involved to write the ROW to the csv file.
     *
     * Finally, it will invoke updateMyForms() to populate any readonly <INPUT> fields in the Selection Bar.
     *
     *
     * SUBTOTALLING.
     *
     * Assumes that the user can select one or more 'additional columns'. Will display a subtotal when all but the last of these 'additional columns' changes.
     *
     *
     *
     *
     *
     * @param resource $rs
     *            Resource from Fetchlist.
     * @param boolean $subTotal
     *            SubTotal lines
     * @param boolean $editLink
     *            the EDIT column be prefixed to the report
     * @param boolean $deleteLink
     *            the DELETE column be appended to the report
     */
    function displayData($rs, $subTotal = false, $editLink = false, $deleteLink = false, $totalOnly = false)
    {
        Trace::traceTimings(null, __METHOD__, __LINE__);
        $rows = 0;
        $total = 0;
        $subTotalArray = array();
        $cols = trim(trim($this->colSelect->getSelect()), ',');
        $SqlColumns = preg_split("/,/", $cols);
        $previousRow = array();
        // if (count ( $SqlColumns ) > 1 or $subTotal) {
        // for($c = 0; $c < count ( $SqlColumns ) - 1; $c ++) {
        // $previousRow [trim ( $SqlColumns [$c] )] = "initial value";
        // $trackPreviousRow = TRUE;
        // }
        // } else {
        // $trackPreviousRow = FALSE;
        // }

        $columns = db2_num_fields($rs);
        $columnType = array();
        for ($c = 0; $c < $columns; $c ++) {
            $columnType[db2_field_name($rs, $c)] = db2_field_type($rs, $c);
        }

        // $this->initialiseSubTotalArray($subTotalArray, $cols, $rs);

        $rowNumber = 1; // Get the first row first, as we fetched it already processing the header.
        while ($row = sqlsrv_fetch_array($rs, $rowNumber)) {
            // if($rowNumber==1 and $trackPreviousRow){
            // //initialise previous row here.
            // $this->updatePreviousRow($previousRow,$row);
            // }
            if (empty($previousRow)) {
                /*
                 * If we've not initialise $previousRow - do it now.
                 */
                $this->updatePreviousRow($previousRow, $row);
            }
            $rowNumber = null; // In future, just get the next row.
            if ($this->displayRow($row)) {

                if ($subTotal) {
                    $needToDisplaySubtotal = $this->checkForColumnChange($previousRow, $row);
                    if (! empty($subTotalArray) and $needToDisplaySubtotal) {
                        /*
                         * !empty($subTotalArray) - means we dont subtotal on the very first line.
                         */
                        // echo "<H2>Need to display subtotal : $needToDisplaySubtotal Was :" . $previousRow[$needToDisplaySubtotal] . " Is:" . $row[$needToDisplaySubtotal] ."</H2>";
                        $this->displaySubTotals($previousRow, $row, $subTotalArray, $needToDisplaySubtotal);
                    }
                    $this->incrementSubTotals($subTotalArray, $row);
                    // echo "<BR/><B>" . __METHOD__ . __LINE__ . " incremented SubTotalArray</B>" ;
                    // print_r($subTotalArray);
                    // if($trackPreviousRow){
                    // $this->updatePreviousRow($previousRow,$row);
                    // }
                }

                $this->startNewRowInReport($row);
                // Print out each field in the new record.
                if ($editLink) {
                    $this->insertEdit($row);
                }
                foreach ($row as $key => $value) {
                    if (! isset($this->hiddenColumns[str_replace("_", " ", trim($key))])) {
                        $this->processField($key, $value, $row, $columnType[$key]);
                    }
                    $row[$key] = trim($value);

                    if ($columnType[$key] == 'real' or $columnType[$key] == 'int') {
                        $this->calcTotal($key, $value, $row, $columnType[$key]);
                    } elseif (! isset($this->subTotal[$key])) {
                        $this->subTotal[$key] = ' ';
                    }
                }
                if ($deleteLink) {
                    $this->insertDelete($row);
                }
                $rows = $rows + 1;
                $this->buildDetailDiv($row); // Give any offspring the chance to build a togglable detail Div section here
                $this->myFormFields['lines'] = $rows;
                if (isset($this->excel)) {
                    $this->excel->writeLine($row);
                }
                if (isset($this->csv)) {
                    $this->writeRowToCsv($row, $this->csv);
                    // fputcsv ( $this->csv, $row, ",", '"' );
                }
                $this->buildEmail($row);
                echo "</tr>";
            }
            $this->updatePreviousRow($previousRow, $row); // Save this row as the $previousRow
        }
        $columnsinTable = 0;

        $columnsinTable = $this->ColumnsInTable;
        $editLink ? $columnsinTable ++ : $columnsinTable;
        $deleteLink ? $columnsinTable ++ : $columnsinTable;

        // echo "<br>cols in tab $columnsinTable";

        $this->updateFormFieldsScript = $this->updateMyFormFields($this->myFormFields, $columnsinTable);

        if ($subTotal) {
            $this->displaySubTotals($previousRow, $row, $subTotalArray);
        }

        if ($this->displayTotals) {
            // Read 1st row again to get the column names for the Totals Row.
            $row = sqlsrv_fetch_array($rs, 1);
            echo "<HR/>";
            echo "<TR>";
            if ($editLink) {
                echo "<TD></TD>";
            }
            if (! empty($row)) {
                foreach ($row as $key => $value) {
                    if (! isset($this->hiddenColumns[str_replace("_", " ", trim($key))])) {
                        $cellName = 'totalCell' . $key;
                        if (! empty($this->Totals[$cellName])) {
                            echo "<TH $this->tableTHclass >" . number_format($this->Totals[$cellName], 2) . "</TH>";
                        } else {
                            echo "<TH></TH>";
                        }
                    }
                }
            }
            if ($deleteLink) {
                echo "<TD></TD>";
            }
            echo "</TR>";
            if (isset($this->excel)) {
                $this->excel->writeLine($this->Totals);
            }
            if (isset($this->csv)) {
                fputcsv($this->csv, $this->Totals, ",", '"');
            }
        }

        if ($this->total) {
            $this->displayTotal();
        }
        Trace::traceTimings(null, __METHOD__, __LINE__);
    }


    function startNewRowInReport($row){
        echo "<tr >";
    }

    /**
     * Prints out a SUBTOTAL row, values expected in $sublineA array.
     *
     * @param array $subLineA
     *            this array contains the values to be displayed, would need to study code more to really comment.
     * @param array $SQLcolumns
     *            Not sure what this is doing - have to study the code
     */
    function printSubTotal($subLineA, $SQLcolumns)
    {
        Trace::traceComment(null, __METHOD__);
        for ($sl = count($subLineA['col']); $sl > 0; $sl --) {
            switch ($subLineA['col'][$sl - 1] - 1) {
                case '0':
                    $bgc = "#6699cc";
                    break;
                case '1':
                    $bgc = "#cceeff";
                    break;
                default:
                    $bgc = "#33ff88";
            }
            echo "<TR style='background-color:$bgc'>";
            for ($sc = 0; $sc < $subLineA['col'][$sl - 1] - 1; $sc ++) {
                echo "<TD></TD>";
            }
            echo "<TH>" . $subLineA['prow'][$sl - 1] . " Total</TH>";
            for ($sc = 0; $sc < (count($SQLcolumns) - ($subLineA['col'][$sl - 1] + 1)); $sc ++) {
                echo "<TD></TD>";
            }
            echo "<TH>";
            echo $subLineA['st'][$sl - 1];
            echo "</TH></TR>";
        }
    }

    /**
     * Displays the heading on the table.
     *
     * Calls showHeading() to determine if the column is being displayed at all.
     * Calls setHeadingTitle() - so if you want to overide the default heading ( which is the column name from the SELECT) do it there.
     * Calls setHedingSpan() - SO if you need to have headings that span multiple columns in the table.
     *
     * @param boolean $pivot
     *            TRUE ..
     * @param boolean $full
     *            TRUE then it won't look for columns selected in the colSelect Selection Bar
     * @param boolean $editLink
     *            TRUE then it will preceed each row with an 'edit' column.
     * @param boolean $deleteLink
     *            TRUE then it will append a 'delete' column to each row.
     * @param resource $rs
     *            Resource returned by fetchList()
     */
    function displayHeading($subTotal, $full = false, $editLink = false, $deleteLink = false, $rs = null)
    {
        if (! $full) {
            if (isset($this->colSelect)) {
                $cols = $this->colSelect->getHeadings();
            }
        } else {
            $cols = '';
            $columns = db2_num_fields($rs);
            for ($c = 0; $c < $columns; $c ++) {
                $cols .= " , " . str_replace('_', ' ', db2_field_name($rs, $c));
                if ($this->displayTotals) {
                    // They want to display Totals, so initialise the Totals array.
                    $cellName = 'totalCell' . db2_field_name($rs, $c);
                    $this->Totals[$cellName] = null;
                }
            }
        }
        $SQLcolumns = preg_split("/,/", trim($cols));
        Trace::traceVariable($SQLcolumns, __METHOD__, __LINE__);
        if (empty($this->Headings)) {
            foreach ($SQLcolumns as $key => $column) {
                // echo "<BR>" . __METHOD__ . "Key $key Column $column";
                if ($column != null) {
                    $this->Headings[] = $column;
                }
            }
        }
        Trace::traceVariable($this->Headings, __METHOD__, __LINE__);
        if (isset($this->excel)) {
            $this->excel->writeHeaderLine($this->Headings, 'bgcolor=#cceeff');
        }
        if (isset($this->csv)) {
            fputcsv($this->csv, $this->Headings, ",", '"');
        }
        // $this->buildEmail($this->Headings," class='blue-dark' ", "<TH style='background:#05a; color:#fff; text-align:right' >",'</TH>');
        echo "<TR>";

        if ($editLink) {
            $width = $this->setHeadingWidth('edit');
            echo "$this->tableTHtag $this->tableTHclass $width>" . $this->editLabel . $this->tableTHtagEnd;
        }
        $this->ColumnsInTable = 0;
        for ($i = 0; $i < count($this->Headings); $i ++) {
            if ($this->showHeading(trim($this->Headings[$i]))) {
                $colSpan = $this->setHeadingColspan($this->Headings[$i]);
                $heading = $this->setHeadingTitle($this->Headings[$i]);
                $width = $this->setHeadingWidth($this->Headings[$i]);
                echo "$this->tableTHtag $this->tableTHclass $width colspan='$colSpan' ";
                echo ">";
                echo $heading . $this->tableTHtagEnd;
                $this->ColumnsInTable ++;
            }
        }
        if ($deleteLink) {
            $width = $this->setHeadingWidth('delete');
            echo "$this->tableTHtag $this->tableTHclass $width style='text-align:center' >" . $this->deleteLabel . "$this->tableTHtagEnd";
        }
        echo "</TR>";
    }

    function displayTableFooter($editLink = false, $deleteLink = false)
    {
        echo "<TR>";
        if ($editLink) {
            $width = $this->setHeadingWidth('edit');
            echo "$this->tableTHtag $this->tableTHclass $width>" . $this->tableTHtagEnd;
        }

        for ($i = 0; $i < count($this->Headings); $i ++) {
            if ($this->showHeading(trim($this->Headings[$i]))) {
                $colSpan = $this->setHeadingColspan($this->Headings[$i]);
                $heading = $this->setHeadingTitle($this->Headings[$i]);
                echo "$this->tableTHtag $this->tableTHclass  colspan='$colSpan' ";
                echo ">$heading";
                echo $this->tableTHtagEnd;
            }
        }
        if ($deleteLink) {
            $width = $this->setHeadingWidth('delete');
            echo "$this->tableTHtag $this->tableTHclass $width>" . $this->deleteLabel . "$this->tableTHtagEnd";
        }
        echo "</TR>";
    }

    /**
     *
     * Builds a SELECTION BAR from $colSelect - will determine which columns will be used to build the SELECT
     *
     * Expects $this->fields to be an array of 'column name'=>'label'
     *
     * @param string $width
     *            for the TABLE as xx%
     */
    function columnSelection($width = '95%', $show = true)
    {
        Trace::traceComment(null, __METHOD__);
        if ($show) {
            echo "<TABLE class=bar-blue-med-light style='width:$width' >";
            echo "<TR bgcolor='#99bbee'>";
        }
        $cell = 1;
        foreach ($this->fields as $lab => $col) {
            if ($cell ++ == $this->selPerRow) {
                $cell = 2;
                if ($show) {
                    echo "</TR><TR  bgcolor='#99bbee'>";
                }
            }
            if (is_string($lab)) {
                if (strrpos($lab, ".") > 0) {
                    // If the LABEL has a . in it - then they need that as the column name but don't display it
                    $label = substr($lab, strrpos($lab, ".") + 1);
                } else {
                    $label = $lab;
                }
                if (strtoupper($lab) == $lab) {
                    $this->colSelect->checkbox($label, $col, 'Y', null, $this->pwd);
                } else {
                    $this->colSelect->checkbox($label, $col);
                }
            } else {
                $this->colSelect->checkbox($lab['HEADING'], $lab['COLUMN']);
            }
        }
        if ($show) {
            echo "</TR><TR bgcolor='#99bbee'><TD><span class='button-blue'><input type='submit' value='refresh' onclick=submit() /></span></TD>";
            echo "</TR>";
            echo "</TABLE>";
        }
    }

    /**
     *
     * Builds a SELECTION BAR from $colSelect - will determine which columns will be used to build the SELECT
     *
     * Expects $this->fields to be an array of 'column name'=>'label'
     *
     * @param string $width
     *            for the TABLE as xx%
     */
    function clearColumnSelection()
    {
        Trace::traceComment(null, __METHOD__);
        foreach ($this->fields as $lab => $col) {
            if (is_string($lab)) {
                if (strrpos($lab, ".") > 0) {
                    // If the LABEL has a . in it - then they need that as the column name but don't display it
                    $label = substr($lab, strrpos($lab, ".") + 1);
                } else {
                    $label = $lab;
                }
            } else {
                $label = $lab['HEADING'];
            }
            $var = 'sbc' . strtr($label, ' ', '_');
            unset($_REQUEST[$var]);
        }
    }

    function preventAutoRefresh(){
        $this->predicateSelect = new SelectionBar(false);
    }


    /**
     *
     * Displays a set of "Multi-record" buttons.
     *
     * Expects $this->actionButtons to be an array of the details of the buttons to be displayed
     *
     * @param string $width
     *            for the TABLE as xx%
     */
    function multiRecordButtons($width = '50%')
    {
        Trace::traceComment(null, __METHOD__);
        echo "<TABLE class=bar-blue-med-light style='width:$width'>";
        echo "<TR bgcolor='#99bbee'><TD colspan='8'><span style='font-size: 0.85em;font-weight: normal;'>Select multiple records,using the 'Select' checkbox in the Action column prior to clicking one of these buttons. <BR/>OR<BR/>Use a specific button in the Action column to perform an Action on that 1 record</span></TD></TR> ";
        echo "<TR bgcolor='#99bbee'><TD><span class='button-blue'>";
        $cell = 1;
        foreach ($this->actionButtons as $label => $buttonDefintion) {
            if ($cell ++ == 7) {
                $cell = 2;
                echo "</span></TD></TR><TR  bgcolor='#99bbee'><TD><span class='button-blue'>";
            }
            echo "<input type='submit' name='multipleActionSubmit' value='" . $buttonDefintion['value'] . "' />&nbsp;";
        }
        echo "</span></TD></TR>";
        echo "</TABLE>";
    }

    /**
     * Builds the $this->predicateSelect Selection Bar using :
     * <ul>
     * <li> $this->inputfields - Creating $selectionBar->inputField
     * <li> $this->dropSelect - Creating $selectionBar->selectBox
     * <li> $this->readonlyBoxes - Creating $selectionBar->readonlyBox
     * </ul>
     *
     * @param string $width
     *            The width of the screen in % that the Selection Bar should occupy.
     */
    function dropSelection($width = '95%')
    {
        Trace::traceComment(null, __METHOD__);
        $this->displayProfileOptions();

        echo "<div class='panel panel-primary'>";
        echo "<div class='panel-heading'>Selection Bar</div>";


        if (isset($this->inputFields)) {
            $cell = 1;

            foreach ($this->inputFields as $key => $data) {
                if (! is_array($data)) {
                    switch ($data) {
                        case 'submit':
                            $this->predicateSelect->submitButton();
                            break;
                        default:
                            break;
                    }
                } else {
                    if ($key == 'button') {
                        $this->predicateSelect->button($data['label'], $data['type'], $data['class'], $data['onclick']);
                    } else {
                        $caseSensitive = isset($data['caseSensitive']) ? $data['caseSensitive'] : null;
                        $this->predicateSelect->inputField($data['label'], $data['column'], '', '100', $caseSensitive);
                    }
                }
            }
        }
        if (isset($this->dropSelect)) {
            foreach ($this->dropSelect as $key => $data) {
                if (is_a($data, 'itdq\DropSelectionItem')) {
                    $this->predicateSelect->selectbox($data->label(), $data->first(), $data->column(), $data->data(), $data->state(), 100, $data->type(), $data->operator());
                } elseif (! is_array($data)) {
                    switch ($data) {
                        case 'submit':
                            $this->predicateSelect->submitButton();
                            break;
                        default:
                            echo "<TD colspan ='2'></TD>";
                            ;
                            break;
                    }
                } else {
                    if ($key == 'button') {
                        $this->predicateSelect->button($data['label'], $data['type'], $data['class'], $data['onclick']);
                    } else {
                        if (! isset($data['type'])) {
                            $type = 'char';
                        } else {
                            $type = $data['type'];
                        }
                        if (! isset($data['operator'])) {
                            $operator = '=';
                        } else {
                            $operator = $data['operator'];
                        }
                        $this->predicateSelect->selectbox($data['label'], $data['first'], $data['column'], $data['array'], null, 100, $type, $operator);
                    }
                }
            }
        }
        if (isset($this->readonlyBoxes)) {
            echo "<TABLE class=$this->dropSelectTableClass >";
            echo "<TR><TD colspan='6'>";
            echo "<TABLE><TR>";
            $cell = 1;
            foreach ($this->readonlyBoxes as $key => $data) {
                if (empty($data)) {
                    $cell = 2;
                    echo "</TR><TR>";
                } elseif ($data == 'space') {
                    echo "<TD colspan='2'></TD>";
                } else {
                    if ($cell ++ == 10) {
                        $cell = 2;
                        echo "</TR><TR>";
                    }
                    $this->predicateSelect->readonlyBox($data['title'], $data['label'], $data['size'], $data['maxLength'], $data['value']);
                    $this->myFormFields[$data['label']] = 0;
                }
            }

            echo "</TABLE>";
            echo "</TD></TR></TABLE>";
        }


        echo "</div>";

    }

    /**
     * simple rests sort sequence to ASC and the sort column to 1
     */
//     function resetSort()
//     {
//         Trace::traceComment(null, __METHOD__);
//         $global_col = 'col' . $this->id;
//         $global_ord = 'ord' . $this->id;
//         $this->col = 1;
//         $this->ord = null;
//         $_SESSION[$global_ord] = $this->ord;
//         $_SESSION[$global_col] = $this->col;
//     }

    /**
     *
     * Overwrite this method if you need to have some column heading span multiple columns
     *
     * @param string $key
     * @return number
     */
    function setHeadingColspan($key)
    {
        return 1;
    }

    /**
     * Overwrite this method if you want to disply a heading that is different from the DB2 Column Name returned in the SQL SELECT
     *
     * @param string $heading
     * @return unknown
     */
    function setHeadingTitle($heading)
    {
        return isset($this->headingTitles[trim($heading)]) ? ucwords(strtolower(trim($this->headingTitles[trim($heading)]))) : ucwords(strtolower(trim($heading)));
    }

    /**
     * Overwrite this method if you want to adjust the width of a particular column in the listing
     *
     * @param string $column
     * @return unknown
     */
    function setHeadingWidth($column)
    {
        return null;
    }

    /**
     * Use this to toggle the color of each row, the default toggles between gray and white
     *
     * @param array $row
     *            Row from DB2_FETCH being displayed
     * @param string $bgColor
     *            Current Background colour
     * @return string
     */
     function setBgColor($row, $bgColor)
     {
//         if ($bgColor == $this->tableRowDark) {
//             $bgColor = $this->tableRowLight;
//         } else {
//             $bgColor = $this->tableRowDark;
//         }
//         return $bgColor;
     }

    /**
     * Overwrite this method in order to process each cell, determing exactly what will be displayed on the screen.
     * You are passed the
     *
     *
     * @param string $key
     *            name
     * @param string $value
     *            value for this column
     * @param array $row
     *            full row being displayed - so you can test other fields or even display other columns
     * @param string $type
     *            sure what this is :-)
     */
    function processField($key, $value, $row, $type = null)
    {
        echo $this->tableTDtag . htmlspecialchars(trim($value)) . $this->tableTDtagEnd;
        if ($this->displayTotals && is_numeric($value)) {
            // They want to display totals and this is a numeric field, so total it.
            $cellName = 'totalCell' . $key;
            $this->Totals[$cellName] += $value;
        }
    }

    /**
     * Don't display the results on the screen - just write them straight to a CSV.
     *
     * Use this in some apps for BIG extracts, where it takes too long to build the screen and all the user actually wants
     * is the CSV with the data in.
     *
     * @param resource $rs
     *            Set from sqlsrv_query
     */
    function dumpToCsv($rs, $updateForms = true)
    {
        $cols = '';
        $columns = db2_num_fields($rs);
        for ($c = 0; $c < $columns; $c ++) {
            $cols .= " , " . str_replace('_', ' ', db2_field_name($rs, $c));
        }
        $SQLcolumns = preg_split("/,/", trim($cols));
        foreach ($SQLcolumns as $key => $column) {
            if ($column != null) {
                $headings[] = trim($column);
            }
        }
        if (isset($this->csv)) {
            fputcsv($this->csv, $headings, ",", '"');
        }
        $rows = 0;
        while ($row = sqlsrv_fetch_array($rs)) {
            Trace::traceVariable($row, __METHOD__);
            if ($this->displayRow($row)) {
                foreach ($row as $column => $value) {
                    $trimmedRow[$column] = trim($value);
                }
                $rows = $rows + 1;
                fputcsv($this->csv, $trimmedRow, ",", '"');
                unset($trimmedRow);
                if ($updateForms) {
                    if ($rows % 50 == 0) {
                        set_time_limit(60);
                        echo "<SCRIPT LANGUAGE='JavaScript'>\n";
                        echo "document.myForm.lines.value = $rows;";
                        echo "</SCRIPT>";
                    }
                }
            }
        }
        if ($updateForms) {
            echo "<SCRIPT LANGUAGE='JavaScript'>\n";
            echo "document.myForm.lines.value = $rows;";
            echo "</SCRIPT>";
        }
    }

    /**
     * Override this method to perform any processing you need to display the contents of the "insert link" cell.
     *
     * @param array $row
     *            Array containing the row being processed (frm sqlsrv_fetch_array)
     */
    function insertEdit($row)
    {
        return;
    }

    /**
     * Override this method to perform any processing you need to display the contents of the "delete link" cell.
     *
     *
     * @param array $row
     *            Array containing the row being processed (frm sqlsrv_fetch_array)
     */
    function insertDelete($row)
    {
        return;
    }

    /**
     * Use this to build fancy <DIV></DIV>s that the user can toggle open and closed.
     *
     * You need to create the "toggle" - do that in processField
     * In this method() you can build the table that will open/close when the toggle is clicked.
     *
     * @param array $row
     *            $row from sqlsrv_fetch_array
     */
    function buildDetailDiv($row)
    {
        return;
    }

    /**
     * Determine if you want to display this column.
     *
     * This default method simply tests if the colum name appears in $this->hiddenColumns array
     *
     * @param string $value
     *            Name being displayed
     * @return boolean
     */
    function showHeading($value)
    {
        if (isset($this->hiddenColumns[trim($value)])) {
            return false;
        }
        return true;
    }

    /**
     * Overwrite this method if you need to check a row and determine if it should be displayed or not.
     *
     * Return TRUE and displayData will print out the row, return FALSE and it won't.
     *
     * @param array $row
     *            Array containing the data from sqlsrv_fetch_array
     * @return boolean
     */
    function displayRow(&$row)
    {
        return true;
    }

    /**
     *
     * @param $id -
     *            HTML Form ID of the field to be updated with the Row Counter.
     */
    function setRowCountId($id)
    {
        $this->rowCountId = $id;
    }

    /**
     * Will invoke javascript to update any readonly boxes in the Selection Bar based on the contents of the array passed into it.
     *
     * @param array $myFormFields
     */
    function updateMyFormFields($myFormFields, $columns = 1)
    {
        $script = "";
        if (isset($myFormFields)) {
            // echo "\n<TR><TD>";
            $script .= "<SCRIPT  type='text/javascript'>";
            foreach ($myFormFields as $key => $data) {
                $script .= "var field=document.getElementById($this->rowCountId); ";
                $script .= " if (field) { ";
                $script .= "      var curVal = parseInt($this->rowCountId.$key.value);\n";
                $script .= "      $this->rowCountId.$key.value = curVal +  $data;\n";
                $script .= " }; ";
            }
            $script .= "</SCRIPT>";
            // echo "</TD>";

            // for($i=1;$i<$columns;$i++){
            // echo "<TD></TD>";
            // }

            // echo "</TR>\n";
            return $script;
        }
    }

    /**
     * Use this method if you need to point to a different CSV from the one set in the __construct.
     *
     * Not sure why you'd need to do that but ho hum.
     *
     * @param resource $csv
     *            from fopen on the CSV
     */
    function setCsv($csv)
    {
        $this->csv = $csv;
    }

    /**
     * sets the value of $this->total - not sure why but ho hum.
     *
     * @param boolean $total
     */
    function setTotal($total = false)
    {
        $this->total = $total;
    }

    /**
     *
     * @return string
     */
    function getEmailBody()
    {
        return $this->emailBody;
    }

    /**
     *
     * @param unknown_type $rowArray
     * @param unknown_type $trClass
     * @param unknown_type $colStart
     * @param unknown_type $colEnd
     */
    function buildEmail($rowArray, $trClass = null, $colStart = '<TD>', $colEnd = '</TD>')
    {
        // $this->emailBody .= "<TR $trClass>";
        // foreach($rowArray as $value){
        // $this->emailBody .= $colStart . htmlspecialchars($value,ENT_QUOTES) . $colEnd;
        // }
        // $this->emailBody .= "</TR>\n";
    }

    function updatePreviousRow(&$previousRow, $row)
    {
        foreach ($row as $columnName => $value) {
            $previousRow[trim($columnName)] = $row[trim($columnName)];
        }
    }

    function incrementSubTotals(&$subTotalArray, $row)
    {
        foreach ($row as $key => $value) {
            if (is_numeric($value) and ! isset($this->dontSubTotal[$key])) {
                /*
                 * We have a numeric field that we are supposed to subtotal.
                 */
                $subTotalArray[$key] = isset($subTotalArray[$key]) ? $subTotalArray[$key] + $value : $value;
            } elseif (! isset($subTotalArray[$key])) {
                $subTotalArray[$key] = null;
            }
        }

        // echo "<BR/>Key: $key Subtotal was:" . $subTotalArray[$key] . " adding : $value";
        // if(isset($subTotalArray[$key])){
        // /*
        // * We have a value in the Subtotal Row
        // *
        // * Could be Numeric - OR - could be th
        // */

        // } else {
        // }

        // if(is_numeric($subTotalArray[$key])){
        // /*
        // * We
        // */
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // $subTotalArray[$key] += $value;
        // }

        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // $subTotalArray[$key] += $value;
        // } elseif(is_numeric($value) and !isset($this->dontSubTotal[$key])){
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // $subTotalArray[$key] = $value;
        // } else {
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // $subTotalArray[$key] = null;
        // }
        // }
        // echo "<HR/>";

        // if(!isset($subTotalArray[$key]) or !is_numeric(trim($value))) {
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // $subTotalArray[$key] = null;

        // } elseif(isset($subTotalArray[$key])) {
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // $subTotalArray[$key] += $value;

        // } else {
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // $subTotalArray[$key] = $value;
        // }
        // $subTotalArray[$key] = (!isset($subTotalArray[$key]) or !is_numeric(trim($value))) ? null : isset($subTotalArray[$key]) ? '123'+$value : is_numeric($value) ? $value : null;
        // echo "<BR/>New SubTotal:" . $subTotalArray[$key];
        // }
        // echo "<BR/>" . __METHOD__ . __LINE__ . "<BR/>";
        // print_r($subTotalArray);
        // echo "<HR/>";

        /*
         * This code was here 2013-05-13 But I dont understand it - so rewrote it above.
         */
        // print_r($subTotalArray);
        // echo "<BR/><------><BR/>";
        // print_r($row);
        // echo "<BR/><B>@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@</B></BR>";
        // foreach ($subTotalArray as $columnName=>$subTotalRow) {
        // foreach($subTotalRow as $columnName2 => $value){
        // // echo "<BR/><B>..... Inner Loop</B> $columnName $columnName2 :" . $row[trim($columnName2)] . "<BR/>";
        // // print_r($subTotalArray[trim($columnName)]);
        // // echo "<HR/>";
        // if(empty($subTotalRow[trim($columnName)])){
        // $subTotalArray[trim($columnName)][trim($columnName2)] = $row[trim($columnName2)];
        // } else {
        // $subTotalArray[trim($columnName)][trim($columnName2)] += $row[trim($columnName2)];
        // }
        // }
        // }
        // echo "<H3> Incremented Sub Totals</H3>";
        // print_r($subTotalArray);
        // echo "<BR/><B>=====================</B></BR>";
    }

    /*
     * Checks to see if the value in a column has changed such that we need to display the SubTotal.
     * Returns the Column Name that changed - or FALSE is none of the trigger columns have changed.
     */
    function checkForColumnChange($previousRow, $row)
    {
        foreach ($this->triggerSubTotal as $columnToCheck => $ignore) {
            if (trim($previousRow[$columnToCheck]) != trim($row[$columnToCheck])) {
                // echo "<BR/>" . __METHOD__ . __LINE__ ;
                // echo "<BR/>Value was : " . trim($previousRow[$columnToCheck]) . " is : " . trim($row[$columnToCheck]);
                // echo "<BR/>Row:";
                // print_r($row);
                // echo "<BR/>Previos Row:";
                // print_r($previousRow);
                return $columnToCheck;
            }
        }
        return FALSE;
    }

    function calcTotal($key, $value, $row, $type = null)
    {
        if (! isset($this->hiddenColumns[str_replace("_", " ", trim($key))])) {
            $this->subTotal[$key] = isset($this->subTotal[$key]) ? $this->subTotal[$key] += $value : $value;
        } else {
            $this->subTotal[$key] = ' ';
        }
    }

    function displayTotal()
    {
        /*
         * BAsically - if we're turned on Totalling - then calcTotal gets called for any fields that are type REAL or INT
         * And it accumulates the value for that column.
         * This function then displays the Totalled columns.
         *
         */
        if (isset($this->subTotal)) {
            echo "\n<TFOOT>";
            echo "<TR bgcolor='#cceeff' class='displayTotal' >";
            if ($this->editLink) {
                echo "<TD></TD>";
            }
            foreach ($this->subTotal as $key => $value) {
                if (! isset($this->hiddenColumns[str_replace("_", " ", trim($key))])) {
                    $this->processField($key, $value, $this->subTotal, 'Subtotal');
                }
            }
            if ($this->deleteLink) {
                echo "<TD></TD>";
            }
            echo "</TR>";
            echo "</TFOOT>\n";
            // If we have a CSV open, then put the row to it.
            if (isset($this->csv)) {
                fputcsv($this->csv, $this->subTotal, ",", '"');
            }
        }
    }

    function getSubTotal()
    {
        return $this->subTotal;
    }

    function getPredicateSelect()
    {
        return $this->predicateSelect->getPredicate();
    }

    function displaySubTotals($previousRow, $row, &$subTotalArray, $columnThatHasChanged = false)
    {
        // echo "<BR/>" . __METHOD__ . __LINE__ ;
        // print_r($subTotalArray);
        // echo "<HR/>";
        echo "<TR bgcolor='#5Fe' class='displaySubtotalFirst'>";
        foreach ($subTotalArray as $key => $value) {
            if (trim($key) == trim($columnThatHasChanged)) {
                $this->processField($columnThatHasChanged, $previousRow[$columnThatHasChanged], $previousRow);
            } else {
                $this->processField($key, $subTotalArray[$key], $subTotalArray);
            }
        }
        echo "</TR>";
        foreach ($subTotalArray as $key => $value) {
            $subTotalArray[$key] = null;
        }

        // $displaySubTotals = false;
        // $displayColumnName = false;
        // foreach($subTotalArray as $columnName => $subTotalRow){
        // echo "<BR/><B>Display Sub Totals checking:</B> $columnName against $columnThatHasChanged ";
        // if(trim($columnName)==trim($columnThatHasChanged) or !$columnThatHasChanged or $displaySubTotals){
        // $displaySubTotals = true;
        // echo "<BR/>HIT<BR/>";
        // print_r($subTotalArray);
        // echo "<TR bgcolor='#5Fe'>";
        // foreach($previousRow as $column => $value ){
        // if(trim($column) == trim($columnThatHasChanged)){
        // $displayColumnName = true;
        // echo "<TD>$value</TD>";
        // } else {
        // echo "<TD></TD>";
        // }
        // }
        // echo "<TD style='text-align:right'>SubTotal:</TD>";
        // foreach($subTotalRow as $column => $value){
        // echo "<BR/>STR Col$column Val$value";
        // echo "<TD style='text-align:center'>" . $this->formatNumericSubTotal($value) . "</TD>";
        // $subTotalRow[trim($column)] = 0.00;
        // }
        // echo "</TR>";
        // } else {
        // echo "<BR/>MISS";
        // }
        // }
    }

    function formatNumericSubTotal($value)
    {
        return $value;
    }

    function initialiseSubTotalArray(&$subTotalArray, $cols, $rs)
    {
        $cols = trim(trim($this->colSelect->getSelect()), ',');
        $SqlColumns = preg_split("/,/", $cols);
        if (count($SqlColumns) > 1) {
            for ($c = 0; $c < count($SqlColumns) - 1; $c ++) {
                // We need all but the lowest level to have an Array that will hold the subtotal values for this level.
                $subTotalArray[trim($SqlColumns[$c])] = array();
            }
        }

        $columns = db2_num_fields($rs);
        $columnType = array();
        for ($c = 0; $c < $columns; $c ++) {
            $columnType[db2_field_name($rs, $c)] = db2_field_type($rs, $c);
            if (db2_field_type($rs, $c) == 'real') {
                foreach ($subTotalArray as $column => $subTotalRow) {
                    $subTotalArray[$column][db2_field_name($rs, $c)] = 0.00;
                }
            }
        }
    }

    function setTableTitle($title)
    {
        $this->tableTitle = $title;
    }

    function setTableTag($tableTag)
    {
        $this->tableTag = $tableTag;
    }

    /**
     * Will display the options for loading and saving Profiles.
     *
     * A Profile is a pre-set list of options in the associated DropSelect panel.
     */
    function displayProfileOptions()
    {
        if ($this->profileSaveable && isset(AllITdqTables::$PROFILES)) { // They are setup to have saved profiles.
            $loader = new Loader();
            $myProfiles = $loader->load('PROFILE_NAME', AllITdqTables::$PROFILES, " (INTRANET='" . htmlspecialchars(trim($_SESSION['ssoEmail'])) . "' or INTRANET='global' ) AND PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "' ");
            echo "<TABLE class=$this->dropSelectTableClass style='width:25%'>";
            if (! empty($myProfiles)) {
                $this->profileSelectionBar->selectBox('Load Profile', 'Select...', 'SELECTED_PROFILE', $myProfiles);
                echo "<TR></TR>";
            }
            $this->profileSelectionBar->setAutoRefresh(FALSE); // Dont want Profile Name changes triggering a refresh
            $_REQUEST['sbifProfile_Name'] = null;
            $_SESSION['sbifProfile_Name'] = null;
            $this->profileSelectionBar->inputField('Profile Name', 'PROFILE_NAME');
            $this->profileSelectionBar->setAutoRefresh(TRUE);
            $this->profileSelectionBar->submitButton('Save Profile');
            $this->profileSelectionBar->submitButton('Delete Profile');
            if (isset($_SESSION['adminBg'])) {
                if (OKTAGroups::inAGroup($_SESSION['adminBg'], $_SESSION['ssoEmail'])) {
                    $this->profileSelectionBar->checkBox('Global', 'global');
                }
            }
            echo "</TABLE>";

            if (isset($_REQUEST['sbsLoad_Profile'])) {
                if (! empty($_REQUEST['sbsLoad_Profile'])) {
                    $allFields = $loader->loadIndexed('FIELD_VALUE', 'FIELD_NAME', AllITdqTables::$PROFILES, " (INTRANET='" . htmlspecialchars(trim($_SESSION['ssoEmail'])) . "' OR INTRANET='global')  AND PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "'  AND PROFILE_NAME='" . htmlspecialchars(trim($_REQUEST['sbsLoad_Profile'])) . "' ");
                    foreach ($allFields as $key => $value) {
                        $_REQUEST[$key] = $value;
                        if (! isset($_SESSION[$key])) {
                            $_SESSION[$key] = $value;
                        }
                    }
                }
            }
        }
        return;
    }

    function getProfilePredicate()
    {
        Trace::traceComment(null, __METHOD__, __LINE__);
        $this->profilePredicate = false;
        if ($this->profileSaveable && isset(AllITdqTables::$PROFILES)) { // They are setup to have saved profiles.
            $loader = new Loader();
            $myProfiles = $loader->load('PROFILE_NAME', AllITdqTables::$PROFILES, " (INTRANET='" . htmlspecialchars(trim($_SESSION['ssoEmail'])) . "' OR INTRANET='global') AND PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "' ");
            if (isset($_REQUEST['sbsLoad_Profile'])) {
                $this->profilePredicate = null;
                if (! empty($_REQUEST['sbsLoad_Profile'])) {
                    $allFields = $loader->loadIndexed('FIELD_VALUE', 'FIELD_NAME', AllITdqTables::$PROFILES, " (INTRANET='" . htmlspecialchars(trim($_SESSION['ssoEmail'])) . "' OR INTRANET='global') AND PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "'  AND PROFILE_NAME='" . htmlspecialchars(trim($_REQUEST['sbsLoad_Profile'])) . "' ");
                    foreach ($allFields as $key => $value) {
                        if (substr($key, 0, 3) == 'sbs') {
                            $fieldName = substr($key, 3);
                            $this->profilePredicate .= " and $fieldName='" . htmlspecialchars(trim($value)) . "' ";
                        }
                    }
                    $this->profilePredicate = substr($this->profilePredicate, 4); // Drop the first AND
                }
            }
        }
        return $this->profilePredicate;
    }

    function restrictRecordsViewable()
    {
        return $this->getProfilePredicate();
    }

    // function restrictPredicate(){
    // echo "<BR/>" . __METHOD__ . __LINE__ ;
    // return $this->getProfilePredicate();
    // }

    /**
     * Saves the different values in the Drop Down and Input fields in the Selection Bar to AllITdqTables::$PROFILES
     */
    function saveProfile()
    {
        if ($this->profileSaveable && isset(AllITdqTables::$PROFILES) && isset($_REQUEST['sbifProfile_Name']) && isset($_REQUEST['sbbSave_Profile'])) {
            if (! empty($_REQUEST['sbifProfile_Name']) && ! empty($_REQUEST['sbbSave_Profile'])) {
                $table = new DbTable(AllITdqTables::$PROFILES);
                $predicate = " PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "' AND PROFILE_NAME='" . htmlspecialchars(trim($_REQUEST['sbifProfile_Name'])) . "' AND INTRANET='" . htmlspecialchars(trim($_SESSION['ssoEmail'])) . "' ";
                $userid = $_SESSION['ssoEmail'];
                if (isset($_SESSION['adminBg'])) {
                    if (OKTAGroups::inAGroup($_SESSION['adminBg'], $_SESSION['ssoEmail'])) {
                        if (isset($_REQUEST['sbcGlobal'])) {
                            if ($_REQUEST['sbcGlobal'] == 'Y') {
                                $predicate = " PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "' AND PROFILE_NAME='" . htmlspecialchars(trim($_REQUEST['sbifProfile_Name'])) . "' AND INTRANET='global' ";
                                $userid = 'global';
                            }
                        }
                    }
                }
                $table->deleteData($predicate, FALSE); // Clear down an existing entry - Dont announce it.
                $profileRecord = new ProfileRecord();
                foreach ($_REQUEST as $key => $value) {
                    if ((substr($key, 0, 3) == 'sbs' or substr($key, 0, 4) == 'sbif') && ! empty($value) && ! isset($this->dontSaveTheseProfileFields[$key]) && ! empty($_REQUEST['sbifProfile_Name'])) { // Only Save input fields and drop down selection boxes
                        $profileRecord->reset($userid, $_SERVER['PHP_SELF'], trim($_REQUEST['sbifProfile_Name']), trim($key), trim($value));
                        $table->saveRecord($profileRecord);
                    }
                }
            }
        }
    }

    /**
     * Saves the different values in the Drop Down and Input fields in the Selection Bar to AllITdqTables::$PROFILES
     */
    function deleteProfile()
    {
        if ($this->profileSaveable && isset(AllITdqTables::$PROFILES) && isset($_REQUEST['sbsLoad_Profile']) && isset($_REQUEST['sbbDelete_Profile'])) {
            if (! empty($_REQUEST['sbsLoad_Profile']) && ! empty($_REQUEST['sbbDelete_Profile'])) {
                $table = new DbTable(AllITdqTables::$PROFILES);
                $predicate = " PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "' AND PROFILE_NAME='" . htmlspecialchars(trim($_REQUEST['sbsLoad_Profile'])) . "' AND INTRANET='" . htmlspecialchars(trim($_SESSION['ssoEmail'])) . "' ";
                $userid = $_SESSION['ssoEmail'];
                if (isset($_SESSION['adminBg'])) {
                    if (OKTAGroups::inAGroup($_SESSION['adminBg'], $_SESSION['ssoEmail'])) {
                        if (isset($_REQUEST['sbcGlobal'])) {
                            if ($_REQUEST['sbcGlobal'] == 'Y') {
                                $predicate = " PAGE='" . htmlspecialchars(trim($_SERVER['PHP_SELF'])) . "' AND PROFILE_NAME='" . htmlspecialchars(trim($_REQUEST['sbsLoad_Profile'])) . "' AND INTRANET='global' ";
                                $userid = 'global';
                            }
                        }
                    }
                }
                $table->deleteData($predicate, FALSE); // Clear down an existing entry - Dont announce it.
            }
        }
    }

    function profileSaveable()
    {
        return $this->profileSaveable;
    }

    function writeRowToCsv($row, $csv, $seperator = ',', $delimiter = '"')
    {
        fputcsv($csv, $row, $seperator, $delimiter);
    }

    function getReportAsCsv($resultSet){
        $titles = '';
        $data = '';

        while( ($row=sqlsrv_fetch_array($resultSet)) == true){
            $row = $this->preProcessRowForCsv($row);
            $titles = empty($titles) ? $this->processRowForCsvTitles($row) . "\n" : $titles;
            $data .= $this->processRowForCsvData($row) . "\n";
        }

        $csv = $titles . $data;
        return $csv;

    }


    function preProcessRowForCsv($row){
        return $row;
    }

    function processRowForCsvTitles($row){
        foreach ($row as $key => $value){
            $titles .= '"' .  trim($key) . '",';   // Doesn't matter if we do it every time, we only write it to the final CSV once.
        }
        return substr($titles,0,-1);
    }

    function processRowForCsvData($row){
        $data = '';
        foreach ($row as $key => $value){
            $data .= '"' .  trim($value) . '",';   // Doesn't matter if we do it every time, we only write it to the final CSV once.
        }
        return substr($data,0,-1);
    }


}