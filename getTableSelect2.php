<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/12/2017
 * Time: 9:49 AM
 */
    require("connection.php");
    require_once("Field.php");
    session_start();
    /*
     * There are three ways to search on a field: multiselect checkbox, min/max input boxes, and a simple checkbox for
     * has/has not relationships (for example, check the box if you want to search on properties that "have AC").
     * In order to keep the filter dynamic that means we will need to check each field name for certain keywords in order
     * to determine which HTML element should be generated to allow the user to search by that category. The following
     * three global arrays contain the keywords for each of those scenarios based on an analysis of the database fields
     * and how they were searchable in the old RP2 filter. Contains wildcard expressions. Any field not in one of these
     * two arrays will generate a multiselect checkbox menu.
     */
    $minMax = [
        '/amt/',
        '/adj/',
        '/price/',
        '/.av/',
        '/value/',
        '/taxable/',
        '/value/',
        '/taxable/',
        '/total/',
        '/yr/',
        '/acres/',
        '/cost/',
        '/perimeter/',
        '/area/',
        '/height/',
        '/nbr/',
        '/sqft/',
        '/feet/',
        '/length/',
        '/width/'
    ];
    $checkbox = ['/pct/', '/percent/'];

    $_SESSION['county'] = $county = $_GET['county'];
    /*
    * For display purposes table name was capitalized on view page
    * This removes the capitalization to match table names
    */
    $_SESSION['table'] = $table = $county . '_' . strtolower($_GET['table']);

    $alreadyDisplayedFields = $_SESSION['alreadyDisplayedFields'];
    $fields = array();
    $gridCount = 0;


/*
 * Class to hold county codes, type and meaning
 */
    /*class Code {
        public $type;
        public $code;
        public $meaning;

        public function __construct($type, $code, $meaning) {
            $this->type = $type;
            $this->code = $code;
            $this->meaning = $meaning;
        }
    }*/

    /*
     * Now need to get the fields for the table being requested
     * Check for duplicates that are already displayed in other accordions
     * Check if field (for display purposes) needs to be linked with codes table or a definition table
     */
    $query = "SHOW COLUMNS FROM " . $table . ";";
    if($result = mysqli_query($link, $query)) {
        //Get field information for all fields in the table
        while($row = $result->fetch_assoc()) {
            /*
             * Field isn't a duplicate
             *       isn't the primary key
             * So add it to the list of fields to be displayed and to list for duplicate checks
             */
            if(!in_array($row['Field'], $alreadyDisplayedFields) && strcmp($row['Field'], 'primaryID') != 0) {
                array_push($fields, new Field($row['Field'], $table, $link, $minMax, $checkbox));
                array_push($alreadyDisplayedFields, $row['Field']);
            }
        }
    }

    for($i = 0; $i < sizeOf($fields); ++$i) {
        $_SESSION['fields'][$table][$i] = $fields[$i];
    }
?>

<html>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
    <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="jquery.multiselect.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="jquery.multiselect.css"/>

    <body>
        <table>
            <?php
                foreach($fields as $field) {
                    if($gridCount == 0) {
                        echo "<tr>";
                    }
                    if($gridCount < 3) {
                        echo "<td width='360px'>";
                    }
            ?>
                    <span><input type="checkbox" name="template_list[]" value="<?php echo $field->fullFieldName ?>"><?php echo $field->fieldName ?></span>
                    <?php
                    echo "</td>";
                    if($gridCount == 2) {
                        echo "</tr>";
                        $gridCount = 0;
                    }
                    else {
                        ++$gridCount;
                    }
                }
            ?>
        </table>
    </body>
</html>