<?
// This page allows you to add and remove learning outcomes to/from a class
	// You can remove outcomes currently assigned from a course
	// You can add an existing outcome to a course
	// You can create a new learning outcome

// Semesters
$sems[1] = "Spring ";
$sems[2] = "Summer ";
$sems[3] = "Fall ";
?>

<h1 align=center>Add/Remove Learning Outcomes to a Class</h1>

<?
// Creates a course in the database
if ($_POST['confirm'] == "Yes")
{
	$cid = $_POST['cid'];
	$syid = $_POST['syid'];
	
	// gets course name
	$query = <<< ENDOFQUERY
	SELECT program, course from course where course_id=$cid
ENDOFQUERY;
	$cname = $db->getSELECT($query);
	$cname = $cname['results'][1];
	$cname = $cname['program'].' '.$cname['course'];
	$query = <<< ENDOFQUERY
	SELECT * from semester_year where semester_year_id=$syid
ENDOFQUERY;
	$semyear = $db->getSELECT($query);
	$semyear = $semyear['results'][1];
	$name = $cname.': '.$sems[$semyear['semester']].$semyear['year'];
	
	$db->insertRow(array('table'=>'vcu_class', 'course_id'=>$cid, 'semester_year_id'=>$syid));
?>
	<p><?=$name?> has been inserted into the database.</p>
<?
}

// Removes learning outcome from a course
if ($_POST['send'] == "Remove Outcome")
{
	$class = $_POST['class'];
	$oid = $_POST['oid'];
	
	$query = <<< ENDOFQUERY
	select vcu_class_outcome_id from vcu_class_outcome
	where vcu_class_id=$class and outcome_id=$oid
ENDOFQUERY;
	$void = $db->getSELECT($query);
	$void = $void['results'][1]['vcu_class_outcome_id'];
	
	$query = <<< ENDOFQUERY
	delete from vcu_class_outcome where vcu_class_id=$class and outcome_id=$oid
ENDOFQUERY;
	mysql_query($query);
	
	$query = <<< ENDOFQUERY
	delete from outcome_course_feedback where vcu_class_id=$class and vcu_class_outcome_id=$void
ENDOFQUERY;
	mysql_query($query);
}

// Adds learning outcome to a course
if ($_POST['send'] == "Add Learning Outcome")
{
	$class = $_POST['class'];
	$oid = $_POST['outcome'];
	
	$query = <<< ENDOFQUERY
	SELECT vcu_class_outcome_id from vcu_class_outcome where vcu_class_id=$class and outcome_id=$oid
ENDOFQUERY;
	$exists = $db->getSELECT($query);
	
	// If selected outcome is not already listed
	if (!is_array($exists))
	{
		$db->insertRow(array('table'=>'vcu_class_outcome', 'vcu_class_id'=>$class, 'outcome_id'=>$oid));
		
		$query = <<< ENDOFQUERY
		SELECT vcu_class_outcome_id from vcu_class_outcome where vcu_class_id=$class and outcome_id=$oid
ENDOFQUERY;
		$void = $db->getSELECT($query);
		$void = $void['results'][1]['vcu_class_outcome_id'];
		
		$db->insertRow(array('table'=>'outcome_course_feedback', 'vcu_class_id'=>$class, 'vcu_class_outcome_id'=>$void, 'achievement_A'=>0, 'achievement_B'=>0, 'achievement_C'=>0, 'achievement_D'=>0, 'achievement_F'=>0, 'achievement_P'=>0));
	}
}

// Creating a Brand New Learning Outcome and Adding it to the Database
if ($_POST['send'] == "Create New Learning Outcome")
{
	$text = $_POST['newoutcome'];
	
	$query = <<< ENDOFQUERY
	SELECT * from outcome where outcome_text='$text'
ENDOFQUERY;
	$exists = $db->getSELECT($query);
	
	// If selected outcome does not already exist in the database
	if (!is_array($exists))
	{
		$db->insertRow(array('table'=>'outcome', 'outcome_text'=>$text));
?>
		<p><?=$text?> inserted into database.</p>
<?
	}
	else
	{
?>
		<p><?=$text?> already exists in the database.</p>
<?
	}
}

