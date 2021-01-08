<?
// This page allows a professor to add and modify grades for a class
	// If a class has no learning outcomes,
		// the user can choose to add outcomes to a class via a redirect
	// If the class has learning outcomes,
		// the user can add and modify grades for learning outcomes assigned to the class
	// *The default number of grades is set to 0
		// "Submit Grades" will send the grades to the database
		// "Cancel" will return the user to the class selection page

// Semesters
$sems[1] = "Spring ";
$sems[2] = "Summer ";
$sems[3] = "Fall ";
?>

<h1 align=center>Add/Edit Outcome Grades</h1>

<?
// Creates a course in the database
if ($_POST['confirm'])
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

// Updating Grades to Database
if ($_POST['update'])
{
	$total = $_POST['total'];		// gets total number of outcomes
	
	for ($i=1; $i<=$total; $i++)
	{
		$oid = $_POST['outcome'.$i];	// gets outcome id
		$a = $_POST['a'.$i];			// gets number of As
		$b = $_POST['b'.$i];			// gets number of Bs
		$c = $_POST['c'.$i];			// gets number of Cs
		$d = $_POST['d'.$i];			// gets number of Ds
		$f = $_POST['f'.$i];			// gets number of Fs
		$p = $_POST['p'.$i];			// gets number of Ps
		
		// Updates database with above values
		$query = <<< ENDOFQUERY
		update outcome_course_feedback
		set achievement_A=$a, achievement_B=$b, achievement_C=$c, achievement_D=$d, achievement_F=$f, achievement_P=$p
		where outcome_course_id = $oid
ENDOFQUERY;
		
		mysql_query($query);
	}
	?>
	<p>Grades Updated</p>
	<?
} // end of "Submit Grades"

// Lists grades for given course, semester, and year
// Allows user to change grades
if ($_POST['send'])
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
	
	$query = <<< ENDOFQUERY
	SELECT * from outcome_course_feedback where course_id=$cid and semester_year_id=$syid
ENDOFQUERY;
	$outcomes = $db->getSELECT($query);
	
	// if the selected class exists
	if (is_array($class))
	{
		$class = $class['results'][1]['vcu_class_id'];
		
		// gets outcome ids for this course from class_outcome table
		$query = <<< ENDOFQUERY
		SELECT * from vcu_class_outcome
		where vcu_class_id=$class
		order by outcome_id
ENDOFQUERY;
		$outids = $db->getSELECT($query);
		$outids = $outids['results'];
		
		// gets outcomes
		$query = <<< ENDOFQUERY
		SELECT * from outcome
		order by outcome_id
ENDOFQUERY;
		$outcomes = $db->getSELECT($query);
		$outcomes = $outcomes['results'];
		
		if (is_array($outids))
		{
			// "Title"
?>
			<h2 align=center>Course <?=$name?></h2><br>
<?

			$id = $outids[$i]['vcu_class_outcome_id'];
			$query = <<< ENDOFQUERY
			select * from outcome_course_feedback where vcu_class_id=$class
ENDOFQUERY;
			$out = $db->getSELECT($query);
			$out = $out['results'];
			
			//$outids = $outids['results'];
			$total = count($outids);
?>
			<p><table border=1 width=500><tr><td>
			<form name=grades method=post action=?page=proffeedback onSubmit="return validate_grades();">
			<input type=hidden name=cid value=<?=$cid?>>
			<input type=hidden name=syid value=<?=$syid?>>
			<input type=hidden name=total value=<?=$total?>>
<?
			//echo(count($outids));
			for ($i=1; $i<=$total; $i++)
			{
				$name = $outcomes[$outids[$i]['outcome_id']]['outcome_text'];
				
				// finds the appropriate location of the grades....
				$index = $outids[$i]['vcu_class_outcome_id'];
				for($j=1; $j<=$total; $j++)
				{
					if ($index == $out[$j]['vcu_class_outcome_id'])
						$index = $j;
				}
?>
				<font size=4><b><?=$name?>:</b></font><br>
				<input type=hidden name=<?='outcome'.$index?> value=<?=$out[$index]['outcome_course_id']?>>
				A: <input name=<?='a'.$index?> type=text size=3 value=<?=$out[$index]['achievement_A']?>><br>
				B: <input name=<?='b'.$index?> type=text size=3 value=<?=$out[$index]['achievement_B']?>><br>
				C: <input name=<?='c'.$index?> type=text size=3 value=<?=$out[$index]['achievement_C']?>><br>
				D: <input name=<?='d'.$index?> type=text size=3 value=<?=$out[$index]['achievement_D']?>><br>
				F: <input name=<?='f'.$index?> type=text size=3 value=<?=$out[$index]['achievement_F']?>><br>
				P: <input name=<?='p'.$index?> type=text size=3 value=<?=$out[$index]['achievement_P']?>><p>
<?
			}
?>
			<input type="submit" name="update" value="Submit Grades">
			<input type="submit" name="return" value="Cancel">
			</form>
			</td></tr></table>
<?
		}
		
		// if there are no outcomes assigned to this course
		// allows user to redirect to add/remove outcomes page
		else
		{
?>
			<font size=4><b><?=$name?> does not have any Learning Outcomes</b></font><p>
			<p>Would you like to go the the add / remove learning outcomes page?</p>
			<form name=cform method=post action=?page=addoutcomes>
			<input type=hidden name=course value=<?=$cid?>>
			<input type=hidden name=semyear value=<?=$syid?>>
			<input type="submit" name="send" value="Yes">
			</form>
			<form name=cform method=post action=?page=proffeedback>
			<input type="submit" name="return" value="No">
			</form>
<?
		}
	}
	
	// if the selected class does not exist
	else
	{
?>
		<form name=cform method=post action=?page=proffeedback>
		<font size=4><b><?=$name?> does not exist</b></font><p>
		<p>Would you like to create this course?</p>
		<input type=hidden name=cid value=<?=$cid?>>
		<input type=hidden name=syid value=<?=$syid?>>
		<input type="submit" name="confirm" value="Yes">
		<input type="submit" name="return" value="No">
		</form>
<?
	}
} // end of "Add Grades"

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
	
	<p><table border=1 width=500><tr><td><form name=cform method=post action=?page=proffeedback>
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

