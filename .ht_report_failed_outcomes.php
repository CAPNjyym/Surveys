<h2 align=center>Failed Learning Outcomes Report</h2>
<?
// Given a Time Range, a report generator should report all Learning Outcomes that were bad.

// The bar that determines whether the grades pass or fail
// The default is set to 80%, meaning if 80% or more are A/B/C/P then the Learning Outcome passes, otherwise it fails
$pass_bar = 80;

// Semesters
$sems[1] = "Spring ";
$sems[2] = "Summer ";
$sems[3] = "Fall ";

if ($_POST && ($_POST['send'] == 'Submit'))
{
	$syid1 = $_POST["syid1"];
	$syid2 = $_POST["syid2"];
	
	$query = <<< ENDOFQUERY
SELECT * FROM semester_year where semester_year_id=$syid1
ENDOFQUERY;
	$term1 = $db->getSELECT($query);
	$term1 = $term1['results'][1];
	
	$query = <<< ENDOFQUERY
SELECT * FROM semester_year where semester_year_id=$syid2
ENDOFQUERY;
	$term2 = $db->getSELECT($query);
	$term2 = $term2['results'][1];
	
	// check to make sure terms are ordered correctly
	// if they are not ordered correctly, swap them
	if ($term1['year'] > $term2['year'] ||
		($term1['year'] == $term2['year'] &&
			$term1['semester'] > $term2['semester']))
	{
		$temp = $term1;
		$term1 = $term2;
		$term2 = $temp;
	}
	
	$year1 = $term1['year'];
	$year2 = $term2['year'];
	$sem1 = $term1['semester'];
	$sem2 = $term2['semester'];
	$semyear = $sems[$sem1].' '.$year1;
	if ($year1 != $year2 || $sem1 != $sem2)
		$semyear .= ' to '.$sems[$sem2].' '.$year2;
?>
	<h3 align=center><?=$semyear?></h3>
<?
	// query the database for all semester_years that are between term1 and term2, inclusive
	// if terms have the same year
	if ($year1 == $year2)
	{
		$query = <<< ENDOFQUERY
		SELECT * FROM semester_year
		where year = $year1 and semester != 0
		and semester >= $sem1 and semester <= $sem2
		order by year, semester
ENDOFQUERY;
	}
	// if terms have different years
	else
	{
		$query = <<< ENDOFQUERY
		SELECT * FROM semester_year
		where year > $year1 and year < $year2 and semester != 0
		or year = $year1 and semester >= $sem1 and semester != 0
		or year = $year2 and semester <= $sem2 and semester != 0
		order by year, semester
ENDOFQUERY;
	}
	$terms = $db->getSELECT($query);
	$terms = $terms['results'];
	
	// a REALLY nasty query that will get all classes that are linked to the given semester_year_ids
	$total = count($terms);
	$query = "SELECT * FROM vcu_class WHERE ";
	for ($i=1; $i<$total; $i++)
		$query = $query . "semester_year_id = " . $terms[$i]['semester_year_id'] . " OR ";
	$query = $query . "semester_year_id = " . $terms[$total]['semester_year_id'];
	
	$classes = $db->getSELECT($query);
	
	if (!is_array($classes))
	{
?>
		<p>There are no classes from <?=$semyear?>.</p>
		<form name="return" method="post" action="?page=failedlos">
		<input type="submit" name="return" value="Return">
		</form>
<?
		die;
	}
	
	$classes = $classes['results'];
	$total = count($classes);
	
	if ($total > 0)
		$query = "SELECT * FROM vcu_class_outcome WHERE vcu_class_id = " . $classes[1]['vcu_class_id'];
	for ($i=2; $i<=$total; $i++)
		$query = $query . " OR vcu_class_id = " . $classes[$i]['vcu_class_id'];
	
	$classouts = $db->getSELECT($query);
	
	if (!is_array($classouts))
	{
?>
		<p>There are no classes from <?=$semyear?>.</p>
		<form name="return" method="post" action="?page=failedlos">
		<input type="submit" name="return" value="Return">
		</form>
<?
		die;
	}
	
	$classouts = $classouts['results'];
	$classoutcount = count($classouts);
	
	// a REALLY nasty query that will get all LO grades for the outcomes from term 1 to term 2
	if ($classoutcount > 0)
		$query = "SELECT * FROM outcome_course_feedback WHERE vcu_class_outcome_id = " . $classouts[1]['vcu_class_outcome_id'];
	for ($i=2; $i<=$classoutcount; $i++)
		$query = $query . " OR vcu_class_outcome_id = " . $classouts[$i]['vcu_class_outcome_id'];
	
	$grades = $db->getSELECT($query);
	$grades = $grades['results'];
	$total = count($grades);
	$pass = $fail = 0;
	
	// Gets all outcomes from the database
	$query = "SELECT * FROM outcome ORDER BY outcome_id";
	$outcomes = $db->getSELECT($query);
	if (!is_array($outcomes))
	{
?>
		<font size=4><b><?=$name?>There are no learning outcomes in the database.</b></font><p>
		<p>Would you like to go the the add / remove learning outcomes page?</p>
		<form name=cform method=post action=?page=addoutcomes>
		<input type="submit" name="cancel" value="Yes">
		</form>
		<form name="return" method="post" action="?page=failedlos">
		<input type="submit" name="return" value="No">
		</form>
<?
		die;
	}
	
	$outcomes = $outcomes['results'];
	$outcount = count($outcomes);
	
	// Goes through each outcome and totals the grades
	for($i=1; $i<=$classoutcount; $i++)
	{
		
		$outcome = $classouts[$i]['outcome_id'];
		$feedbackid = $classouts[$i]['vcu_class_outcome_id'];
		
		for($j=1; $j<=$total; $j++)
			if ($grades[$j]['vcu_class_outcome_id'] == $feedbackid)
				$gradeid = $j;
		
		$outcomes[$outcome]['pass'] += $grades[$gradeid]['achievement_A'];
		$outcomes[$outcome]['pass'] += $grades[$gradeid]['achievement_B'];
		$outcomes[$outcome]['pass'] += $grades[$gradeid]['achievement_C'];
		$outcomes[$outcome]['fail'] += $grades[$gradeid]['achievement_D'];
		$outcomes[$outcome]['fail'] += $grades[$gradeid]['achievement_F'];
		$outcomes[$outcome]['pass'] += $grades[$gradeid]['achievement_P'];
	}
	
	$out = false;
	
	// Lists the outcomes that have failed
	for ($i=1; $i<=$outcount; $i++)
	{
		$pass = $outcomes[$i]['pass'];
		$fail = $outcomes[$i]['fail'];
		
		// if the total number of grades is 0
		if ($pass > 0 || $fail > 0)
		{
			$out = true;
			$percent = 100 * $pass / ($pass + $fail);
			
			if ($percent < $pass_bar)
			{
?>
				<p><b><?=$outcomes[$i]['outcome_text']?></b> has not acheived a passing rating (<?=$percent?>%).</p>
<?
			}
		}
	}
	
	// If no outcomes were listed, then display a message saying all have passed
	if (!$out)
	{
?>
		<p>All learning outcomes have acheived a passing rating, or have no grades entered.</p>
<?
	}
?>
	<form name="return" method="post" action="?page=failedlos">
	<input type="submit" name="return" value="Return">
	</form>
<?
}
// Main page
else
{
?>
	<p> Given a Time Range, this report generator will list all Learning Outcomes that were bad in the Time Range.</p>
	<form name="sr" method="post" action="?page=failedlos">
<?
	// gets list of time range values from database
	$query = <<< ENDOFQUERY
SELECT * FROM semester_year where semester!=0
order by year, semester
ENDOFQUERY;

	$terms = $db->getSELECT($query);

	// if there are no terms....
	if (!is_array($terms))
	{
?>
		</form>
		<font size=4><b><?=$name?>There are no terms in the database.</b></font><p>
		<p>Would you like to go the the manage courses and terms page?</p>
		<form name=cform method=post action=?page=managecat>
		<input type="submit" name="cancel" value="Yes">
		</form>
		<form name=cform method=post action=?page=manrep>
		<input type="submit" name="cancel" value="No">
		</form>
<?
		die;
	}
	else
	{
?>
		Time Range:<br/>
		<select name="syid1">
<?
		// drop down menu for time range
		$terms = $terms['results'];
		$total = count($terms);
		
		for ($i=1; $i<=$total; $i++)
		{
			$syid = $terms[$i]['semester_year_id'];
			$term = $sems[$terms[$i]['semester']].' '.$terms[$i]['year'];
?>
			<option value=<?=$syid?>><?=$term?></option>;
<?
		}
?>
		</select>
		to
		<select name="syid2">
<?
		for ($i=1; $i<=$total; $i++)
		{
			$syid = $terms[$i]['semester_year_id'];
			$term = $sems[$terms[$i]['semester']].' '.$terms[$i]['year'];
?>
			<option value=<?=$syid?>><?=$term?></option>;
<?
		}
?>
		</select>
<?
	}
?>
	<br/>
	<br/>
	
	<input type="submit" name="send" value="Submit">
	</form>
	
	<br/>
	
	<form name="return" method="post" action="?page=manrep">
	<input type="submit" name="return" value="Cancel">
	</form>
<?
}
?>