// Changing Learning Outcomes Page
if ($_POST && ($_POST['send']))
{
	// gets the course, semester, and year submitted
	$cid = $_POST['course'];
	$syid = $_POST['semyear'];
	
	// gets course name
	$query = <<< ENDOFQUERY
	SELECT program, course from course where course_id=$cid
ENDOFQUERY;
	$cname = $db->getSELECT($query);
	$cname = $cname['results'][1];
	$cname = $cname['program'].' '.$cname['course'];
	$query = <<< ENDOFQUERY
	SELECT * from semester_year where semester_year_id=$syid
ENDOFQUERY;
	$semyear = $db->getSELECT($query);
	$semyear = $semyear['results'][1];
	$name = $cname.': '.$sems[$semyear['semester']].$semyear['year'];
	
	// gets class id
	$query = <<< ENDOFQUERY
	SELECT vcu_class_id from vcu_class where course_id=$cid and semester_year_id=$syid
ENDOFQUERY;
	$class = $db->getSELECT($query);
	
	// if the selected class exists
	if (is_array($class))
	{
		$class = $class['results'][1]['vcu_class_id'];
		
		// gets outcomes
		$query = <<< ENDOFQUERY
		SELECT * from outcome
		order by outcome_id
ENDOFQUERY;
		$outcomes = $db->getSELECT($query);
		$outcomes = $outcomes['results'];
		
		// "Title"
?>
		<h2 align=center>Course <?=$name?></h2><br>
<?
		
		// gets outcome ids for this course from class_outcome table
		$query = <<< ENDOFQUERY
		SELECT outcome_id from vcu_class_outcome
		where vcu_class_id=$class
		order by outcome_id
ENDOFQUERY;
		$outids = $db->getSELECT($query);
		$outids = $outids['results'];
		
		// List Current Outcomes
		if (is_array($outids))
		{
?>
			<h4>Current learning outcomes for this course:</h4>
			<table border=1 width=500><tr><td>
<?
			// gets text for each outcome id
			for ($i=1; $i<=count($outids); $i++)
			{
				$oid = $outids[$i]['outcome_id'];
				
				$outtext = $outcomes[$oid]['outcome_text'];
?>
				<form action=?page=addoutcomes method=post><b>
<?
				echo($outtext);
?>
				</b><input type=submit name=send value="Remove Outcome">
				<input type=hidden name=course value=<?=$cid?>>
				<input type=hidden name=semyear value=<?=$syid?>>
				<input type=hidden name=class value=<?=$class?>>
				<input type=hidden name=oid value=<?=$oid?>></form>
<?
			}
?>
			</tr></td></table>
<?
		}
		else
		{
?>
		<h4>No learning outcomes found for this course.</h4>
<?	
		}
		
		// Add an Outcome to this Course
?>
		<br><h4>Add a learning outcome for this course:</h4>
		<form action=?page=addoutcomes method=post>
		<input type=hidden name=course value=<?=$cid?>>
		<input type=hidden name=semyear value=<?=$syid?>>
		<input type=hidden name=class value=<?=$class?>>
		<select name=outcome>
<?
		for ($i=1; $i<=count($outcomes); $i++)
		{
?>
			<option value=<?=$i?>><?=$outcomes[$i]['outcome_text']?></option>
<?
		}
?>
		</select>
		<input type=submit name=send value="Add Learning Outcome">
		</form>
<?
		
		// Create new Learning Outcome
?>
		<br><h4>Add a new Learning Outcome to the Database:</h4>
		<form method=post action=?page=addoutcomes>
		<input name=newoutcome type=text size=30>
		<input type=hidden name=course value=<?=$cid?>>
		<input type=hidden name=semyear value=<?=$syid?>>
		<input type="submit" name="send" value="Create New Learning Outcome">
		<p></p>
		<input type="submit" name="return" value="Select New Class">
		</form>
<?
	}
	
	// if the selected class does not exist
	else
	{
?>
		<form name=cform method=post action=?page=addoutcomes>
		<font size=4><b><?=$name?> does not exist</b></font><p>
		<p>Would you like to create this course?</p>
		<input type=hidden name=cid value=<?=$cid?>>
		<input type=hidden name=syid value=<?=$syid?>>
		<input type="submit" name="confirm" value="Yes">
		<input type="submit" name="return" value="No">
		</form>
<?
	}
}

// The "main" page
else
{
	$query = <<< ENDOFQUERY
	SELECT * from course
	order by program, course
ENDOFQUERY;
	$courses = $db->getSELECT($query);
	
	$query = <<< ENDOFQUERY
	SELECT * from semester_year where semester!=0
	order by year, semester
ENDOFQUERY;
	$terms = $db->getSELECT($query);
	$terms = $terms['results'];
	
	// if courses exist
	if (is_array($courses))
	{
		$courses = $courses['results'];
	?>
	
	<p><table border=1 width=500><tr><td>
	<form name=cform method=post action=?page=addoutcomes>
		<font size=4><b>Select Course and Term</b></font><p>
		Course: <select name=course>
			<?
			for ($i = 1; $i <= count($courses); $i++)
			{
				$c = $courses[$i];
				?>
				<option value=<?=$c['course_id']?>> <?=$c['program'].' '.$c['course']?></option>
				<?
			}
			?>
			</select>
		Term: <select name=semyear>
<?
			for ($i=1; $i<=count($terms); $i++)
			{
?>
				<option value=<?=$terms[$i]['semester_year_id']?>><?=$sems[$terms[$i]['semester']].$terms[$i]['year']?></option>
<?
			}
?>
			</select><p>

		<input type="submit" name="send" value="Get Class">
	</form></td></tr></table>

<?
	}
} // end of else
?>
